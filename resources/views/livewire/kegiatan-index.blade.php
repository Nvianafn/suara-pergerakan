<div>
@push('styles')
<style>
.filter-chips{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:2.5rem}
.keg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
.keg-card{border-radius:1rem;overflow:hidden;background:var(--sc-lowest);border:1px solid var(--outline-variant);transition:transform .3s ease,box-shadow .3s ease;display:block}
.keg-card:hover{transform:translateY(-6px);box-shadow:0 26px 60px -20px rgba(0,32,104,.3)}
.keg-thumb{height:190px;position:relative;overflow:hidden}
.keg-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
.keg-card:hover .keg-thumb img{transform:scale(1.06)}
.keg-thumb .tag{position:absolute;top:.8rem;left:.8rem;background:rgba(255,255,255,.85);backdrop-filter:blur(8px);color:var(--primary);font-size:11px;font-weight:600;letter-spacing:.05em;padding:.3rem .7rem;border-radius:9999px}
.keg-body{padding:1.4rem}
.keg-body .meta{font-size:12px;color:var(--on-surface-variant);letter-spacing:.04em;margin-bottom:.5rem}
.keg-body h3{font-size:1.14rem;line-height:1.35;margin-bottom:.5rem}
.keg-body p{font-size:.9rem;color:var(--on-surface-variant)}
.empty-state{text-align:center;padding:4rem 1rem;color:var(--on-surface-variant)}
.pager{margin-top:3rem}
@media(max-width:960px){.keg-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.keg-grid{grid-template-columns:1fr}}
</style>
@endpush

<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><span>Kegiatan</span></div>
    <span class="eyebrow">Dokumentasi</span>
    <h1>Kegiatan &amp; Program</h1>
    <p>Jejak langkah pergerakan: dari kaderisasi, kajian keilmuan, hingga pengabdian masyarakat.</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="filter-chips">
      <label for="periode-filter">Periode</label>
      <select id="periode-filter" wire:model.live="periode"><option value="">Semua periode</option>@foreach($periodeList as $p)<option value="{{ $p->slug }}">{{ $p->nama }}</option>@endforeach</select>
      <button type="button" wire:click="$set('biro', '')" @class(['chip', 'active' => $biro === ''])>Semua</button>
      @foreach ($biroList as $b)
        <button type="button" wire:click="setBiro('{{ $b->slug }}')" @class(['chip', 'active' => $biro === $b->slug])>{{ Str::of($b->nama)->replace('Biro ', '') }}</button>
      @endforeach
    </div>

    <div wire:loading.class="opacity-50">
      @if ($kegiatan->count())
        <div class="keg-grid">
          @foreach ($kegiatan as $k)
          <a href="{{ route('kegiatan.show', $k) }}" class="keg-card">
            <div class="keg-thumb">
              <img src="{{ $k->thumbnail ? \App\Services\PublicMedia::url($k->thumbnail) : asset('images/hero.png') }}" alt="{{ $k->judul }}">
              @if ($k->biro_label)<span class="tag">{{ Str::of($k->biro_label)->replace('Biro ', '') }}</span>@endif
            </div>
            <div class="keg-body">
              <div class="meta">{{ $k->tanggal->translatedFormat('d F Y') }} @if($k->lokasi)&middot; {{ $k->lokasi }} @endif</div>
              <h3>{{ $k->judul }}</h3>
              <p>{{ Str::limit($k->deskripsi, 90) }}</p>
            </div>
          </a>
          @endforeach
        </div>
        <div class="pager">{{ $kegiatan->links() }}</div>
      @else
        <div class="empty-state">
          <p><x-icon name="calendar" :size="40"/></p>
          <h3>Belum ada kegiatan</h3>
          <p>Belum ada kegiatan pada kategori ini.</p>
        </div>
      @endif
    </div>
  </div>
</section>
</div>
