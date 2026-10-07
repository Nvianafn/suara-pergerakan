<?php

namespace App\Livewire;

use App\Models\Karya;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Karya')]
class KaryaIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true, keep: true)]
    public string $search = '';

    #[Url(as: 'tipe', history: true)]
    public string $tipe = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setTipe(string $tipe): void
    {
        $this->tipe = $this->tipe === $tipe ? '' : $tipe;
        $this->resetPage();
    }

    public function render()
    {
        $karya = Karya::with('anggota')
            ->published()
            ->when($this->tipe, fn ($q) => $q->where('tipe', $this->tipe))
            ->when($this->search, fn ($q) => $q->where('judul', 'like', '%'.$this->search.'%'))
            ->latest('published_at')
            ->paginate(8);

        return view('livewire.karya-index', [
            'karya' => $karya,
            'tipeList' => ['artikel' => 'Artikel', 'esai' => 'Esai', 'puisi' => 'Puisi', 'berita' => 'Berita'],
        ]);
    }
}
