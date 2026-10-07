# Panduan Deploy — Website PMII Rayon Saintek

> **Panduan legacy, bukan prosedur production saat ini.** Isi di bawah berasal
> dari baseline AWS/MySQL/Laravel 11. Target aktif mengikuti `PRD_v1.6.md`:
> Laravel 13, Azure SQL, Azure Container Apps, GHCR dan R2. Jangan gunakan
> akun dummy, seeding development, atau storage publik untuk foto privat.
> Bootstrap production: `docs/INITIAL_ADMIN.md`; media: `docs/R2_STORAGE.md`;
> email: `docs/BREVO_SMTP.md`. Pipeline/image production Azure masih perlu
> disiapkan dan diverifikasi sebelum deployment.

Target: **Ubuntu 24.04 LTS (AWS EC2)** · Domain **rayonsaintek.com** · Stack: Laravel 11 + Livewire 3 + MySQL 8 + Nginx + PHP 8.3

---

## 1. Persiapan Server

Masuk ke VPS via SSH, lalu update:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip
```

### DNS
Di panel domain `rayonsaintek.com`, arahkan A record ke IP publik EC2:
- `rayonsaintek.com`      -> A -> <IP_EC2>
- `www.rayonsaintek.com`  -> A -> <IP_EC2>

Di AWS Security Group, buka inbound port **80** dan **443** (dan 22 untuk SSH).

---

## 2. Install PHP 8.3 + ekstensi

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl
```

Cek: `php -v` harus 8.3.x.

> Ekstensi **gd** wajib untuk Intervention Image (resize + konversi WebP thumbnail/foto).

---

## 3. Install Composer, Node.js, MySQL, Nginx

```bash
# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 LTS (untuk build Vite/Tailwind)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# MySQL + Nginx
sudo apt install -y mysql-server nginx
sudo systemctl enable --now mysql nginx php8.3-fpm
```

---

## 4. Buat database MySQL

```bash
sudo mysql
```
```sql
CREATE DATABASE pmii_saintek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pmii_user'@'127.0.0.1' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON pmii_saintek.* TO 'pmii_user'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

---

## 5. Deploy kode (Git Clone langsung)

Karena kerangka dasar Laravel (seperti `public/index.php`, `artisan`, `storage/`, dll.) sudah lengkap dan terkonfigurasi di dalam repositori ini, Anda tidak perlu lagi melakukan trik *scaffold-then-overlay*. Anda dapat langsung melakukan clone repositori dari GitHub Anda.

```bash
cd /var/www

# Clone repositori dari GitHub Anda (ganti URL di bawah dengan URL repositori Anda)
sudo git clone https://github.com/username/pmii-rayon-saintek-laravel.git rayonsaintek

cd /var/www/rayonsaintek
```

---

## 6. Konfigurasi & instalasi dependensi

```bash
cd /var/www/rayonsaintek

# Salin .env produksi (template ada di deploy/.env.production)
sudo cp deploy/.env.production .env
sudo nano .env      # isi DB_PASSWORD dll

# Dependensi PHP (produksi)
composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate

# Migrasi + seed data awal (biro, periode, admin, dsb)
php artisan migrate --force --seed

# Symlink storage -> public (agar foto tampil)
php artisan storage:link

# Build aset frontend (Tailwind + Vite)
npm ci
npm run build

# Cache untuk performa produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Kredensial admin awal (dari seeder)
- URL: `https://rayonsaintek.com/login`
- Email: `admin@pmii-saintek.id`
- Password: `password`  ->  **WAJIB ganti** lewat menu Pengguna setelah login pertama.

---

## 7. Permission

```bash
sudo chown -R www-data:www-data /var/www/rayonsaintek
sudo find /var/www/rayonsaintek -type f -exec chmod 644 {} \;
sudo find /var/www/rayonsaintek -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/rayonsaintek/storage /var/www/rayonsaintek/bootstrap/cache
```

---

## 8. Konfigurasi Nginx

```bash
sudo cp /var/www/rayonsaintek/deploy/nginx-rayonsaintek.conf \
        /etc/nginx/sites-available/rayonsaintek.com
sudo ln -s /etc/nginx/sites-available/rayonsaintek.com /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Cek `http://rayonsaintek.com` — situs harus tampil (belum HTTPS).

---

## 9. SSL (HTTPS) via Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d rayonsaintek.com -d www.rayonsaintek.com
```

Ikuti prompt (email + redirect HTTP->HTTPS). Auto-renew sudah aktif via systemd timer; tes:
```bash
sudo certbot renew --dry-run
```

Setelah SSL aktif, pastikan `APP_URL=https://rayonsaintek.com` di `.env` lalu:
```bash
php artisan config:cache
```

---

## 10. (Opsional) Queue worker

Proyek pakai `QUEUE_CONNECTION=database`. Jika nanti butuh proses antrean (mis. kirim email), pasang Supervisor:

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/rayonsaintek-worker.conf
```
```ini
[program:rayonsaintek-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/rayonsaintek/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/rayonsaintek/storage/logs/worker.log
stopwaitsecs=3600
```
```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start rayonsaintek-worker:*
```

---

## 11. Update rutin (deploy berikutnya)

Setelah ada perubahan kode:
```bash
cd /var/www/rayonsaintek
bash deploy/update.sh
```
(Script `deploy/update.sh` sudah menangani composer install, build, migrate, dan cache ulang.)

---

## Checklist cepat

- [ ] DNS A record -> IP EC2 (root + www)
- [ ] Security Group buka 80/443/22
- [ ] PHP 8.3 + gd/intl/mbstring terpasang
- [ ] Database `pmii_saintek` + user dibuat
- [ ] Kode di `/var/www/rayonsaintek` (scaffold + overlay)
- [ ] `.env` produksi terisi + `key:generate`
- [ ] `migrate --force --seed` sukses
- [ ] `storage:link` + `npm run build`
- [ ] Permission www-data + storage 775
- [ ] Nginx aktif, `nginx -t` OK
- [ ] Certbot SSL terpasang, HTTP->HTTPS redirect
- [ ] Login admin & **ganti password default**

Selamat, website PMII Rayon Saintek siap mengudara di https://rayonsaintek.com 🚀
