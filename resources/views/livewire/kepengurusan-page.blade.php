<div class="structure-page">
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
.biro-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
.biro-card{border:1px solid var(--outline-variant);border-radius:1.25rem;background:rgba(255,255,255,.78);backdrop-filter:blur(12px);box-shadow:var(--shadow-sm);overflow:hidden}
.biro-card .b-head{display:flex;align-items:center;gap:.8rem;padding:1.15rem 1.3rem;border-bottom:1px solid var(--outline-variant)}
.biro-card .b-head .dot{width:12px;height:12px;border-radius:50%;flex:none}
.biro-card .b-head h3{font-size:1.02rem;flex:1;margin:0}
.biro-card .b-head .count{font-size:12px;color:var(--on-surface-variant);white-space:nowrap}
.biro-card .b-body{padding:1rem 1.3rem 1.3rem;display:grid;gap:.6rem}
.person{display:flex;align-items:center;gap:.75rem;padding:.55rem .6rem;border-radius:.8rem;background:var(--sc-low)}
.person .av{width:42px;height:42px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-display);font-weight:700;color:var(--primary);flex:none;overflow:hidden}
.person .av img{width:100%;height:100%;object-fit:cover}
.person .meta{min-width:0}
.person b{font-size:.9rem;color:var(--on-surface);display:block;line-height:1.3}
.person .badge-ketua{font-size:9.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#fff;background:var(--primary);border-radius:9999px;padding:.14rem .5rem;margin-left:.4rem;vertical-align:1.5px}
.person small{font-size:11.5px;color:var(--on-surface-variant);display:block}
.empty{text-align:center;color:var(--on-surface-variant);padding:3.5rem 1rem;border:1px dashed var(--outline-variant);border-radius:1.25rem}
@media(max-width:960px){.bph-grid{grid-template-columns:1fr 1fr}.biro-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.bph-grid,.biro-grid{grid-template-columns:1fr}.bph-panel{padding:2rem}}
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
      @if($pembina->isNotEmpty())
      <div class="bph-panel">
        <h2>Pembina</h2>
        <div class="bph-grid">
          @foreach($pembina as $profil)
          <div class="bph-card">
            <div class="ph">@if($profil->setuju_publikasi && $profil->foto)<img src="{{ route('media.pembina', $profil) }}" alt="{{ $profil->nama_lengkap }}" loading="lazy">@else{{ mb_substr($profil->nama_lengkap, 0, 1) }}@endif</div>
            <b>{{ $profil->nama_lengkap }}</b>
            <small>Pembina</small>
            @if($profil->setuju_publikasi && $profil->keterangan)<p>{{ $profil->keterangan }}</p>@endif
          </div>
          @endforeach
        </div>
      </div>
      @endif
      @if (! $pengurus->count())
      <div class="empty">Belum ada data kepengurusan untuk periode ini.</div>
      @else
        @if ($bph->count())
        <div class="bph-panel">
          <span class="eyebrow">Pimpinan Rayon</span>
          <h2>Badan Pengurus Harian</h2>
          @foreach ($bph->groupBy('jabatan') as $jabatan => $kelompok)
          <h3 class="structure-role">{{ $jabatan }}</h3>
          <div class="bph-grid">
            @foreach ($kelompok as $p)
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
          @endforeach
        </div>
        @endif

        <div class="biro-grid">
          @foreach ($biroList as $b)
            @php
              $anggotaBiro = ($perBiro[$b->id] ?? collect())
                  ->sortBy(fn ($p) => $p->is_ketua ? 0 : $p->urutan + 1)
                  ->values();
            @endphp
            @if ($anggotaBiro->count())
            <details class="biro-card structure-bureau" open>
              <summary class="b-head">
                <span class="dot" style="background:{{ $b->warna_aksen ?? '#003399' }}"></span>
                <h3>{{ $anggotaBiro->first()->biro_nama ?? $b->nama }}</h3>
                <span class="count">{{ $anggotaBiro->count() }} pengurus</span>
              </summary>
              <div class="b-body">
                @foreach ($anggotaBiro as $p)
                <div class="person">
                  <span class="av">
                    @if ($p->anggota->foto_url)
                    <img src="{{ $p->anggota->foto_url }}" alt="{{ $p->anggota->nama_lengkap }}" loading="lazy">
                    @else
                    {{ $p->anggota->initial() }}
                    @endif
                  </span>
                  <span class="meta">
                    <b>{{ $p->anggota->nama_lengkap }}@if ($p->is_ketua)<span class="badge-ketua">Ketua</span>@endif</b>
                    <small>{{ $p->jabatan }}</small>
                  </span>
                </div>
                @endforeach
              </div>
            </details>
            @endif
          @endforeach
        </div>
      @endif
    </div>
  </div>
</section>
</div>
