<?php

namespace App\Livewire;

use App\Models\Biro;
use App\Models\Kepengurusan as KepengurusanModel;
use App\Models\Periode;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Kepengurusan')]
class KepengurusanPage extends Component
{
    #[Url(as: 'periode', history: true)]
    public ?int $periodeId = null;

    public function mount(?string $periodeSlug = null): void
    {
        if ($periodeSlug !== null) {
            $this->periodeId = Periode::where('slug', $periodeSlug)->firstOrFail()->id;
        }
        $this->periodeId ??= Periode::where('is_aktif', true)->value('id') ?? Periode::max('id');
    }

    public function render()
    {
        $pengurus = KepengurusanModel::with(['anggota', 'biro'])
            ->where('periode_id', $this->periodeId)
            ->orderBy('urutan')
            ->get();

        return view('livewire.kepengurusan-page', [
            'periodeList' => Periode::orderByDesc('tahun_mulai')->get(),
            'periode' => Periode::find($this->periodeId),
            'pengurus' => $pengurus,
            'pembina' => Periode::find($this->periodeId)?->pembina()->orderBy('periode_pembina.urutan')->orderBy('periode_pembina.id')->get() ?? collect(),
            'bph' => $pengurus->where('level', 'bph'),
            'perBiro' => $pengurus->whereIn('level', ['ketua_biro', 'anggota_biro'])->groupBy('biro_id'),
            'biroList' => Biro::unitBiro()->orderBy('urutan')->get(),
        ]);
    }
}
