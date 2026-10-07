<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $biro = auth()->user()->role === 'admin_biro' ? auth()->user()->biro_id : null;
        $kegiatan = Kegiatan::query()->when($biro, fn ($q) => $q->where('biro_id', $biro));
        $karya = Karya::query()->when($biro, fn ($q) => $q->where('biro_id', $biro));

        return view('admin.dashboard', [
            'stats' => [
                'anggota' => $biro ? Anggota::whereHas('kepengurusan', fn ($q) => $q->where('biro_id', $biro))->count() : Anggota::count(),
                'biro' => $biro ? 1 : Biro::count(),
                'kegiatan' => (clone $kegiatan)->count(),
                'karya' => (clone $karya)->count(),
            ],
            'kegiatanTerbaru' => (clone $kegiatan)->with('biro')->latest('tanggal')->take(5)->get(),
            'karyaTerbaru' => (clone $karya)->with('anggota')->when($biro, fn ($q) => $q->where('created_by', auth()->id())->where('status', 'draft'))->latest('created_at')->take(5)->get(),
            'draftKarya' => (clone $karya)->where('status', 'draft')->count(),
        ]);
    }
}
