# Lingkungan development SQL Server

Container ini untuk development, bukan image deployment Azure. Membutuhkan Docker Compose dan host x86_64 yang mendukung image SQL Server dengan memori yang memadai. Versi driver PECL perlu dikunci setelah build PHP 8.4 berhasil diverifikasi.

1. Tetapkan `SQLSERVER_SA_PASSWORD` di shell; gunakan password yang memenuhi kompleksitas SQL Server. Jangan simpan password di repo.
2. `docker compose build app`
3. `docker compose up -d sqlserver`
4. Setelah SQL Server siap, buat database `suara_pergerakan` lewat SQL client. Jika image menyediakan mssql-tools18:

```bash
docker compose exec sqlserver bash -lc '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -Q "IF DB_ID(N'\''suara_pergerakan'\'') IS NULL CREATE DATABASE suara_pergerakan"'
docker compose run --rm app composer install
docker compose run --rm app mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions
docker compose run --rm app php artisan migrate --pretend
```

Migration incremental mengubah relasi arsip menjadi NO ACTION dan menambahkan filtered index periode aktif, penempatan, serta email anggota. Build dan eksekusi migration SQL Server masih perlu dibuktikan; pemeriksaan SQL `--pretend` saja tidak cukup.

Setelah skema kompatibel dan migration berhasil:

```bash
docker compose up -d app
```

URL lokal container: http://127.0.0.1:8001. Build frontend tetap memakai `npm ci` dan `npm run build` pada host. Volume `vendor` memisahkan dependency container dari vendor host. Sertifikat self-signed hanya dipercaya pada development; Azure SQL harus memakai koneksi terenkripsi dengan verifikasi sertifikat.

## Database pengujian terpisah

Buat `suara_pergerakan_test` pada server development, kemudian jalankan:

```bash
docker compose exec sqlserver bash -lc '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -Q "IF DB_ID(N'\''suara_pergerakan_test'\'') IS NULL CREATE DATABASE suara_pergerakan_test"'
docker compose run --rm -e DB_DATABASE=suara_pergerakan_test app vendor/bin/phpunit -c phpunit.sqlserver.xml
```

Konfigurasi ini memaksa koneksi SQL Server dan nama database test; suite memakai RefreshDatabase sehingga database test harus khusus dan boleh dihapus isinya. Host, username, password, dan opsi TLS diwarisi dari environment Compose. Hasil suite SQLite dicatat terpisah dari hasil SQL Server.

Jika plugin `docker compose` belum terpasang, executable Compose resmi dapat digunakan sebagai pengganti dengan argumen yang sama. Verifikasi checksum rilis sebelum menjalankannya. Credential development disimpan di luar repo dan diteruskan lewat `--env-file`.
# Batas upload development

Compose memasang `docker/development/uploads.ini`: 5 MB per file, 48 MB batas request PHP (memberi ruang overhead multipart), dan 21 file untuk 20 foto galeri + thumbnail. Aplikasi menegakkan total file 40 MB dan maksimal 20 foto galeri per kegiatan, termasuk foto lama. Setelah mengubah konfigurasi mount, buat ulang service app dengan `up -d --no-deps app` menggunakan env-file development. Nilai ini juga perlu diterapkan pada runtime/ingress production.
