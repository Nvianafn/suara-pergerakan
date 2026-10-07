# Suara Pergerakan — Website PMII Rayon Saintek

## Acuan
- Requirement: `PRD_v1.6.md`. Baca bagian relevan sebelum mengubah fitur.
- Proyek berasal dari `Nvianafn/pmii-rayon-saintek-laravel`; tujuan pengembangan adalah `Nvianafn/suara-pergerakan`.
- Kondisi awal: Laravel 11, Livewire 3, Tailwind 3, MySQL. Target PRD: Laravel 13, SQL Server/Azure SQL, Azure Container Apps, GitHub Actions/GHCR, R2.
- SQLite lokal hanya untuk smoke test baseline; bukan bukti kompatibilitas SQL Server.

## Aturan implementasi
- Tegakkan hak akses di server: super_admin, admin, admin_biro sesuai matriks PRD.
- Pembina menggunakan profil mandiri dan penempatan per periode; orang luar boleh tanpa anggota. Semua berlabel Pembina, tanpa subjabatan/biro atau akses akun otomatis.
- Halaman struktur berurutan Pembina → BPH → Biro.
- Foto anggota/Pembina harus privat dan diperiksa izinnya pada setiap request; jangan menggunakan URL publik untuk foto tersebut.
- Jaga arsip lintas periode dan aturan penghapusan; aktivasi periode harus atomic.
- Dummy/seeder akun bawaan hanya untuk development, bukan production.
- Jangan commit `.env`, database lokal, credential, atau output build.
- Upgrade framework, database, dan deployment secara bertahap dengan pemeriksaan kompatibilitas.

## Pemeriksaan
- Frontend: `npm ci` lalu `npm run build`.
- PHP: `composer install`; lingkungan mesin saat audit membutuhkan `php -d extension=iconv /usr/bin/composer install`.
- CLI lokal: `php -d extension=iconv artisan ...`.
- Tambahkan pengujian bermakna untuk otorisasi, privasi, arsip, dan constraint; baseline belum memiliki suite tests.
- Catatan kondisi awal dan langkah menjalankan: `docs/BASELINE_AUDIT.md`.
