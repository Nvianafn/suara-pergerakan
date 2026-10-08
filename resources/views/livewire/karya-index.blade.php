<div>
@push('styles')
<style>
.filter-bar{display:flex;gap:1rem;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:2.5rem}
.filter-chips{display:flex;gap:.5rem;flex-wrap:wrap}
.search-box{position:relative;min-width:260px;flex:0 1 320px}
.search-box input{width:100%;padding:.7rem 1rem .7rem 2.5rem;border-radius:9999px;border:1px solid var(--outline-variant);background:var(--sc-lowest);font-family:var(--font-sans);font-size:14.5px;color:var(--on-surface)}
.search-box input:focus{outline:none;border-color:var(--primary-container);box-shadow:0 0 0 3px rgba(0,51,153,.15)}
.search-box .ic{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);opacity:.5}
.karya-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.4rem}
.karya-card{padding:1.6rem;border-radius:1rem;background:rgba(255,255,255,.72);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.6);box-shadow:0 8px 40px -8px rgba(0,32,104,.1);display:flex;flex-direction:column;transition:transform .3s ease,box-shadow .3s ease}
.karya-card:hover{transform:translateY(-5px);box-shadow:0 22px 55px -18px rgba(0,32,104,.28)}
.karya-card h3{font-size:1.18rem;line-height:1.35;margin:1rem 0 .6rem}
.karya-card p{font-size:.92rem;color:var(--on-surface-variant);flex:1}
.karya-card .k-foot{display:flex;align-items:center;gap:.6rem;margin-top:1.3rem;padding-top:1rem;border-top:1px solid var(--outline-variant)}
.karya-card .k-foot .av{width:32px;height:32px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-size:12px;font-weight:700;color:var(--primary)}
.karya-card .k-foot small{font-size:12.5px;color:var(--on-surface-variant)}
.empty-state{text-align:center;padding:4rem 1rem;color:var(--on-surface-variant)}
.pager{margin-top:3rem}
[wire\:loading]{opacity:.55;transition:opacity .2s}
@media(max-width:960px){.karya-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.karya-grid{grid-template-columns:1fr}}
</style>
@endpush

<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><span>Karya</span></div>
    <span class="eyebrow">Ruang Gagasan</span>
    <h1>Karya Anggota</h1>
    <p>Artikel, esai, puisi, dan berita. Wujud nyata tradisi intelektual dan literasi kader pergerakan.</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="filter-bar">
      <div class="filter-chips">
        <button type="button" wire:click="$set('tipe', '')" @class(['chip', 'active' => $tipe === ''])>Semua</button>
        @foreach ($tipeList as $key => $label)
          <button type="button" wire:click="setTipe('{{ $key }}')" @class(['chip', 'active' => $tipe === $key])>{{ $label }}</button>
        @endforeach
      </div>
      <div class="search-box">
        <span class="ic"><x-icon name="search" :size="18"/></span>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari judul karya..." aria-label="Cari judul karya">
      </div>
    </div>

    <div wire:loading.class="opacity-50">
      @if ($karya->count())
        <div class="karya-grid">
          @foreach ($karya as $ky)
          <a href="{{ route('karya.show', $ky) }}" class="karya-card">
            <span class="k-type k-{{ $ky->tipe }}">{{ ucfirst($ky->tipe) }}</span>
            <h3>{{ $ky->judul }}</h3>
            <p>{{ Str::limit($ky->excerpt, 110) }}</p>
            <div class="k-foot">
              <span class="av">{{ strtoupper(Str::substr($ky->penulis(), 0, 2)) }}</span>
              <small>{{ $ky->penulis() }} &middot; {{ optional($ky->published_at)->translatedFormat('d M Y') }}</small>
            </div>
          </a>
          @endforeach
        </div>
        <div class="pager">{{ $karya->links() }}</div>
      @else
        <div class="empty-state">
          <p><x-icon name="book" :size="40"/></p>
          <h3>Belum ada karya</h3>
          <p>Tidak ditemukan karya yang cocok dengan filter atau pencarianmu.</p>
        </div>
      @endif
    </div>
  </div>
</section>
</div>
