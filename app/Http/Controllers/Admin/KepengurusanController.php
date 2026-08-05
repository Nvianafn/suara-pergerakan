<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepengurusanRequest;
use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Kepengurusan;
use App\Models\Periode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KepengurusanController extends Controller
{
    public function index(Request $request): View
    {
        $periode = Periode::orderByDesc('tahun_mulai')->get();
        $selectedId = $request->integer('periode') ?: optional($periode->firstWhere('is_aktif', true))->id ?: optional($periode->first())->id;

        $pengurus = Kepengurusan::with(['anggota', 'biro'])
            ->where('periode_id', $selectedId)
            ->orderBy('urutan')
            ->get();

        $dataIssues = $pengurus->filter(fn (Kepengurusan $k) => ! $k->biro_id)->count();

        return view('admin.kepengurusan.index', [
            'periodeList' => $periode,
            'selectedId' => $selectedId,
            'pengurus' => $pengurus,
            'pengurusByUnit' => $pengurus->groupBy('biro_id'),
            'units' => Biro::orderBy('urutan')->get(),
            'dataIssues' => $dataIssues,
        ]);
    }

    public function create(): View
    {
        return view('admin.kepengurusan.create', $this->formData());
    }

    public function store(KepengurusanRequest $request): RedirectResponse
    {
        Kepengurusan::create($this->payload($request));

        return redirect()->route('admin.kepengurusan.index', ['periode' => $request->periode_id])
            ->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function edit(Kepengurusan $kepengurusan): View
    {
        return view('admin.kepengurusan.edit', array_merge($this->formData(), [
            'kepengurusan' => $kepengurusan,
        ]));
    }

    public function update(KepengurusanRequest $request, Kepengurusan $kepengurusan): RedirectResponse
    {
        $kepengurusan->update($this->payload($request));

        return redirect()->route('admin.kepengurusan.index', ['periode' => $request->periode_id])
            ->with('success', 'Data pengurus berhasil diperbarui.');
    }

    public function destroy(Kepengurusan $kepengurusan): RedirectResponse
    {
        $periodeId = $kepengurusan->periode_id;
        $kepengurusan->delete();

        return redirect()->route('admin.kepengurusan.index', ['periode' => $periodeId])
            ->with('success', 'Pengurus berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'periodeList' => Periode::orderByDesc('tahun_mulai')->get(),
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
            'unitList' => Biro::orderBy('urutan')->get(),
        ];
    }

    private function payload(KepengurusanRequest $request): array
    {
        $data = collect($request->validated())->except('unique_check')->toArray();

        $data['biro_id'] = $data['biro_id'];
        $data['is_ketua'] = $request->boolean('is_ketua');
        $data['urutan'] = $data['urutan'] ?? 0;

        return $data;
    }
}
