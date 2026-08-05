<div>
@push('styles')
<style>
.periode-bar{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:2.5rem}
.periode-bar label{font-size:13px;font-weight:600;color:var(--primary)}
.periode-select{padding:.6rem 2.2rem .6rem 1rem;border-radius:9999px;border:1px solid var(--outline-variant);background:var(--sc-lowest);font-family:var(--font-sans);font-weight:600;font-size:14px;color:var(--primary);cursor:pointer}
.periode-tema{font-family:var(--font-serif);font-style:italic;color:var(--on-surface-variant)}
.bph-panel{position:relative;border-radius:1.5rem;overflow:hidden;padding:3rem;background:linear-gradient(120deg,#002068,#003399);margin-bottom:3rem}
.bph-panel::after{content:"";position:absolute;right:-8%;top:-30%;width:44%;height:160%;background:radial-gradient(circle,rgba(252,212,0,.2),transparent 65%)}
.bph-panel .eyebrow{color:var(--gold);position:relative;z-index:2}
.bph-panel h2{color:#fff;position:relative;z-index:2;margin:.5rem 0 2rem}
.bph-grid{position:relative;z-index:2;display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem}
.bph-card{text-align:center}
.bph-card .ph{width:100%;aspect-ratio:1/1.15;border-radius:1rem;background:linear-gradient(160deg,#dce9ff,#8aa4ff);border:2px solid rgba(255,255,255,.3);display:grid;place-items:center;font-family:var(--font-display);font-size:2.2rem;color:var(--primary);margin-bottom:.8rem;overflow:hidden}
.bph-card .ph img{width:100%;height:100%;object-fit:cover}
.bph-card b{color:#fff;font-family:var(--font-sans);font-weight:600;font-size:.98rem;display:block}
.bph-card small{color:var(--on-primary-container);font-size:12.5px}
.acc-item{border:1px solid var(--outline-variant);border-radius:1rem;background:rgba(255,255,255,.72);backdrop-filter:blur(12px);margin-bottom:1rem;overflow:hidden}
.acc-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.3rem 1.6rem;cursor:pointer}
.acc-head .left{display:flex;align-items:center;gap:1rem}
.acc-head .dot{width:12px;height:12px;border-radius:50%;flex:none}
.acc-head h3{font-size:1.15rem}
.acc-head .count{font-size:12.5px;color:var(--on-surface-variant)}
.acc-head .arrow{transition:transform .3s ease;color:var(--primary)}
.acc-body{padding:0 1.6rem 1.5rem;display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.person{display:flex;align-items:center;gap:.8rem;padding:.8rem;border-radius:.8rem;background:var(--sc-low)}
.person .av{width:44px;height:44px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-display);font-weight:700;color:var(--primary);flex:none;overflow:hidden}
.person .av img{width:100%;height:100%;object-fit:cover}
.person b{font-size:.92rem;color:var(--on-surface);display:block;line-height:1.2}
.person small{font-size:12px;color:var(--on-surface-variant)}
@media(max-width:960px){.bph-grid{grid-template-columns:1fr 1fr}.acc-body{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.bph-grid,.acc-body{grid-template-columns:1fr}.bph-panel{padding:2rem}}
</style>
@endpush

<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><span>Kepengurusan</span></div>
    <span class="eyebrow">Struktur Organisasi</span>
    <h1>Barisan Kepengurusan</h1>
    <p>Susunan pengurus yang menggerakkan roda organisasi PMII Rayon Saintek dari periode ke periode.</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="periode-bar">
      <label for="periode">Periode</label>
      <select id="periode" class="periode-select" wire:model.live="periodeId">
        @foreach ($periodeList as $p)
          <option value="{{ $p->id }}">{{ $p->nama }} @if($p->is_aktif)(Aktif)@endif</option>
        @endforeach
      </select>
      @if ($periode?->tema)<span class="periode-tema">&ldquo;{{ $periode->tema }}&rdquo;</span>@endif
    </div>

    <div wire:loading.class="opacity-50">
      @if ($bph->count())
      <div class="bph-panel">
        
        <h2>Badan Pengurus Harian</h2>
        <div class="bph-grid">
          @foreach ($bph as $p)
          <div class="bph-card">
            <div class="ph">
              @if ($p->anggota->foto_url)
              <img src="{{ $p->anggota->foto_url }}" alt="{{ $p->anggota->nama_lengkap }}" loading="lazy">
              @else
              {{ $p->anggota->initial() }}
              @endif
            </div>
            <b>{{ $p->anggota->nama_lengkap }}</b>
            <small>{{ $p->jabatan }}</small>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      @forelse ($biroList as $b)
        @php $anggotaBiro = $perBiro[$b->id] ?? collect(); @endphp
        @if ($anggotaBiro->count())
        <div class="acc-item" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
          <div class="acc-head" x-on:click="open = !open">
            <div class="left">
              <span class="dot" style="background:{{ $b->warna_aksen ?? '#003399' }}"></span>
              <h3>{{ $b->nama }}</h3>
              <span class="count">&middot; {{ $anggotaBiro->count() }} pengurus</span>
            </div>
            <span class="arrow" :style="open ? 'transform:rotate(180deg)' : ''">&#9662;</span>
          </div>
          <div class="acc-body" x-show="open" x-collapse x-cloak>
            @foreach ($anggotaBiro->sortBy('urutan') as $p)
            <div class="person">
              <span class="av">
                @if ($p->anggota->foto_url)
                <img src="{{ $p->anggota->foto_url }}" alt="{{ $p->anggota->nama_lengkap }}" loading="lazy">
                @else
                {{ $p->anggota->initial() }}
                @endif
              </span>
              <span>
                <b>{{ $p->anggota->nama_lengkap }}</b>
                <small>{{ $p->jabatan }}</small>
              </span>
            </div>
            @endforeach
          </div>
        </div>
        @endif
      @empty
        <p>Belum ada data kepengurusan untuk periode ini.</p>
      @endforelse
    </div>
  </div>
</section>
</div>
