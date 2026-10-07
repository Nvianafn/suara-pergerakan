<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\Kepengurusan;
use App\Models\Periode;
use App\Services\FeaturedWorks;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $periodeAktif = Periode::aktif()->first();

        $bph = Kepengurusan::with('anggota')
            ->where('periode_id', $periodeAktif?->id)
            ->bph()->orderBy('urutan')->take(2)->get();

        return view('home', [
            'biro' => Biro::unitBiro()->where('is_aktif', true)->orderBy('urutan')->get(),
            'kegiatanTerbaru' => Kegiatan::with('biro')->published()->latest('published_at')->take(3)->get(),
            'karyaPilihan' => app(FeaturedWorks::class)->forHome(),
            'bph' => $bph,
            'periodeAktif' => $periodeAktif,
            'stats' => [
                'anggota' => Anggota::where('status', 'aktif')->count(),
                'biro' => Biro::count(),
                'kegiatan' => Kegiatan::published()->count(),
                'karya' => Karya::published()->count(),
            ],
        ]);
    }

    public function tentang(): View
    {
        return view('tentang', [
            'periodeAktif' => Periode::aktif()->first(),
            'stats' => [
                'anggota' => Anggota::where('status', 'aktif')->count(),
                'biro' => Biro::count(),
                'kegiatan' => Kegiatan::published()->count(),
                'karya' => Karya::published()->count(),
            ],
        ]);
    }

    public function biroIndex(): View
    {
        return view('biro.index', [
            'biro' => Biro::unitBiro()->where('is_aktif', true)->withCount(['kepengurusan', 'kegiatan'])->orderBy('urutan')->get(),
        ]);
    }

    public function biroShow(Biro $biro): View
    {
        $periodeAktif = Periode::aktif()->first();

        $biro->load(['kepengurusan' => function ($q) use ($periodeAktif) {
            $q->where('periode_id', $periodeAktif?->id)
                ->with('anggota')->orderBy('urutan');
        }]);
        if (! $biro->is_aktif) {
            $biro->setRelation('kepengurusan', collect());
        }

        return view('biro.show', [
            'biro' => $biro,
            'ketua' => $biro->kepengurusan->firstWhere('is_ketua', true),
            'kegiatan' => $biro->kegiatan()->published()->latest('published_at')->take(4)->get(),
        ]);
    }

    public function kegiatanShow(Kegiatan $kegiatan): View
    {
        abort_if($kegiatan->status !== 'published', 404);
        $kegiatan->load(['biro', 'foto']);

        return view('kegiatan.show', [
            'kegiatan' => $kegiatan,
            'lainnya' => Kegiatan::published()->where('id', '!=', $kegiatan->id)
                ->latest('published_at')->take(3)->get(),
        ]);
    }

    public function karyaShow(Karya $karya): View
    {
        abort_if($karya->status !== 'published', 404);
        $karya->load('anggota');

        return view('karya.show', [
            'karya' => $karya,
            'terkait' => Karya::published()->where('id', '!=', $karya->id)
                ->where('tipe', $karya->tipe)->latest('published_at')->take(3)->get(),
        ]);
    }

    public function kontak(): View
    {
        return view('kontak');
    }

    public function kontakSubmit(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'pesan' => 'required|string|max:2000',
        ]);

        // TODO: kirim email / simpan ke tabel pesan (Fase lanjutan)
        return back()->with('success', 'Terima kasih! Pesan kamu sudah kami terima.');
    }
}
