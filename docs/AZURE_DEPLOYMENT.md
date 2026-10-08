# Deployment Azure — persiapan

Target PRD 7: GHCR → Azure Container Apps Consumption + Azure SQL + R2.
Status: image production sedang diverifikasi lokal; resource Suara Pergerakan
di Azure belum dibuat. Login lokal berhasil pada 8 Oktober 2026.

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
