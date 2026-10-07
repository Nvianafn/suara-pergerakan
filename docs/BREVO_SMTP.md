# Email reset password — Brevo

PRD 4: pemulihan password admin melalui email. Gunakan domain pengirim yang sudah
authenticated dan sender yang disetujui Brevo. Branded tracking subdomain tidak
diperlukan untuk tautan reset Laravel.

## Konfigurasi lokal

Di `.env` lokal, ubah blok mail berikut. Isi username dengan **SMTP login** Brevo
dan password dengan **SMTP key** khusus aplikasi (bukan API/MCP key).

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_REQUIRE_TLS=true
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME="ISI_SMTP_LOGIN"
MAIL_PASSWORD="ISI_SMTP_KEY"
MAIL_FROM_ADDRESS="noreply@rayonsaintek.com"
MAIL_FROM_NAME="Suara Pergerakan — PMII Rayon Saintek"
MAIL_EHLO_DOMAIN=rayonsaintek.com
```

Port 587 memakai SMTP dengan STARTTLS wajib; `MAIL_SCHEME` bukan `tls`.
Laravel 13 tidak memakai variabel lama `MAIL_ENCRYPTION`. Jika ada `MAIL_URL`
dari konfigurasi terdahulu, hapus agar tidak menggantikan host/login di atas.
Jangan commit `.env` atau menyalin key ke chat/log. Untuk Azure, password
disimpan sebagai secret runtime.

Pastikan `APP_URL=http://localhost:8001` untuk pengujian lokal; pada deployment
ubah menjadi URL HTTPS website. Tautan lokal hanya bisa dibuka dari mesin lokal.

Setelah mengubah environment, kosongkan cache konfigurasi dan restart aplikasi:

```sh
/tmp/opencode/docker-compose --env-file /tmp/opencode/suara-sqlserver.env exec app php artisan config:clear
/tmp/opencode/docker-compose --env-file /tmp/opencode/suara-sqlserver.env restart app
```

## Verifikasi pengiriman nyata

1. Gunakan akun admin aktif dengan alamat email inbox yang dapat diakses.
2. Buka `/forgot-password`, kirim permintaan reset satu kali.
3. Periksa log transactional Brevo dan inbox/spam penerima. Respons halaman
   yang sukses belum membuktikan email sudah masuk inbox.
4. Buka tautan dari email, buat password baru, lalu login.
5. Pastikan password lama dan token reset yang telah dipakai tidak berlaku.

Sesuaikan path halaman dengan route aplikasi bila berubah. Jangan memakai
alamat dummy seeder sebagai penerima uji nyata. Catat hasil tanpa token reset
atau credential.

## Hasil verifikasi lokal

Pengiriman SMTP nyata diterima Brevo dan penerima mengonfirmasi email masuk.
Penolakan awal `525 Unauthorized IP address` terselesaikan setelah IP koneksi
diizinkan di Brevo. IP deployment Azure perlu dievaluasi kembali ketika deploy.
Pengguna juga menyelesaikan reset lewat email. Bug wajib mengganti password
dua kali diperbaiki: reset mandiri menghapus `must_change_password`; password
sementara dari admin tetap mewajibkan perubahan.

Playwright memverifikasi reset dengan token broker pada akun uji terpisah:
redirect login, password baru masuk dashboard langsung, password lama ditolak,
token yang telah digunakan ditolak. Akun uji terpisah sudah dibersihkan.
Regresi SQL Server terakhir: 91 tests / 493 assertions lulus.
