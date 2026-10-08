# Backup production — Azure SQL + R2

## Tujuan dan jadwal

- Tujuan privat: `pmiisaintek-backups`, token khusus yang tidak dapat membaca
  bucket media. Token aplikasi tidak dapat membaca/menghapus bucket backup.
- Runner: GitHub Actions, `.github/workflows/backup.yml`, environment `production`.
  Tidak ada Container Apps Job baru atau database Azure berbayar untuk export.
- Jadwal UTC `0 19 * * *` = 02.00 WIB hari berikutnya. Database setiap hari;
  kedua bucket media setiap Minggu UTC serta tanggal 1 UTC, dan manual sesudah
  unggahan besar. GitHub menjadwalkan workflow hanya dari default branch dan
  jadwal dapat terlambat. Aktifkan notifikasi email kegagalan Actions untuk dua
  penanggung jawab; approval environment yang menghambat schedule harus dihindari.
- Retensi: 14 titik harian, 8 titik mingguan DB+media, 12 titik bulanan DB+media.
  Snapshot dapat memenuhi lebih dari satu kategori. Retensi memverifikasi arsip
  terbaru sebelum menghapus titik lama. Prefix tanpa manifest adalah parsial,
  tidak dianggap sehat dan belum dibersihkan otomatis.
- Azure SQL PITR: retensi native terkonfirmasi 7 hari. Import lokal tidak membuktikan
  PITR Azure; pengujian PITR tersendiri masih diperlukan.

## Konsistensi dan recovery

Workflow backup dan deploy memakai concurrency group `suara-production-release`.
Koordinator menolak migration Job yang masih Running, mencatat revision aktif,
membuka firewall sementara hanya untuk IP runner, menonaktifkan semua revision
aktif, menunggu 30 detik, lalu memeriksa sesi SQL `suara_web` tidak aktif.
Export memegang session application lock `suara-operations`. Writer luar workflow
dan akses SQL manual harus dihentikan sebelum operasi; lock hanya melindungi
operasi lain yang memakai resource lock yang sama.

SqlPackage 170.5.96.0 menghasilkan BACPAC, lalu objek final kedua bucket disalin
ke prefix versi tanpa penghapusan sinkronisasi. Upload/readback/checksum SHA-256
diverifikasi sebelum manifest completion marker ditulis. Retensi berjalan setelah
layanan diaktifkan kembali. Jangan memakai manifest parsial untuk restore.

`finally` dan step Actions `always()` menghentikan exporter sebelum mengaktifkan
revision kembali dan membersihkan firewall. Jika runner hilang total, operator
harus mengaktifkan revision secara manual dan menghapus rule `backup-runner-*`
setelah memastikan exporter sudah berhenti. Status dan durasi tersedia di log
Actions. Jangan mencetak `.env`, secrets, connection string, atau log SqlPackage.

## Verifikasi 8 Oktober 2026

- Workflow end-to-end `37722153027` sukses: export/copy, recovery, HTTP health,
  dan retensi. Run pertama `37721875445` berhasil export tetapi gagal pada
  recovery duplikat; diperbaiki dengan recovery idempotent (`00f4b33`). Jadwal
  cron sudah dikonfigurasi; eksekusi terjadwal pertama masih perlu diperiksa.
- Snapshot pertama: `snapshots/2026/10/08/030748-8a4cd9ae17b2/manifest.json`.
- BACPAC 15.294 bytes, 2 objek media; private bucket saat ini kosong.
- Export + maintenance dari lokal: 83 detik, layanan direaktivasi.
- Download dari backup, verifikasi checksum, import SQL Server 2022 lokal berhasil.
  Target lokal memerlukan `contained database authentication = 1` untuk contained
  user Azure SQL. Database hasil import: `suara_restore_20261008031101`.
- 21 tabel, 5 filtered indexes, 17 FK, 11 CHECK, 36 migration, 1 akun cocok dengan
  sumber. Data konten/anggota/periode produksi masih kosong, jadi ini belum
  acceptance restore seluruh alur organisasi atau bukti kapasitas data besar.
- Retensi smoke test: 1 sehat dipertahankan, 0 dihapus. Uji pemilihan/penghapusan
  lintas kategori masih diperlukan sebelum retensi memiliki banyak snapshot.
- Objek legacy `foto-anggota/...jpeg` ditemukan pada bucket publik. Pemilik
  mengonfirmasi ini foto anggota dari deployment VPS lama, bukan Pembina;
  foto Pembina lama belum ada. Referensi DB dan status persetujuan belum
  diketahui; migrasi ke privat perlu diselesaikan sebelum go-live. Jangan
  menganggap bucket publik telah lolos audit privasi hanya dari tes backup.
- Aplikasi image production terhadap database hasil import lokal: homepage,
  karya, kegiatan, kepengurusan, login super admin dan halaman CMS utama lolos
  HTTP. Dengan fixture sintetis lokal terpisah dari data asli, foto anggota dan
  Pembina lolos akses internal, publik setelah consent/penempatan, serta 404
  setelah consent dicabut bagi guest dan admin_biro. Struktur/arsip fixture
  memberi HTTP 200. Ini tidak membuktikan pemulihan konten organisasi yang
  belum ada di snapshot atau restore media ke bucket R2 pemulihan tersendiri.

## Restore check lokal

`docker/operations/restore-check.php` mengambil manifest/BACPAC/media dari backup,
memverifikasi byte/checksum, import ke database lokal baru, lalu membandingkan
constraint/index dan jumlah record nonoperasional dengan sumber. Jalankan dari
image operasi, network SQL lokal, env file privat dengan `RESTORE_PASSWORD`,
`BACKUP_MANIFEST`, dan mount direktori `/restore` privat. Tidak mengimpor ke SQL
production. File hasil restore mengandung data privat dan harus tetap dibatasi.

Masih perlu: restore media ke bucket pemulihan terpisah, jalankan aplikasi terhadap
hasil restore, acceptance login/peran/arsip/foto, uji PITR, cadangan APP_KEY/secrets
terpisah, penanggung jawab cadangan dan notifikasi kegagalan, serta ukur kapasitas
dan durasi setelah data organisasi diisi. Kuota R2 dibagi dengan media aktif.
