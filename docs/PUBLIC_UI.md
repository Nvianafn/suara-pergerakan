# Polishing UI publik — 8 Oktober 2026

`resources/css/public.css` adalah entry Vite khusus layout publik, dimuat setelah
gaya halaman. Hierarki heading, spacing section, metadata minimal 13 px pada
komponen utama, kontras footer, focus keyboard, dan target tombol diperbaiki.
Artikel memakai Noto Serif 18 px desktop/17 px mobile, line-height 1.8, maksimal
65ch. Efek kartu lebih tenang dan body gradient disederhanakan.

Verifikasi: npm ci/build lulus. Browser Chromium memeriksa beranda, tentang,
kontak, karya, kegiatan, kepengurusan, biro pada 1440/390/320 px: HTTP 200 tanpa
overflow dokumen; tidak ada pageerror. Artikel nyata diperiksa: desktop 18 px,
lebar 654 px; mobile 17 px. Screenshot homepage desktop/mobile dan artikel
ditinjau. Belum merupakan audit WCAG menyeluruh.
