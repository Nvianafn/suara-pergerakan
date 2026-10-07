<?php

namespace App\Livewire;

use App\Models\Biro;
use App\Models\Kegiatan;
use App\Models\Periode;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Kegiatan')]
class KegiatanIndex extends Component
{
    use WithPagination;

    #[Url(as: 'biro', history: true)]
    public string $biro = '';

    #[Url(as: 'periode', history: true)]
    public string $periode = '';

    public function updatedPeriode(): void
    {
        $this->resetPage();
    }

    public function setBiro(string $slug): void
    {
        $this->biro = $this->biro === $slug ? '' : $slug;
        $this->resetPage();
    }

    public function render()
    {
        $kegiatan = Kegiatan::with('biro')
            ->published()
            ->when($this->periode, fn ($q) => $q->whereHas('periode', fn ($p) => $p->where('slug', $this->periode)))
            ->when($this->biro, fn ($q) => $q->whereHas('biro', fn ($b) => $b->where('slug', $this->biro)))
            ->latest('published_at')
            ->paginate(9);

        return view('livewire.kegiatan-index', [
            'kegiatan' => $kegiatan,
            'biroList' => Biro::orderBy('urutan')->get(),
            'periodeList' => Periode::orderByDesc('tahun_mulai')->get(),
        ]);
    }
}
