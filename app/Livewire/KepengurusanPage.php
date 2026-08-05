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

    public function mount(): void
    {
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
            'bph' => $pengurus->filter(fn ($p) => $p->biro?->tipe === 'bph'),
            'perBiro' => $pengurus->filter(fn ($p) => $p->biro?->tipe === 'biro')->groupBy('biro_id'),
            'biroList' => Biro::unitBiro()->orderBy('urutan')->get(),
        ]);
    }
}
