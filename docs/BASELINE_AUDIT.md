# Audit Awal Suara Pergerakan

Tanggal: 7 Oktober 2026. Acuan: `PRD_v1.6.md`.

## Repository
- Source: `upstream` → https://github.com/Nvianafn/pmii-rayon-saintek-laravel.git
- Destination: `origin` → https://github.com/Nvianafn/suara-pergerakan.git
- Branch kerja: `develop`; riwayat source dipertahankan.
- Repo tujuan kosong saat pemeriksaan awal. Belum dilakukan commit/push baseline.
- Data VPS lama tidak tersedia; data pengurus akan diisi ulang melalui CMS.

## Hasil setup dan smoke test
- Dependency diinstal dari `composer.lock` dan `package-lock.json`, tanpa upgrade.
- Laravel terkunci 11.54.0, Livewire 3.8.2, Vite 5.4.21.
- PHP lokal 8.5.10; iconv tersedia tetapi belum aktif global. Perintah memakai `php -d extension=iconv`.
- Direktori runtime yang tidak terbawa clone dipulihkan, dengan `.gitignore` agar tetap tersedia setelah clone berikutnya.
- SQLite baseline: `/tmp/opencode/suara-pergerakan-baseline.sqlite`, hanya data dummy lokal.
- Seluruh 19 migration serta DatabaseSeeder berhasil pada SQLite.
- `npm run build` berhasil.
- GET `/`, `/tentang`, `/biro`, `/kepengurusan`, `/kegiatan`, `/karya`, `/kontak`, `/login` menghasilkan 200. `/admin` mengarah ke `/login` untuk pengunjung.
- Hasil ini tidak membuktikan SQL Server, alur login/CRUD penuh, upload, atau deployment production.
- GD belum dimuat pada PHP lokal; Docker dan driver sqlsrv belum tersedia pada pemeriksaan awal. Upload gambar dan target SQL Server perlu lingkungan pendukung.
- npm melaporkan 16 advisory (5 moderate, 11 high); evaluasi dependency dilakukan saat tahap upgrade.

## Menjalankan baseline lokal

`.env` lokal dibuat dari `.env.example` dan APP_KEY sudah dibuat; tidak masuk Git.

```bash
php -d extension=iconv /usr/bin/composer install --no-interaction --prefer-dist
npm ci
npm run build
```

Untuk database dummy baru (jangan seed ulang database yang sudah berisi data):

```bash
touch /tmp/opencode/suara-pergerakan-baseline.sqlite
DB_CONNECTION=sqlite DB_DATABASE=/tmp/opencode/suara-pergerakan-baseline.sqlite php -d extension=iconv artisan migrate --seed --no-interaction
```

Jalankan server:

```bash
DB_CONNECTION=sqlite DB_DATABASE=/tmp/opencode/suara-pergerakan-baseline.sqlite php -d extension=iconv artisan serve --host=127.0.0.1 --port=8000
```

Perintah di atas untuk Bash. Pada fish gunakan `env DB_CONNECTION=sqlite DB_DATABASE=/tmp/opencode/suara-pergerakan-baseline.sqlite php -d extension=iconv artisan ...`.

URL: http://127.0.0.1:8000. Akun dummy lokal dari seeder: `admin@pmii-saintek.id`, password `password`; tidak untuk production.

## Kesenjangan yang sudah terlihat di kode

| Area | Kondisi awal | Tindak lanjut sesuai PRD |
|---|---|---|
| Stack | Laravel 11, MySQL; migration menggunakan enum dan perubahan skema lama | Upgrade bertahap dan validasi SQL Server |
| Struktur | Migration Agustus menghapus `level`, menambah `is_ketua`; BPH direpresentasikan sebagai biro bertipe bph | Selaraskan model/form/view/migration dengan struktur PRD sebelum Pembina |
| Pembina | Belum ada profil/penempatan | Profil mandiri + relasi periode, orang luar diperbolehkan |
| Hak akses | Grup admin hanya menerima super_admin/admin | Policy/Gate per objek dan cakupan admin_biro |
| Periode | Admin dapat mengubah is_aktif; controller menonaktifkan periode lain tanpa transaction | Aktivasi khusus super_admin, transaction dan constraint |
| Media | ImageService menyimpan gambar di disk public | R2 publik/privat dan controller foto berizin |
| Arsip/penghapusan | Controller menghapus beberapa record dan media secara langsung | Selaraskan soft delete, proteksi relasi, snapshot dan audit |
| Publikasi | published_at menerima input; unpublish mengosongkan tanggal | Pertahankan publikasi pertama, tanpa input manual |
| Infrastruktur | Panduan deployment lama VPS | Container, GHCR, Azure, sesi persisten dan backup eksternal |
| Pengujian | Tidak ada direktori tests di source awal | Test bermakna untuk fitur/constraint saat implementasi |

Audit lanjutan perlu memeriksa sanitasi rich text, validasi upload, alur autentikasi, empty state, dan seluruh aturan PRD; tabel ini bukan daftar lengkap.

## Urutan pengembangan berikutnya
1. Validasi dependency upgrade, environment container PHP, dan SQL Server.
2. Selaraskan skema organisasi dan aturan periode; bangun hak akses dasar.
3. Tambahkan Pembina, privasi media, riwayat, dan arsip.
4. Lengkapi konten/public frontend, R2, deployment awal, dan verifikasi backup.

R2 publik yang disepakati: `pmiisaintek-assets`. Bucket privat `pmiisaintek-private` masih perlu dibuat. Azure for Students/benefit kredit dan kuota harus diperiksa sebelum provisioning.

## Penyelarasan tahap pertama
- Framework diperbarui ke Laravel 13.35.0, Livewire 3.8.10, Tinker 3.0.2, PHPUnit 12.5.38. Composer platform ditetapkan PHP 8.4.0 sesuai target image.
- Panduan upgrade Laravel 11→12 dan 12→13 ditinjau. Konfigurasi session eksplisit memakai serialisasi JSON.
- Aktivasi periode dibatasi super_admin di server dan form; admin yang mengedit metadata mempertahankan status aktif. Penyimpanan/aktivasi dibungkus transaction; filtered unique index menjamin satu periode aktif pada SQLite/SQL Server.
- Penghapusan periode aktif atau berpengurus ditolak. Proteksi kegiatan/karya/Pembina akan dilengkapi bersama relasi periode pada modul-modul itu.
- PHPUnit: 8 test, 25 assertion lulus (halaman publik, login, akses periode, pergantian periode, constraint, penghapusan aktif). Database pengujian masih SQLite.
- Lingkungan development PHP 8.4 + ODBC/sqlsrv dan SQL Server disiapkan di `compose.yaml`; belum build/run karena Docker belum tersedia. Petunjuk: `docs/SQLSERVER_DEVELOPMENT.md`.
- Struktur BPH/biro dan Pembina belum diubah pada tahap ini. Skema SQL Server belum dinyatakan kompatibel/teruji.

## Penyelarasan struktur kepengurusan
- Migration lanjutan mengembalikan level `bph`, `ketua_biro`, `anggota_biro`, mengubah posisi BPH lama menjadi tanpa biro, dan mengisi snapshot `biro_nama` untuk pengurus biro.
- Model, validasi, CMS kepengurusan dan halaman publik memakai level baru. `is_ketua` dipertahankan sebagai kolom kompatibilitas tampilan dan diturunkan dari level, bukan input pengguna.
- Snapshot diperbarui ketika relasi biro diganti, bukan ketika biro berganti nama.
- Unique index lama yang melarang posisi sama lintas biro diganti dengan dua filtered unique index pada SQLite/SQL Server. SQL Server juga mendapat CHECK level/biro; belum diuji langsung pada SQL Server.
- Unit BPH lama pada tabel biro tetap dipertahankan sementara untuk kompatibilitas rollback/relasi lama; daftar biro publik menggunakan scope unitBiro.
- Migration berhasil pada database baseline yang sudah terisi. PHPUnit lulus: 11 test, 32 assertion.
- Berikutnya: profil/penempatan Pembina dan privasi foto, penyelarasan FK NO ACTION serta constraint skema lainnya, dan uji SQL Server.

## Pembina: profil dan penempatan awal
- Tabel `pembina` dan `periode_pembina` ditambahkan dengan FK NO ACTION, filtered unique relasi anggota terisi dan unique pasangan profil-periode.
- CMS kepengurusan dapat membuat profil orang luar/anggota, menggunakan ulang profil, mengedit nama/keterangan/persetujuan dan urutan, serta melepas penempatan tanpa menghapus profil.
- Halaman publik menampilkan Pembina sebelum BPH, menggunakan placeholder dan menyembunyikan keterangan tanpa persetujuan. Waktu persetujuan dicatat server.
- Periode yang berisi Pembina ditolak penghapusannya. Migration berhasil pada baseline lokal.
- PHPUnit: 14 test, 47 assertion. Test penolakan admin_biro memakai role pada instance user (belum tersimpan di database karena enum role lama belum diperluas); ini menguji middleware, bukan kesiapan akun admin_biro end-to-end.
- Belum selesai: upload/controller foto privat R2, riwayat aktivitas, penghapusan profil tidak terpakai, relasi anggota yang dapat diedit, akses halaman pengurus per periode sesuai URL PRD, dan pengelolaan profil sebelum ada periode. Bagian CMS awal akan dirapikan setelah alur dan privasi lengkap.

## Media privat dan riwayat Pembina
- Adapter S3 dan disk `r2_public`/`r2_private` ditambahkan; kredensial memakai environment. R2 aktual belum dikoneksikan. Untuk development tanpa R2 gunakan `PRIVATE_MEDIA_DRIVER=local` di `.env`; production tetap s3 dengan bucket privat tanpa akses publik.
- Foto dapat diunggah/diganti/dihapus melalui edit profil Pembina: JPG/PNG/WebP maksimal 5 MB, resize dan re-encode WebP. Upload baru dibersihkan jika penyimpanan database gagal; foto lama dibersihkan setelah transaksi berhasil.
- `/media/pembina/{pembina}` men-stream foto dari storage privat tanpa presigned URL. Publik wajib memiliki izin dan penempatan; admin/super_admin boleh melihat untuk CMS. Respons login/tanpa izin no-store, publik berizin cache privat maksimal 1 jam. Bypass cache CDN pada rute media harus ditetapkan saat deployment.
- Riwayat mencatat pembuatan/perubahan/penghapusan profil dan penambahan/pelepasan penempatan. Penghapusan profil digunakan ditolak; profil tidak terpakai dapat dihapus dari CMS.
- PHPUnit: 17 test, 67 assertion lulus, termasuk akses foto, pencabutan izin, validasi file palsu, audit dan proteksi penghapusan. Uji stream memakai storage fake; upload/re-encode nyata memerlukan GD dan uji R2 aktual berikutnya.
- Pengujian SQL Server ditunda atas arahan pengguna; pengguna menyatakan Docker sudah dipasang.
- Masih perlu: foto privat anggota, halaman riwayat, riwayat penolakan akses, pengelolaan profil mandiri sebelum periode, edit relasi anggota, serta penyelarasan role admin_biro dan aturan akun aktif.

## Privasi anggota dan halaman riwayat
- Anggota memiliki `setuju_publikasi`, timestamp persetujuan, dan soft delete. Persetujuan default nonaktif; publik dan admin_biro tidak dapat mengakses foto tanpa izin. Anggota terhapus mengembalikan 404 pada rute media.
- Upload anggota menggunakan `PrivateImageService`; URL foto CMS/publik diarahkan ke `/media/anggota/{id}`. Penulisan database gagal membersihkan upload baru, penggantian berhasil membersihkan foto privat lama.
- Hapus anggota hanya super_admin dan ditolak jika ada pengurus, karya, user, atau profil Pembina terkait. Foto tetap privat ketika anggota di-soft-delete.
- Tindakan tulis anggota dan status izin dicatat dalam transaksi bersama perubahan data, tanpa kontak/NIM. `/admin/riwayat` tersedia melalui navigasi CMS untuk admin/super_admin.
- Command `php -d extension=iconv artisan media:privatize-member-photos` disiapkan untuk memproses ulang foto lama dari disk public lokal ke private, lalu menghapus salinan publik. Jalankan setelah GD dan private storage dikonfigurasi, sebelum membuka aplikasi ke publik. Foto yang sudah terlanjur berada di bucket publik eksternal perlu diinventarisasi terpisah. Command belum dijalankan pada foto nyata.
- Migration baseline lokal berhasil; PHPUnit 22 test/88 assertion, Pint, `npm ci`, `npm run build`, serta `git diff --check` lulus. npm melaporkan 16 temuan audit dependensi (5 moderate, 11 high); pembaruan frontend masih perlu ditangani. Output build tidak dimasukkan ke perubahan.
- Akun admin_biro dalam test masih memakai role in-memory karena skema role lama. Penyelarasan akun, riwayat penolakan akses, dan audit modul lain masih perlu dilanjutkan. Docker tersedia menurut pengguna; pengujian SQL Server tetap ditunda.

## Fondasi akun dan penolakan akses
- Migration memperluas role ke `admin_biro`, menambahkan FK biro NO ACTION dan status `is_active`. SQLite memakai trigger untuk kewajiban biro; SQL Server memakai CHECK (belum diverifikasi langsung).
- CMS akun dapat memilih admin_biro, biro, dan status aktif. Test admin_biro sekarang memakai akun yang tersimpan di database. Hak operasional admin_biro per objek/modul belum dibuka: grup admin masih dibatasi admin/super_admin sampai Policy terkait selesai.
- Login menolak akun nonaktif; middleware memeriksa status di setiap request CMS dan mencatat penolakan role/status berdasarkan nama rute tanpa query/data sensitif. Akun nonaktif tidak lagi memiliki akses internal foto tanpa persetujuan.
- Perubahan role/status tidak boleh menghilangkan super_admin aktif terakhir. Tindakan tulis akun tercatat tanpa password. Akun dengan referensi konten/riwayat ditolak penghapusannya dan dapat dinonaktifkan.
- Migration berhasil pada baseline SQLite berisi data. PHPUnit 25 test/104 assertion, Pint, npm ci/build, dan diff check lulus. Pengujian SQL Server tetap ditunda. Selanjutnya: Policy kegiatan/karya, dashboard dan tampilan anggota terbatas bagi admin_biro, serta pembatasan preview/penulisan per objek dan audit modul lainnya.

## Akses konten admin_biro
- Policy konten memeriksa akses create/update/view/delete serta mencatat penolakan. Kegiatan hanya biro sendiri; karya hanya draft buatan sendiri pada biro sendiri. Admin/super_admin tetap memiliki akses seluruh konten.
- Grup CMS dibuka untuk admin_biro pada dashboard, kegiatan, karya, preview dan daftar anggota terbatas. Modul organisasi/settings/riwayat/user tetap dibatasi role. Daftar anggota terbatas mengambil nama/angkatan/status tanpa NIM, HP, email atau foto privat.
- Payload admin_biro mengunci biro server-side, memaksa karya menjadi draft, menetapkan periode aktif saat create (ditolak jika tidak ada), dan mempertahankan periode record saat update. Form periode read-only untuk admin_biro; admin/super_admin dapat memilih periode/biro.
- Dashboard membatasi statistik/daftar terbaru pada biro akun. Preview memakai Policy yang sama dan rendering teks escaped.
- Kegiatan/karya memiliki periode, snapshot nama biro dan soft delete. Data lama dibiarkan tanpa periode agar tidak mengarang arsip. Soft delete mempertahankan media dan URL publik menjadi 404. Periode dengan konten (termasuk terhapus) tidak boleh dihapus; anggota dengan karya terhapus juga terlindungi.
- Audit pembuatan/perubahan/soft delete kegiatan/karya ditambahkan. Migration lokal berhasil; PHPUnit 28 test/124 assertion, Pint, npm ci/build dan diff check lulus.
- Masih perlu: edit deskripsi biro sendiri, logout otomatis akun nonaktif (saat ini akses ditolak 403), penghapusan permanen media hanya super_admin termasuk aksi galeri saat edit, perbaikan transaksi lifecycle upload konten, preview kaya konten, backfill periode arsip berdasarkan data terverifikasi, serta penyelarasan FK/constraint keseluruhan. SQL Server belum diuji sesuai arahan pengguna.

## Deskripsi biro, sesi nonaktif dan lifecycle media konten
- Admin_biro memiliki halaman deskripsi biro sendiri; Policy menolak biro lain dan payload hanya menerima deskripsi. Nama/urutan/logo/status tidak berubah meskipun dikirim lewat request. Perubahan dan penolakan tercatat.
- Middleware web mengakhiri sesi akun nonaktif pada request berikutnya: logout, invalidate session, regenerasi token, redirect login. Berlaku juga pada akses halaman publik/media oleh sesi tersebut.
- Penghapusan permanen galeri lewat edit kegiatan hanya super_admin; admin/admin_biro ditolak 403 sebelum perubahan data/upload, dan tombol hanya tampil untuk super_admin.
- Pembuatan/perubahan kegiatan beserta galeri berada dalam transaksi; upload baru dibersihkan saat gagal, penghapusan file lama dijalankan setelah commit. Thumbnail karya memakai cleanup kegagalan dan penghapusan lama setelah commit.
- PHPUnit 31 test/140 assertion, termasuk rollback akibat upload galeri gagal, perlindungan deskripsi dan penolakan hapus galeri. Pint, npm ci/build dan diff check lulus.
- Selanjutnya: CMS trash/restore/purge konten, retry cleanup storage setelah commit, audit modul organisasi/settings, proteksi hapus biro beserta FK dan arsip, validasi foto nyata GD/R2, serta deployment. SQL Server tetap ditunda.

## Sampah konten dan cleanup permanen
- `/admin/sampah` menampilkan karya/kegiatan terhapus dengan pagination terpisah. Admin/super_admin dapat memulihkan sebagai draft; karya juga mengosongkan published_at. Admin_biro ditolak.
- Hapus permanen hanya super_admin, hanya record soft-deleted, memakai transaksi dan lock record. Galeri kegiatan dihapus eksplisit; riwayat tindakan tetap tersimpan.
- Object key media dicatat ke `media_cleanup` dalam transaksi sebelum record dihapus. Cleanup dijalankan setelah commit; kegagalan tetap tercatat untuk retry lewat `artisan media:cleanup`, dijadwalkan setiap 10 menit. Scheduler deployment harus dijalankan agar retry otomatis bekerja. Saat ini disk konten masih public lokal sesuai implementasi awal; integrasi aset konten R2 masih perlu diselaraskan.
- Biro dengan kepengurusan, kegiatan/karya (termasuk sampah), atau akun terkait ditolak penghapusannya.
- Migration baseline berhasil; PHPUnit 35 test/161 assertion, Pint, npm ci/build, diff check lulus. Termasuk restore draft, purge media/galeri, penolakan role, dan kegagalan cleanup yang tetap dapat diulang.
- Masih perlu: memperluas cleanup durable ke penggantian thumbnail/penghapusan profil foto, audit organisasi/settings, aturan nonaktif biro dan FK historis, konfigurasi storage R2 nyata, scheduler deployment, serta pemeriksaan SQL Server (masih ditunda).

## Nonaktif biro dan audit organisasi/settings
- Biro memiliki `is_aktif` default true, dapat diubah lewat CMS admin/super_admin. Biro nonaktif disembunyikan dari grid landing dan `/biro`; URL detail tetap tersedia dengan label Arsip dan tanpa daftar pengurus aktif. Penempatan/konten arsip tidak dihapus.
- Tindakan tulis biro, periode dan pengurus dicatat bersama transaksi data. Settings memakai allowlist dan transaksi dengan audit nama kunci, bukan nilai atau kredensial.
- Penggantian/hapus logo biro serta penggantian/hapus foto Pembina dan penggantian foto anggota memasukkan file lama ke media_cleanup dalam transaksi. Kegagalan cleanup setelah commit dapat dicoba ulang lewat scheduler/command.
- Migration baseline berhasil; PHPUnit 37 test/174 assertion, Pint, npm ci/build dan diff check lulus. Pengujian SQL Server tetap ditunda.
- Masih perlu: audit otomatis penonaktifan periode lama saat aktivasi baru, snapshot/backfill konten lama, penyesuaian statistik biro aktif, cleanup durable thumbnail konten, halaman profil Pembina mandiri dan edit relasi anggota, penyelarasan FK/constraint historis, GD/R2 nyata serta deployment.

## Pengelolaan profil Pembina sebelum periode
- Bagian Pembina di CMS kepengurusan kini selalu tersedia, termasuk saat belum ada periode. Profil dapat dibuat/edit tanpa penempatan; tidak membuat anggota atau akun otomatis.
- Form profil digunakan bersama untuk profil tersimpan dan penempatan: relasi anggota/alumni dapat ditautkan/dilepas, unique anggota diperiksa dan anggota soft-deleted ditolak. Foto/izin anggota tidak disalin otomatis.
- Create profil mendukung foto privat `pembina/{id}/...webp` dengan cleanup saat transaksi gagal. Update profil tetap mempertahankan semua penempatan dan urutan; izin/keterangan berlaku lintas periode.
- `/admin/periode/{id}/pengurus` ditambahkan dengan tautan Kelola Pengurus dari daftar periode. Pesan penolakan penghapusan periode menyebut jumlah Pembina, pengurus, kegiatan dan karya termasuk sampah; konfirmasi lama yang mengklaim cascade penghapusan diperbaiki.
- PHPUnit 41 test/203 assertion, Pint, npm ci/build dan diff check lulus. SQL Server tetap belum diuji.
- Setelah server/harness restart, SQLite sementara sebelumnya tidak tersedia. Database demo dibentuk ulang dari migration/seeder di path yang sama; server preview localhost:8000 kembali berjalan, GET /login 200. Ini data demo baru, bukan pengujian upgrade populated baseline sebelumnya.

## Snapshot konten dan relasi arsip
- Trait snapshot pada kegiatan/karya mengisi nama biro saat create/perubahan biro_id, termasuk penulisan langsung lewat model. Edit nama biro atau judul konten tidak menulis ulang snapshot. Label kegiatan publik menggunakan snapshot.
- Migration mengisi snapshot konten lama yang masih kosong berdasarkan nama biro yang tersedia saat migration (bukan rekonstruksi nama historis). Periode konten lama tetap tidak ditebak.
- FK historis kepengurusan→anggota/periode/biro, kegiatan→biro/pembuat, karya→anggota/pembuat, foto→kegiatan dan user→anggota diubah menjadi NO ACTION. Purge galeri tetap eksplisit. SQLite rebuild menyimpan dan mengembalikan definisi index/trigger agar filtered uniqueness tidak berubah menjadi uniqueness biasa.
- Periode memiliki slug unik dengan backfill collision-safe; slug tetap saat nama diedit. URL `/kepengurusan/{periode-slug}` memilih arsip yang tepat dan URL tidak valid 404. CMS periode tetap memakai id.
- Filter periode pada kegiatan publik memakai slug, termasuk periode lama. Nama biro di halaman detail/list/landing kegiatan tidak mengikuti rename biro.
- PHPUnit 45 test/222 assertion, Pint, npm ci/build dan diff check lulus. Migration berhasil pada database demo berisi data. SQL Server belum dijalankan sesuai arahan pengguna; migration NO ACTION ini tidak membuktikan kompatibilitas SQL Server keseluruhan.
- Masih perlu: periode konten wajib setelah mapping data lama terverifikasi, published_at kegiatan, updated_by dan CHECK/JSON/consent constraint lain, author modes/featured karya, sanitasi HTML, cleanup thumbnail durable, audit pergantian periode otomatis, serta verifikasi GD/R2 dan deployment.

## Sanitasi HTML dan publikasi pertama
- HTMLPurifier ditambahkan dengan allowlist PRD: tanpa script/iframe/style/form, event handler, data/javascript URI; gambar hanya origin storage aplikasi atau URL aset R2 publik yang dikonfigurasi, tanpa path foto anggota/Pembina/controller privat. Tautan diberi rel noopener/noreferrer/nofollow. Teks biasa mempertahankan baris baru sebagai paragraf/br.
- Trait model menyaring rich text saat save karya/kegiatan; public detail dan preview juga menyaring saat render untuk melindungi row lama/import yang melewati model. `content:sanitize` membersihkan legacy termasuk soft-deleted tanpa mengubah metadata arsip/publikasi; dijalankan pada demo (6 row dibersihkan).
- published_at kegiatan ditambahkan. Waktu publikasi pertama diisi server dan tidak berubah saat edit/unpublish/republish/restore sampah. Input manual karya dihapus dan tidak digunakan request. Daftar kegiatan publik diurutkan published_at.
- Legacy published kegiatan serta karya published tanpa waktu memakai created_at sebagai fallback; bukan bukti tanggal publikasi historis. Draft yang dahulu di-unpublish dan kehilangan timestamp tidak dapat direkonstruksi otomatis.
- Percobaan admin_biro mengirim status published untuk karya kini ditolak 403 dan dicatat, menggantikan perilaku diam-diam memaksa draft.
- Migration dan sanitasi demo berhasil; test XSS, legacy render, trusted image origins, publikasi dan restore lulus. Rich-text editor visual, author modes dan featured masih tahap berikutnya. SQL Server tetap ditunda.
- Verifikasi akhir: PHPUnit 50 test/259 assertion, Pint, npm ci/build dan diff check lulus; Composer melaporkan tidak ada security advisory pada dependency PHP setelah penambahan HTMLPurifier.

## Verifikasi SQL Server lokal — 7 Oktober 2026
- Build development berhasil: PHP 8.4.26, sqlsrv/pdo_sqlsrv 5.13.3, ODBC 18, GD dan Composer dari lockfile. Compose standalone v5.6.0 di luar repo diverifikasi checksum resmi.
- Semua 32 migration dari nol dan seeder berhasil pada SQL Server 2022 Developer. Database development suara_pergerakan dan suara_pergerakan_test terpisah; development hanya berisi dummy baru.
- Perbaikan aktual: opsi TLS env kini dibaca konfigurasi SQL Server; CHECK level lama dilepas sebelum drop kolom; enum role diperbarui lewat CHECK eksplisit; filtered unique email membolehkan banyak NULL.
- Cast integer FK konten, biro akun, dan anggota Pembina menyelaraskan BIGINT SQL Server yang dibaca sebagai string. Cast created_by memperbaiki penolakan keliru pada preview/edit karya admin_biro.
- storage/framework memakai volume terpisah agar file tes/cache container tidak mengganggu host. Buat direktori views/cache/data/sessions sebelum menjalankan CLI pertama. Artisan serve menggunakan --no-reload agar environment Compose diteruskan ke HTTP.
- PHPUnit SQL Server: 52 test / 261 assertion lulus; SQLite: 52 test / 261 assertion lulus. Pint dan diff check lulus. Homepage dan /login HTTP 200 pada http://127.0.0.1:8001, memakai session database SQL Server.
- Belum dibuktikan: aktivasi request bersamaan, rollback semua migration, keseluruhan constraint JSON/consent/tipe waktu PRD, Azure SQL target, dan media R2. Suite lokal lulus bukan bukti seluruh kontrak production selesai.

## Pengujian lanjutan SQL Server dan GD
- Aktivasi dua periode melalui dua proses PHP independen dengan barrier bersamaan lulus (1 test / 9 assertion): kedua transaksi berhasil, satu periode aktif, jumlah periode dan pengurus arsip tetap. Fixture hanya memakai database test.
- migrate:reset menemukan CHECK role menghalangi drop kolom di migration lama. Setelah constraint dilepas secara eksplisit, reset selesai dan semua migration berhasil dipasang ulang pada database test.
- Pengujian gambar PNG asli dengan payload tambahan menemukan build GD tanpa imagewebp. Dockerfile diperbarui dengan libwebp-dev dan --with-webp; build ulang dan verifikasi re-encoding masih berjalan.
- Database test kini dipaksa juga melalui server PHPUnit dan override Compose; TestCase menolak nama database SQL Server selain suara_pergerakan_test sebelum suite berjalan.
- SQLite host setelah perubahan: 54 test / 261 assertion, 2 skip (GD host tidak tersedia dan concurrency hanya SQL Server). Pint dan diff check lulus.
- Sambil build GD: 6 tes langsung SQL Server lulus untuk admin_biro tanpa biro, role tidak dikenal, BPH dengan biro, anggota_biro tanpa biro, penempatan Pembina duplikat, dan penghapusan profil Pembina yang masih ditempatkan. Pengujian menulis langsung ke database sehingga membuktikan constraint, bukan hanya validasi form.

## Empat mode penulis karya
- Migration menambah penulis_tipe dan penulis_nama; legacy dengan anggota_id menjadi anggota, lainnya redaksi. SQL Server memiliki CHECK kombinasi mode/anggota/nama.
- Form CMS menyediakan anggota, nama bebas, anonim, redaksi. Validasi menolak kombinasi tidak sesuai; payload mengosongkan kolom yang tidak relevan ketika berganti mode. Excerpt dibatasi 200 karakter sesuai PRD.
- Tampilan publik memakai nama anggota, nama bebas, Anonim, atau Redaksi + settings.nama_rayon; tidak menambah tautan profil anggota.
- SQLite: 63 test / 284 assertion, 9 skip terkait SQL Server/GD. Tes terarah SQL Server author/check/concurrency: 9 test / 38 assertion lulus. Migration demo, Pint, build frontend dan diff check berhasil. Suite penuh GD menunggu image WebP selesai.

## Karya Pilihan
- Penanda is_featured pada CMS hanya admin/super_admin. Karya draft tidak boleh dipilih; pilihan published dibatasi enam dalam transaksi dengan transaction-owned sp_getapplock SQL Server untuk menyerialkan perubahan CMS bersamaan.
- Beranda mendahulukan maksimal enam pilihan berdasarkan published_at/id. Jika kurang dari empat, ditambah karya published terbaru sampai empat tanpa duplikasi; draft/soft-deleted tidak ikut.
- Tes fallback, batas enam, featured draft, dan penolakan admin_biro lulus pada SQLite dan SQL Server (masing-masing 3 test / 13 assertion). Migration demo, Pint, build frontend dan diff check berhasil.
- Tambahan tes memastikan pilihan yang sudah ada dapat diedit pada batas enam dan dilepas saat menjadi draft. Suite SQL Server gabungan tanpa dua tes gambar: 65 test / 317 assertion lulus. SQLite: 67 test / 302 assertion, 9 skip khusus SQL Server/GD.
- Suite gabungan mengungkap race aktivasi: model periode yang dibaca sebelum transaksi dapat memiliki nilai is_aktif kedaluwarsa, sehingga Eloquent melewati update aktivasi. Store/update periode sekarang mengambil transaction-owned sp_getapplock SQL Server; update me-refresh model di dalam lock. Tes dua proses juga membersihkan koneksi dan mereset RefreshDatabaseState agar tidak mencemari tes berikutnya. Suite gabungan lulus setelah perbaikan.

## Rich-text editor CMS

## CMS Tentang dan Kontak
- Tampilan/SEO: form multipart menyediakan logo/favicon/hero/OG (5 MB, JPG/PNG/WebP), preview tersimpan termasuk OG, dan navigasi anchor empat bagian settings. Gambar diproses ImageService; URL public disk dipakai pada navbar, hero, favicon, dan default OG. Penggantian gambar mengantrekan file lama dalam transaksi untuk cleanup durable; upload baru yang gagal dipersist juga masuk cleanup. SQL Server memakai mutex transaksi site-settings. Tab interaktif dan preview sebelum simpan belum tersedia.
- Tes settings terbaru 4 test / 30 assertion lulus pada SQLite dan SQL Server, termasuk penggantian hero, cleanup file lama, render publik, dan mempertahankan gambar saat tidak mengupload. Pemrosesan gambar dimock; re-encode GD asli tetap menunggu build. npm ci/build, Pint dan diff check berhasil.
- Settings mencakup tagline, deskripsi/sejarah organisasi, visi teks polos, misi rich text, URL Google Maps tanpa iframe, dan lima sosmed dengan kunci sosmed_* PRD. Kunci sosial legacy tetap terbaca sampai nilai canonical disimpan (termasuk saat dikosongkan).
- Rich text disanitasi pada model Setting saat save dan pada render Tentang. URL sosial hanya http/https; peta hanya HTTPS Google Maps yang diizinkan. Kontak/footer menampilkan tautan sosial yang tersedia; tagline digunakan navbar/footer. Perubahan tetap diaudit dan admin_biro ditolak oleh route server.
- Tes penyimpanan/render/sanitasi/audit, URL berbahaya, otorisasi admin_biro, dan kompatibilitas kunci lama: 3 test / 20 assertion lulus pada SQLite dan SQL Server. Suite SQLite 73 test / 342 assertion, 9 skip. npm ci/build, Pint, diff check berhasil. Tab UI dan upload Tampilan/SEO masih perlu dilengkapi.
- TipTap dimuat secara lazy hanya pada form karya/kegiatan. Toolbar: paragraf, h2–h4, bold/italic/underline/strike, daftar, kutipan, tautan http/https/mailto, pemisah, undo/redo. Image extension mempertahankan gambar HTML yang sudah ada; pemeriksaan domain gambar tetap pada HTMLPurifier server.
- Textarea tetap menjadi fallback jika JavaScript belum tersedia; saat editor siap, submit menyinkronkan HTML ke field asli. Baris baru legacy plain text dikonversi dengan text nodes dan br, bukan interpolasi HTML. Deskripsi kegiatan tetap opsional, konten karya wajib.
- npm ci dan build berhasil. Smoke runtime jsdom di luar repo memverifikasi inisialisasi dua editor, baris baru, deskripsi kosong opsional, sinkronisasi submit, dan toolbar berlabel. ContentSafetyTest: 5 test / 36 assertion lulus. Pemeriksaan visual browser belum tersedia karena browser desktop tidak terhubung. npm audit masih melaporkan 16 vulnerability baseline.

## Verifikasi final image GD/WebP

## Password akun
- Uji Playwright nyata desktop 1440/mobile 390: delapan halaman publik HTTP 200 tanpa overflow/gambar rusak; login CMS berhasil. Delapan halaman CMS diuji; overflow mobile dan tombol sidebar tersembunyi diperbaiki pada layout bersama, tabel scroll di area sendiri, input dibatasi viewport. Settings tabs/click/keyboard, preview gambar pilihan, filter anggota, editor TipTap, preview galeri/caption terverifikasi. Sidebar bisa ditutup Escape/klik luar dengan aria-expanded sinkron; pesan validasi global ditampilkan. Build lulus, console halaman settings setelah reload tanpa error. Ini smoke browser, belum acceptance seluruh operasi tulis/semua role. SMTP nyata dan delapan temuan dependency Tailwind masih pending.
- Penyelesaian CMS setelah audit: CHECK JSON SQL Server untuk karya.tags/riwayat.perubahan; pencatatan cleanup thumbnail/galeri atomic dengan transaksi; filter anggota/karya/riwayat dengan pagination/query string dan waktu riwayat WIB; bulk karya khusus admin/super_admin (timestamp dan media dipertahankan, batas featured tetap diperiksa); edit caption lama scoped per kegiatan; settings tab keyboard-accessible dan preview pilihan gambar; panduan awal dashboard serta penolakan pembuatan konten tanpa periode. SQL Server 90 test / 470 assertion lulus; npm ci/build lulus. Dependency diperbarui kompatibel ke Vite 6.4.4/plugin 1.3 dan versi minor/patch lainnya: audit turun dari 16 menjadi 8 (3 moderate, 5 high), masih pada rantai Tailwind 3 termasuk braces tanpa patch tersedia. Uji visual browser dan SMTP nyata masih pending. Redesign tetap sesudah deployment pada branch terpisah.
- Verifikasi R2 lanjutan: controller foto anggota/Pembina menggunakan objek privat nyata lulus 1 test / 33 assertion; izin, pencabutan, admin_biro, streaming/cache diperiksa. Objek kedua bucket bertahan setelah force-recreate app; semua objek tes dibersihkan dan homepage HTTP 200. Cleanup rollback foto privat memakai antrean durable dengan tes delete gagal/retry. Suite SQL Server 83 test / 436 assertion lulus. Rencana disepakati: deployment Azure sebelum redesign; redesign dikerjakan pada branch terpisah lalu merge setelah final.
- Integrasi media publik configurable selesai: PUBLIC_MEDIA_DISK memilih local/public atau r2_public untuk upload, URL, penggantian, dan cleanup, termasuk logo biro. Command media:copy-public-to-r2 memverifikasi hash dan mempertahankan sumber, menolak konflik serta folder foto privat legacy. Tes SQL Server keseluruhan: 82 test / 429 assertion lulus; build frontend lulus. Tes R2 nyata upload/read/delete kedua bucket dan konversi WebP/custom domain HTTP 200 berhasil. Detail dan batas verifikasi: docs/R2_STORAGE.md.
- Bootstrap production ditambahkan melalui app:buat-super-admin, input interaktif tersembunyi atau INITIAL_ADMIN_* untuk job noninteraktif. Menolak super_admin existing termasuk nonaktif; SQL Server menyerialkan bootstrap dengan transaction-owned lock. Akun pertama selalu wajib ganti password. Production seeder hanya membuat kunci settings kosong tanpa akun/data dummy dan tanpa menimpa nilai existing. Panduan: docs/INITIAL_ADMIN.md.
- Tes bootstrap, secrets kosong, dan seeder production: masing-masing SQLite/SQL Server 3 test / 14 assertion lulus.
- Akun baru dan password yang diatur ulang super_admin ditandai must_change_password. Middleware web memblokir request akun tersebut kecuali ganti password/logout, termasuk endpoint tulis. Halaman /admin/ganti-password meminta password saat ini, konfirmasi, minimal 8 karakter, dan password berbeda; sukses membuka CMS kembali.
- Forgot/reset menggunakan broker Laravel, notifikasi email, token satu kali, respons generik, penolakan akun nonaktif, serta throttle login/reset/ganti password. Reset email juga mewajibkan ganti password sesuai acceptance PRD. Reset/ganti password memutar remember token dan membersihkan sesi lama; audit tidak menyimpan rahasia dan input password/token tidak di-flash.
- Suite SQL Server: 76 test / 405 assertion lulus. SQLite: 76 test / 379 assertion, 9 skip. Setelah penambahan invalidasi sesi admin, tes terarah SQL Server: 5 test / 45 assertion lulus. Migration development, npm ci/build, Pint dan diff check berhasil.
- Email pengiriman nyata belum diuji: development memakai MAIL_MAILER=log; production perlu SMTP yang berfungsi. Command bootstrap super_admin pertama masih perlu dibuat.
- Build development berhasil dengan WebP enabled, PHP 8.4.26, sqlsrv/pdo_sqlsrv 5.13.3. Tes gambar asli PNG privat dan JPEG publik: 2 test / 11 assertion lulus (konversi WebP, resize, payload tambahan terhapus, file dapat dihapus).
- Suite SQL Server lengkap pada suara_pergerakan_test: 74 test / 378 assertion lulus tanpa skip. Container preview dibuat ulang memakai image baru; homepage dan login HTTP 200. Penyimpanan R2 dan deployment Azure tetap belum diverifikasi.

## Batas upload galeri
- Kegiatan: maksimal 20 foto galeri tersimpan, 5 MB per gambar, total 40 MB per penyimpanan termasuk thumbnail. Thumbnail karya juga 5 MB. Validasi MIME/image tetap memakai deteksi server.
- Edit menghitung foto yang sudah ada, mengurangi hanya ID penghapusan milik kegiatan itu, lalu menambahkan upload baru. Pemeriksaan diulang dalam transaksi setelah lockForUpdate pada kegiatan untuk menyerialkan perubahan galeri bersamaan. Penghapusan foto tetap hanya super_admin.
- Compose memasang uploads.ini (upload_max_filesize=5M, post_max_size=48M untuk overhead multipart, max_file_uploads=21). Container development telah dibuat ulang dan nilai runtime terverifikasi; homepage HTTP 200. Konfigurasi yang sama perlu diterapkan pada container production dan ingress deployment.
- Tes batas jumlah, >5 MB, total >40 MB termasuk thumbnail, payload PHP berkedok PNG, ID hapus dari kegiatan lain, dan penggantian foto 5 MB saat kapasitas penuh: 3 test / 20 assertion lulus di SQLite dan SQL Server. Pemrosesan gambar dimock pada tes batas; tes re-encode asli tetap terpisah menunggu build GD.
- SQL Server gabungan tanpa dua tes gambar: 68 test / 337 assertion lulus. SQLite: 70 test / 322 assertion, 9 skip. npm ci/build, Pint perubahan, dan diff check berhasil.
- Form galeri sekarang menampilkan preview dan input caption opsional per file (maksimal 255 karakter), dengan indeks caption sesuai indeks upload. Nama file dirender sebagai textContent; object URL preview dibersihkan saat pilihan berubah/halaman ditinggalkan. Tes penggantian foto juga memverifikasi caption tersimpan; 3 test / 20 assertion lulus dan build frontend berhasil.
- Investigasi build GD: build lama berjalan sekitar 27 menit tetapi masih mengunduh paket Debian via HTTP (index 9 MB memakan 9 menit, 16.7 kB/s), belum kompilasi. Build dihentikan dan dijalankan ulang dengan sumber Debian HTTPS, timeout 30 detik/retry 3, kompilasi ekstensi paralel, dan pin sqlsrv/pdo_sqlsrv 5.13.3. Hasil build baru belum tersedia.
