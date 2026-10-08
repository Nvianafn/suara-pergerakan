# Deployment Azure — persiapan

Target PRD 7: GHCR → Azure Container Apps Consumption + Azure SQL + R2.
Status: image production lolos smoke test lokal; CI SQL Server GitHub Actions
lolos pada commit `407319c`. Login lokal berhasil pada 8 Oktober 2026.
Resource group `rg-suara-pergerakan` dan environment Consumption
`env-suara-pergerakan` (Indonesia Central, logs destination none) sudah dibuat.
Azure SQL `suara_pergerakan` berhasil dibuat di East Asia: `useFreeLimit=true`,
`freeLimitExhaustionBehavior=AutoPause`, status Online. Free offer di Indonesia
Central ditolak dengan `ProvisioningDisabled`; kuota saja tidak menjamin offer.
Container App web, migration, dan bootstrap produksi belum dijalankan.
Environment GitHub `production` tersedia dan variables OIDC sudah dikonfigurasi.
Workflow `azure-connection.yml` memverifikasi federated login dan akses resource.
Verifikasi OIDC berhasil pada run `37716091270` setelah subject federasi memakai
claim persis `repo:Nvianafn@147637319/suara-pergerakan@1408415641:environment:production`.
Image GHCR berhasil dipublikasikan pada run `37716095369` dan pull terverifikasi:
`ghcr.io/nvianafn/suara-pergerakan@sha256:a3cb3e6c3378740097ba12b9e9ccadb4a6f208db3f12e971d307011efb839f98`.
Koneksi langsung ke Azure SQL menggunakan image ini berhasil dengan
`Encrypt=yes;TrustServerCertificate=no`. Firewall bootstrap sementara hanya
mengizinkan IP lokal; hapus setelah bootstrap selesai.
Container App `suara-pergerakan` berhasil dibuat di Indonesia Central, tetapi
belum siap menerima penggunaan normal (runtime secrets/bootstrap belum selesai).
Pembuatan migration Job ditolak `ExpressEnvironmentResourceNotSupported` pada
environment tersebut. Environment Consumption `env-suara-eastasia` sedang
disiapkan untuk memverifikasi dukungan Jobs, dekat dengan region database.
Jangan menyatakan deployment selesai sebelum migration Jobs, kredensial DB
runtime terpisah, bootstrap admin dan pemeriksaan aplikasi berhasil.

## Deployment pertama — 8 Oktober 2026

- Cleanup resource percobaan selesai setelah memeriksa dependensi: app
  `suara-pergerakan`, environment `env-suara-pergerakan`/`env-suara-eastasia`,
  serta server `sql-suara-21f28aeb` (hanya master, tanpa database organisasi)
  telah dihapus. Resource production tersisa web, migration Job, server SQL
  East Asia beserta database; environment bersama tetap dipakai.
- Sesudah cleanup, `rayonsaintek.com` homepage/login dan `ashofah.me` HTTP 200.
  Scale web tetap min 0/max 1. Firewall SQL tersisa `shared-environment-egress`;
  tidak ada rule runner/bootstrap sementara. Default branch `develop` memuat
  cron backup, tetapi run schedule pertama belum terjadi saat pemeriksaan.
- Custom domain `https://rayonsaintek.com` aktif dengan Azure managed certificate
  `mc-env-ashofah-wo-rayonsaintek-com-5288`, binding SNI. DNS apex A menuju
  `70.153.96.216`, DNS-only, TXT `asuid` dipertahankan untuk verifikasi.
  Domain R2 publik dipindah ke `https://assets.rayonsaintek.com`; HTTPS objek
  terverifikasi. APP_URL dan R2_PUBLIC_URL runtime sudah diperbarui.
- Smoke test custom domain: homepage, login, forgot-password, kepengurusan,
  karya, kegiatan, dan built CSS/JS HTTP 200; HTTP→HTTPS 301; guest `/admin`
  diarahkan ke login. Login pertama sempat HTTP 500 setelah revision update,
  dua pemeriksaan ulang berhasil 200. Foto legacy ditunda atas arahan pemilik.
- Workflow deploy digest end-to-end berhasil pada run `37722967845`: OIDC,
  migration Job, update web dan HTTP probe semuanya sukses. Domain Azure
  sementara aktif; custom domain aset/website masih menunggu konfigurasi DNS.
- Environment bersama yang disetujui: `rg-ashofah-world/env-ashofah-workload`,
  mode WorkloadProfiles, profil Consumption. Resource app/jobs tetap berada
  di `rg-suara-pergerakan`; aplikasi `ashofah-web` tetap tersendiri.
- API `2026-07-01` memperlihatkan `environmentMode`; CLI yang digunakan belum
  memperlihatkan properti ini. Dua environment percobaan dibuat sebagai Express.
  Pembuatan environment standar baru ditolak kuota global subscription.
- Web: `suara-web`, min 0/max 1, 0,25 vCPU/0,5 GiB, HTTPS-only.
  URL sementara: https://suara-web.wonderfulgrass-00f3abce.indonesiacentral.azurecontainerapps.io.
- Job `suara-migration` pada digest release berhasil (`suara-migration-ckg8f32`).
  Job bootstrap super admin berhasil (`suara-bootstrap-j7rmcgj`). Login mengarah
  ke wajib ganti password; password sementara disimpan privat di mesin lokal.
- Web memakai contained SQL user `suara_web` (db_datareader/db_datawriter),
  bukan admin migrasi. APP_KEY stabil; R2/SMTP secrets runtime tidak di-image.
- Firewall SQL mengizinkan egress teramati `70.153.49.16` untuk environment bersama.
  IP ini bukan jaminan seluruh IP masa depan; verifikasi tiap revision/restart.
- OIDC mendapat Contributor di resource group proyek dan Reader pada environment
  bersama saja. Workflow deploy manual memakai digest, migrasi satu job, update
  web, dan HTTP check; acceptance login/media serta backup tetap perlu diperiksa.
- Homepage dan login HTTP 200. Tes email Azure ditolak Brevo `525 Unauthorized
  IP address`; otorisasi egress di Brevo diperlukan sebelum reset dianggap lolos.
- Backup/export/restore, media production, custom domain dan cleanup resource
  percobaan belum selesai. Deployment belum memenuhi seluruh acceptance PRD.
- Setelah IP Brevo diotorisasi, submission reset production berhasil HTTP 200
  dengan notifikasi pengiriman; konfirmasi inbox dan reset oleh pemilik masih
  diperlukan. Dua disk R2 dari runtime Azure lolos put/get/delete objek unik
  `deployment-check`, dan cleanup diverifikasi. Ini tes storage, bukan acceptance
  lengkap upload CMS/foto privat.
- Backup eksternal pertama dan import SQL Server lokal berhasil pada 8 Oktober
  2026 (21 tabel/5 filtered index/17 FK/11 CHECK; maintenance 83 detik). Rincian
  scheduler, retensi, batas verifikasi, dan restore ada di `BACKUP_OPERATIONS.md`.
- Job bootstrap dihapus setelah sukses untuk menghapus secrets password awal
  dari resource operasional. Firewall bootstrap lokal dan egress environment
  percobaan juga dihapus; rule environment bersama tetap digunakan.
Build GHCR pertama berhasil membangun image tetapi smoke check terlalu cepat
mengakses port saat startup (connection reset); retry mencakup error startup.

## Hasil inspeksi subscription dan biaya

- Subscription `Azure for Students` aktif, `quotaId=AzureForStudents_2018-01-01`,
  dengan `spendingLimit=On`. Saldo kredit belum berhasil diambil via API;
  periksa saldo/masa berlaku lewat halaman benefit student di portal.
- Policy mengizinkan Malaysia West, East Asia, Australia East, Indonesia Central,
  dan India South Central. Indonesia Central menjadi kandidat awal.
- Resource `rg-ashofah-world` sudah memiliki tiga app/environment Consumption;
  resource tersebut milik proyek lain. Allowance gratis Container Apps dibagi
  antar resource dalam subscription, bukan jatah terpisah setiap app.
- SQL di Indonesia Central berstatus Available. Kuota regional SQLDB 40 vCore;
  `FreeLimitQuota` 1 dengan pemakaian 0. Kuota bukan bukti final bahwa konfigurasi
  free offer akan diterima; respons provisioning harus diperiksa.
- Dokumentasi SQL free offer menyebut 100.000 vCore-second/bulan, data 32 GB,
  backup 32 GB. Gunakan `useFreeLimit=true`,
  `freeLimitExhaustionBehavior=AutoPause`, backup Local. Saat kuota habis database
  berhenti sampai bulan berikutnya. Students Starter tidak kompatibel; subscription
  ini Azure for Students. Dokumentasi menyebut sampai 10 DB, tetapi kuota API
  subscription saat pemeriksaan masih 1.
- Retail API Indonesia Central (USD, 8 Oktober 2026): vCPU aktif
  $0,000028/detik, RAM aktif $0,000004/GiB-detik, request $0,40/juta.
  Pada 0,25 vCPU/0,5 GiB, satu jam aktif sekitar $0,0324 sebelum allowance.
  Contoh 30 jam aktif/bulan sekitar $0,972; 24 jam aktif × 30 hari sekitar
  $23,328. Ini biaya compute bruto ilustratif, bukan total tagihan.
- Container Apps Consumption menyediakan allowance bulanan 180.000 vCPU-second,
  360.000 GiB-second, dan 2 juta request. Logs, jobs, transfer dan pemakaian
  proyek lain turut memengaruhi total. Min replica 0 tidak menjamin biaya nol.
- Hindari profil Dedicated/private endpoint untuk konfigurasi awal hemat;
  log retention/ingestion harus ditetapkan saat provisioning.

Referensi: [SQL free offer](https://learn.microsoft.com/azure/azure-sql/database/free-offer),
[Container Apps pricing](https://azure.microsoft.com/pricing/details/container-apps/),
[Retail Prices API](https://prices.azure.com/api/retail/prices).

## Login lokal

Azure CLI dipasang terisolasi dalam `/tmp/opencode/azure-cli-venv`, bukan
dependency aplikasi. Jalankan:

```sh
/tmp/opencode/azure-cli-venv/bin/az login --use-device-code
/tmp/opencode/azure-cli-venv/bin/az account list --output table
```

Pilih Azure for Students. Periksa kredit/kuota di portal sebelum provisioning.
Jangan memasukkan login/cache Azure atau credential ke Git.

## Image

Workflow `.github/workflows/ci.yml` menguji PR serta push `develop`/`main`
dengan SQL Server 2022. `.github/workflows/image.yml` mempublikasikan image
setelah push `main` atau pemicu manual pada branch yang dipilih.
Repository saat persiapan memakai default branch `develop`; build pertama
dipicu manual pada `develop`. Publikasi bukan deployment otomatis.

Nama package: `ghcr.io/nvianafn/suara-pergerakan`, tag `sha-<commit>`.
Digest ditulis ke job summary. Package baru bisa privat secara default;
visibilitas/pull credential harus diverifikasi sebelum Container Apps dibuat.

```sh
docker build -f docker/production/Dockerfile -t suara-pergerakan-production:local .
```

Nginx/PHP-FPM melayani port 8080; `/healthz` tidak mengakses SQL/R2.
Entry point melakukan cache konfigurasi/route/view setelah env runtime tersedia,
tanpa menjalankan migrasi. Job dapat mengganti command dengan `php artisan ...`.
Media permanen memakai R2; sessions/cache menggunakan database. APP_KEY stabil,
APP_ENV=production, APP_DEBUG=false, LOG_CHANNEL=stderr. Secrets tidak di-build.

## Sebelum deploy

- Verifikasi image lokal dan migration target SQL.
- Cek free offer Azure SQL dan region; tentukan budget serta retensi log.
- Siapkan GHCR tag SHA/digest dan OIDC GitHub→Azure.
- Buat app minReplicas=0/maxReplicas=1, jobs migration/backup terpisah.
- Pisahkan kredensial DB web dari migration; batasi firewall SQL.
- Verifikasi IP outbound untuk Brevo; izin lokal bukan bukti izin Azure.
- Pasang assets.rayonsaintek.com untuk R2 sebelum memindahkan domain website.
- Verifikasi backup eksternal/restore dan smoke check sebelum domain cutover.
