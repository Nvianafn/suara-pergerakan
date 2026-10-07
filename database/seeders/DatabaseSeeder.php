<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\KegiatanFoto;
use App\Models\Kepengurusan;
use App\Models\Periode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            foreach (['nama_rayon', 'tagline', 'deskripsi_singkat', 'tentang_deskripsi', 'tentang_sejarah', 'visi', 'misi', 'email_kontak', 'no_wa', 'alamat', 'peta_url', 'sosmed_instagram', 'sosmed_youtube', 'sosmed_tiktok', 'sosmed_facebook', 'sosmed_x', 'logo', 'favicon', 'hero_image', 'og_image'] as $key) {
                Setting::firstOrCreate(['key' => $key], ['value' => '']);
            }

            return;
        }
        // ---- Super Admin ----
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@pmii-saintek.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // ---- Biro ----
        $biroData = [
            ['nama' => 'Biro Keilmuan', 'deskripsi' => 'Kajian ilmiah, riset, dan literasi untuk menajamkan nalar kader.', 'warna_aksen' => '#002068'],
            ['nama' => 'Biro Kaderisasi', 'deskripsi' => 'Merancang jenjang pembinaan dari MAPABA hingga pengaderan lanjut.', 'warna_aksen' => '#fcd400'],
            ['nama' => 'Biro Media & Jaringan', 'deskripsi' => 'Publikasi, dokumentasi, dan komunikasi digital pergerakan.', 'warna_aksen' => '#003399'],
            ['nama' => 'Biro Sosial Masyarakat', 'deskripsi' => 'Aksi pengabdian dan advokasi isu kemasyarakatan.', 'warna_aksen' => '#fcd400'],
            ['nama' => 'Biro Ekonomi Kreatif', 'deskripsi' => 'Kewirausahaan dan kemandirian ekonomi kader.', 'warna_aksen' => '#002068'],
            ['nama' => 'Biro Keagamaan', 'deskripsi' => 'Pembinaan spiritual dan penguatan nilai Aswaja.', 'warna_aksen' => '#fcd400'],
        ];
        $biro = [];
        foreach ($biroData as $i => $b) {
            $biro[$i] = Biro::create($b + ['tipe' => 'biro', 'urutan' => $i]);
        }
        $bphUnit = Biro::updateOrCreate(
            ['slug' => 'badan-pengurus-harian'],
            [
                'nama' => 'Badan Pengurus Harian',
                'tipe' => 'bph',
                'deskripsi' => 'Pimpinan rayon yang menggerakkan roda organisasi sehari-hari.',
                'warna_aksen' => '#002068',
                'urutan' => 0,
            ]
        );

        // ---- Periode ----
        Periode::create([
            'nama' => '2024/2025', 'tahun_mulai' => 2024, 'tahun_selesai' => 2025,
            'is_aktif' => false, 'tema' => 'Kokohkan Barisan, Rawat Pergerakan',
        ]);
        $periode = Periode::create([
            'nama' => '2025/2026', 'tahun_mulai' => 2025, 'tahun_selesai' => 2026,
            'is_aktif' => true, 'tema' => 'Menguatkan Nalar, Merawat Pergerakan',
            'deskripsi' => 'Periode kepengurusan aktif PMII Rayon Saintek.',
        ]);

        // ---- Anggota ----
        $a = [];
        $anggotaData = [
            ['Ahmad Fauzan', 'Teknik Informatika', 2022], ['Siti Aisyah', 'Matematika', 2022],
            ['M. Rizki', 'Fisika', 2023], ['Nur Hidayah', 'Kimia', 2023],
            ['Fikri Aziz', 'Teknik Informatika', 2023], ['Dina Rahma', 'Biologi', 2023],
            ['Hasan Basri', 'Teknik Elektro', 2023], ['Laila Nur', 'Matematika', 2024],
            ['Yusuf Maulana', 'Fisika', 2024], ['Rara Anjani', 'Kimia', 2024],
            ['Bayu Saputra', 'Teknik Informatika', 2024], ['Intan Permata', 'Biologi', 2024],
        ];
        foreach ($anggotaData as $i => [$nama, $prodi, $ang]) {
            $a[$i] = Anggota::create([
                'nim' => '2'.str_pad((string) ($i + 1), 8, '0', STR_PAD_LEFT),
                'nama_lengkap' => $nama,
                'nama_panggilan' => explode(' ', $nama)[0],
                'angkatan' => $ang,
                'fakultas' => 'Sains dan Teknologi',
                'prodi' => $prodi,
                'email' => strtolower(str_replace([' ', '.'], '', explode(' ', $nama)[0])).($i + 1).'@student.pmii.id',
                'bio' => 'Kader PMII Rayon Saintek yang aktif dalam kajian dan pengabdian.',
                'status' => 'aktif',
            ]);
        }

        // ---- Kepengurusan (periode aktif) ----
        $bph = [
            [0, 'Ketua Rayon', 0], [1, 'Wakil Ketua', 1],
            [2, 'Sekretaris', 2], [3, 'Bendahara', 3],
        ];
        foreach ($bph as [$idx, $jab, $ord]) {
            Kepengurusan::create([
                'anggota_id' => $a[$idx]->id, 'periode_id' => $periode->id,
                'biro_id' => $bphUnit->id, 'jabatan' => $jab, 'is_ketua' => true, 'urutan' => $ord,
            ]);
        }
        // Ketua + anggota Biro Keilmuan sebagai contoh lengkap
        Kepengurusan::create([
            'anggota_id' => $a[4]->id, 'periode_id' => $periode->id,
            'biro_id' => $biro[0]->id, 'jabatan' => 'Ketua Biro Keilmuan', 'is_ketua' => true, 'urutan' => 0,
        ]);
        foreach ([5, 6, 7, 8] as $ord => $idx) {
            Kepengurusan::create([
                'anggota_id' => $a[$idx]->id, 'periode_id' => $periode->id,
                'biro_id' => $biro[0]->id, 'jabatan' => 'Anggota Biro Keilmuan',
                'is_ketua' => false, 'urutan' => $ord,
            ]);
        }
        // Ketua biro lain
        foreach ([1 => 9, 2 => 10, 3 => 11] as $biroIdx => $angIdx) {
            Kepengurusan::create([
                'anggota_id' => $a[$angIdx]->id, 'periode_id' => $periode->id,
                'biro_id' => $biro[$biroIdx]->id,
                'jabatan' => 'Ketua '.$biro[$biroIdx]->nama, 'is_ketua' => true, 'urutan' => 0,
            ]);
        }

        // ---- Kegiatan ----
        $kegiatanData = [
            ['MAPABA XVII: Gerbang Kaderisasi Awal', 1, '2026-06-12', 'Aula FST', 'Masa penerimaan anggota baru yang diikuti puluhan mahasiswa Saintek.'],
            ['Diskusi Publik: Etika Teknologi & AI', 0, '2026-05-28', 'Taman Kampus', 'Forum terbuka membahas tanggung jawab moral di era kecerdasan buatan.'],
            ['Bakti Sosial & Literasi Digital Desa', 3, '2026-05-03', 'Desa Binaan', 'Pengabdian masyarakat sekaligus pelatihan literasi digital warga desa.'],
            ['Ngaji Kebangsaan & Halaqah Aswaja', 5, '2026-04-19', 'Masjid Kampus', 'Kajian rutin memperkuat wawasan keislaman dan kebangsaan kader.'],
            ['Sekolah Riset: Metodologi Penelitian', 0, '2026-04-02', 'Lab Komputer', 'Pelatihan dasar metodologi riset bagi kader lintas angkatan.'],
            ['Workshop Konten & Desain Grafis', 2, '2026-03-15', 'Sekretariat', 'Membekali kader keterampilan produksi konten digital pergerakan.'],
        ];
        foreach ($kegiatanData as [$judul, $biroIdx, $tgl, $lokasi, $desk]) {
            $keg = Kegiatan::create([
                'biro_id' => $biro[$biroIdx]->id, 'judul' => $judul,
                'deskripsi' => $desk.' Kegiatan ini menjadi bagian dari ikhtiar pergerakan untuk terus tumbuh bersama.',
                'tanggal' => $tgl, 'lokasi' => $lokasi, 'status' => 'published',
                'created_by' => $admin->id,
            ]);
            for ($f = 1; $f <= 4; $f++) {
                KegiatanFoto::create([
                    'kegiatan_id' => $keg->id, 'path' => 'seed/kegiatan-sample.jpg',
                    'caption' => 'Dokumentasi '.$judul.' #'.$f, 'urutan' => $f,
                ]);
            }
        }

        // ---- Karya ----
        $karyaData = [
            ['Merawat Nalar Kritis di Tengah Arus Informasi', 'artikel', 0, '2026-06-12', ['literasi', 'pergerakan', 'nalar'], 'Bagaimana kader menjaga daya pikir kritis ketika dibanjiri informasi digital yang tak terbendung setiap hari.'],
            ['Sains, Iman, dan Tanggung Jawab Sosial', 'esai', 2, '2026-06-05', ['sains', 'aswaja'], 'Refleksi tentang bagaimana ilmu pengetahuan dan nilai keislaman berjalan beriringan.'],
            ['Aswaja sebagai Cara Pandang, Bukan Sekadar Slogan', 'artikel', 6, '2026-06-01', ['aswaja', 'moderasi'], 'Menelaah nilai moderasi Ahlussunnah wal Jama\'ah dalam praktik keseharian mahasiswa.'],
            ['Sajak untuk Sang Penggerak', 'puisi', 1, '2026-06-09', ['puisi', 'refleksi'], 'Di antara barisan yang lelah, ada nyala yang tak pernah padam — sebuah sajak tentang keteguhan.'],
            ['MAPABA XVII Sukses Digelar, Diikuti 60 Peserta', 'berita', null, '2026-06-14', ['kaderisasi', 'berita'], 'Rangkaian penerimaan anggota baru berlangsung meriah selama tiga hari.'],
            ['Rindu yang Bergerak', 'puisi', 7, '2026-05-28', ['puisi'], 'Sebuah puisi pendek tentang pergerakan, harapan, dan cinta pada tanah air.'],
            ['Teknologi untuk Kemaslahatan Umat', 'esai', 5, '2026-05-22', ['teknologi', 'sosial'], 'Menimbang arah pengembangan teknologi yang berpihak pada masyarakat luas.'],
        ];
        foreach ($karyaData as [$judul, $tipe, $angIdx, $tgl, $tags, $excerpt]) {
            Karya::create([
                'anggota_id' => $angIdx === null ? null : $a[$angIdx]->id,
                'judul' => $judul, 'tipe' => $tipe,
                'konten' => '<p>'.$excerpt.'</p><p>Tulisan ini merupakan bagian dari ruang literasi kader PMII Rayon Saintek, sebagai ikhtiar merawat tradisi berpikir dan menulis.</p>',
                'excerpt' => $excerpt, 'tags' => $tags,
                'status' => 'published', 'published_at' => $tgl,
                'created_by' => $admin->id,
            ]);
        }

        // ---- Settings ----
        $settings = [
            'nama_rayon' => 'PMII Rayon Saintek',
            'deskripsi_singkat' => 'Pergerakan Mahasiswa Islam Indonesia, Rayon Sains & Teknologi. Dzikir, Fikir, Amal Sholeh.',
            'email_kontak' => 'rayonsaintek@pmii.id',
            'no_wa' => '+62 812-0000-0000',
            'alamat' => 'Sekretariat FST, Purwokerto',
            'instagram' => 'https://instagram.com/pmii.saintek',
            'facebook' => '#',
            'youtube' => '#',
        ];
        foreach ($settings as $k => $v) {
            Setting::create(['key' => $k, 'value' => $v]);
        }
    }
}
