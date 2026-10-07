# Super admin pertama

Jalankan migration sebelum bootstrap. Production tidak memiliki akun/password bawaan; `db:seed --force` hanya menambahkan kunci settings kosong dan mempertahankan nilai yang sudah ada.

## Interaktif

```bash
php artisan app:buat-super-admin
```

Perintah meminta nama, email unik, dan password tersembunyi (minimal 8 karakter). Pada host lokal gunakan `php -d extension=iconv artisan ...`. Command menolak bila sudah ada super_admin, termasuk yang nonaktif. SQL Server memakai lock transaksi untuk menghindari dua eksekusi membuat akun pertama bersamaan.

## Job noninteraktif

Pasang secrets sementara `INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL`, dan `INITIAL_ADMIN_PASSWORD` pada Manual Container Apps Job dengan image/database aplikasi yang sama, lalu jalankan:

```bash
php artisan app:buat-super-admin --no-interaction
```

Jalankan dengan satu eksekusi aktif. Jangan menaruh password pada argumen command, log, atau repository. Jika memakai config cache, bangun cache setelah secrets tersedia. Setelah berhasil, lepaskan secrets dan konfigurasi bootstrap dari job/cache sebelum memakai ulang image/runtime.

Masuk dengan akun tersebut dan ganti password yang diwajibkan sebelum membuka CMS. Selanjutnya buat super_admin cadangan melalui CMS dan lengkapi settings organisasi.
