<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodeRequest;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Services\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeriodeController extends Controller
{
    public function index(): View
    {
        return view('admin.periode.index', [
            'periode' => Periode::withCount('kepengurusan')->orderByDesc('tahun_mulai')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.periode.create');
    }

    public function store(PeriodeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->boolean('is_aktif');

        $periode = DB::transaction(function () use ($data) {
            $this->lockActivation();
            if ($data['is_aktif']) {
                Periode::query()->update(['is_aktif' => false]);
            }

            $periode = Periode::create($data);
            ActivityLog::record('periode', 'pembuatan', $periode->id, 'Periode dibuat; status '.($periode->is_aktif ? 'aktif' : 'arsip').'.');

            return $periode;
        });

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode "'.$periode->nama.'" berhasil dibuat.');
    }

    public function edit(Periode $periode): View
    {
        return view('admin.periode.edit', ['periode' => $periode]);
    }

    public function update(PeriodeRequest $request, Periode $periode): RedirectResponse
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->user()->role === 'super_admin'
            ? $request->boolean('is_aktif') : $periode->is_aktif;

        DB::transaction(function () use ($data, $periode) {
            $this->lockActivation();
            $periode->refresh();
            if ($data['is_aktif']) {
                Periode::where('id', '!=', $periode->id)->update(['is_aktif' => false]);
            }

            $periode->update($data);
            ActivityLog::record('periode', 'perubahan', $periode->id, 'Periode diperbarui; status '.($periode->is_aktif ? 'aktif' : 'arsip').'.');
        });

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode "'.$periode->nama.'" berhasil diperbarui.');
    }

    public function destroy(Periode $periode): RedirectResponse
    {
        $counts = [
            'pembina' => $periode->pembina()->count(),
            'pengurus' => $periode->kepengurusan()->count(),
            'kegiatan' => Kegiatan::withTrashed()->where('periode_id', $periode->id)->count(),
            'karya' => Karya::withTrashed()->where('periode_id', $periode->id)->count(),
        ];
        if ($periode->is_aktif || array_sum($counts) > 0) {
            $details = collect($counts)->map(fn ($count, $name) => $count.' '.$name)->implode(', ');

            return back()->withErrors(['periode' => 'Periode ini tidak dapat dihapus'.($periode->is_aktif ? ' karena masih aktif' : '').': '.$details.'.']);
        }

        DB::transaction(function () use ($periode) {
            $periode->delete();
            ActivityLog::record('periode', 'penghapusan', $periode->id, 'Periode tanpa relasi dihapus.');
        });

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode telah dihapus.');
    }

    private function lockActivation(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement("DECLARE @result int; EXEC @result = sp_getapplock @Resource = 'periode-activation', @LockMode = 'Exclusive', @LockOwner = 'Transaction', @LockTimeout = 10000; IF @result < 0 THROW 50001, 'Could not lock period activation', 1;");
        }
    }
}
