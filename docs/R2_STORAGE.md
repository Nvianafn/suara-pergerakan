# Media Cloudflare R2

Konfigurasi runtime (credential hanya pada secrets/environment, bukan repository):

```dotenv
PUBLIC_MEDIA_DISK=r2_public
PRIVATE_MEDIA_DRIVER=s3
R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
R2_ACCESS_KEY_ID=<secret>
R2_SECRET_ACCESS_KEY=<secret>
R2_PUBLIC_BUCKET=pmiisaintek-assets
R2_PRIVATE_BUCKET=pmiisaintek-private
R2_PUBLIC_URL=https://<custom-domain-aset>
```

Endpoint S3 tidak menyertakan path bucket. Token membutuhkan Object Read & Write pada kedua bucket. Bucket privat tidak boleh memiliki custom domain atau public development URL. Foto anggota/Pembina tetap dilayani controller aplikasi yang memeriksa izin setiap request.

## Pindah dari disk lokal

Sebelum mengganti `PUBLIC_MEDIA_DISK`, jalankan:

```bash
php artisan media:copy-public-to-r2
```

Command memverifikasi hash file, mempertahankan sumber, dan menolak menimpa key tujuan dengan isi berbeda. Command juga menolak bila folder foto anggota/Pembina legacy masih ditemukan di disk publik; selesaikan privatization terlebih dahulu. Jalankan ulang aman untuk file identik. Setelah berhasil, ganti disk dan refresh config cache/runtime sesuai deployment.

Cleanup menyimpan nama disk saat pekerjaan dibuat sehingga retry tetap menghapus objek di disk asal. Sebelum cutover, selesaikan cleanup tertunda; source lokal dipertahankan untuk pemeriksaan dan rollback terencana.

## Verifikasi development 7 Oktober 2026

- Kedua bucket nyata lolos upload, baca, dan hapus melalui S3.
- Gambar nyata diproses ulang ke WebP: publik 1600×800, privat 1200×600; payload tambahan sumber tidak ikut tersimpan.
- Aset publik lewat `https://rayonsaintek.com` memberi HTTP 200 dan byte identik; tes container memakai IPv4 karena koneksi default sebelumnya timeout.
- Semua objek tes dihapus. Disk publik lokal hanya berisi `.gitignore`, sehingga tidak ada aset lama untuk disalin.
- Runtime lokal memakai `PUBLIC_MEDIA_DISK=r2_public`. Domain utama saat ini diarahkan ke R2; sebelum website dipasang pada domain utama, hubungkan `assets.rayonsaintek.com` ke bucket dan perbarui URL aset.
- Tes otomatis SQL Server: 82 test / 429 assertion lulus. Build frontend lulus; audit dependency masih mencatat 16 temuan (5 moderate, 11 high).

Verifikasi lanjutan: foto anggota/Pembina melalui controller Laravel dengan objek R2 nyata lulus 1 test / 33 assertion, mencakup izin, penempatan Pembina, pencabutan persetujuan, penolakan admin_biro, byte stream, dan cache privat/no-store. Kedua bucket tetap dapat dibaca setelah container aplikasi force-recreate; objek tes dihapus dan homepage kembali HTTP 200.

Tes integrasi nyata dijalankan secara eksplisit pada database test, terpisah dari suite rutin agar CI tidak membutuhkan secrets R2:

```bash
/tmp/opencode/docker-compose --env-file /tmp/opencode/suara-sqlserver.env run --rm --no-deps -e DB_DATABASE=suara_pergerakan_test -e RUN_R2_INTEGRATION=1 app vendor/bin/phpunit -c phpunit.sqlserver.xml tests/Integration/R2PhotoAccessTest.php
```

Cleanup foto privat sekarang masuk antrean durable, termasuk rollback upload; kegagalan delete dipertahankan untuk retry. Suite SQL Server sesudah perubahan: 83 test / 436 assertion lulus.
