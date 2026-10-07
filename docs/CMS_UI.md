# Perapihan CMS

Gaya bersama CMS berada di `resources/css/admin.css`, dimuat setelah gaya
halaman agar input/filter/tabel/form konsisten. Entry ini harus ikut build Vite.

Perbaikan: input tanpa class tetap bergaya, checkbox tidak melebar, filter dalam
panel, bulk action berjarak, tabs settings memiliki status aktif, form memakai
kolom responsif, card tidak bergerak saat hover, sidebar aktif pada tambah/edit,
dan atribut hidden dihormati agar textarea sumber TipTap tidak tampil ganda.

Verifikasi 7 Oktober 2026:
- npm ci dan npm run build lulus; audit npm masih 8 temuan sebelumnya.
- 12 halaman CMS diperiksa pada 1440 dan 390 px, HTTP 200 tanpa overflow
  dokumen (dashboard, karya, tambah karya, tambah kegiatan, settings, anggota,
  pengguna, periode, kepengurusan, biro, riwayat, sampah).
- Screenshot daftar karya, form karya, settings desktop/mobile ditinjau.
- Sinkronisasi TipTap ke textarea, hidden textarea, pergantian tab settings,
  tutup sidebar dengan Escape lulus. Tidak ada pageerror pada uji interaksi.
- Ini pemeriksaan UI; bukan acceptance semua operasi tulis dan semua role.
