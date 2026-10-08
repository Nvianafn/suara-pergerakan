@extends('layouts.app')

@section('title', $kegiatan->judul)

@push('styles')
<style>
.keg-cover{height:clamp(260px,42vh,420px);border-radius:1.25rem;overflow:hidden;position:relative;margin-bottom:2.5rem}
.keg-cover img{width:100%;height:100%;object-fit:cover}
.keg-cover::after{content:"";position:absolute;inset:0;background:linear-gradient(0deg,rgba(0,20,60,.55),transparent 55%)}
.keg-layout{display:grid;grid-template-columns:1.5fr .5fr;gap:3rem;align-items:start}
.keg-article p{color:var(--on-surface-variant);font-size:1.05rem;margin-bottom:1.2rem;line-height:1.8}
.gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-top:2rem}
.gallery img{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:.7rem;cursor:pointer;transition:transform .3s ease}
.gallery img:hover{transform:scale(1.03)}
.keg-side{position:sticky;top:6rem}
.info-card{padding:1.6rem;border-radius:1rem;margin-bottom:1.2rem}
.info-card h4{font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:var(--primary);margin-bottom:1rem}
.info-row{display:flex;gap:.7rem;align-items:flex-start;margin-bottom:.9rem;font-size:.92rem}
.info-row .ic{opacity:.6}
.info-row b{display:block;color:var(--on-surface)}
.info-row small{color:var(--on-surface-variant)}
.other-keg a{display:block;padding:.9rem 0;border-bottom:1px solid var(--outline-variant);font-size:.92rem;color:var(--on-surface)}
.other-keg a:last-child{border-bottom:none}
.other-keg a:hover{color:var(--primary)}
.other-keg .meta{font-size:11.5px;color:var(--on-surface-variant)}
@media(max-width:960px){.keg-layout{grid-template-columns:1fr}.keg-side{position:static}.gallery{grid-template-columns:1fr 1fr}}
</style>
@endpush

@section('content')
<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><a href="{{ route('kegiatan.index') }}">Kegiatan</a><span>&rsaquo;</span><span>{{ Str::limit($kegiatan->judul, 40) }}</span></div>
    @if ($kegiatan->biro_label)<span class="eyebrow">{{ $kegiatan->biro_label }}</span>@endif
    <h1>{{ $kegiatan->judul }}</h1>
    <p>{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }} @if($kegiatan->lokasi)&middot; {{ $kegiatan->lokasi }} @endif</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="keg-cover">
      <img src="{{ $kegiatan->thumbnail ? \App\Services\PublicMedia::url($kegiatan->thumbnail) : asset('images/hero.png') }}" alt="{{ $kegiatan->judul }}">
    </div>
    <div class="keg-layout">
      <div class="keg-article reveal">
        {!! $kegiatan->safe_html !!}

        @if ($kegiatan->foto->count())
        <h3 style="margin-top:1rem">Dokumentasi</h3>
        <div class="gallery">
          @foreach ($kegiatan->foto as $f)
          <img src="{{ Str::startsWith($f->path, 'seed/') ? asset('images/hero.png') : \App\Services\PublicMedia::url($f->path) }}" alt="{{ $f->caption ?? $kegiatan->judul }}">
          @endforeach
        </div>
        @endif
      </div>

      <aside class="keg-side">
        <div class="info-card glass">
          <h4>Detail Kegiatan</h4>
          <div class="info-row"><span class="ic"><x-icon name="calendar"/></span><span><b>{{ $kegiatan->tanggal->translatedFormat('d F Y') }}</b><small>Tanggal pelaksanaan</small></span></div>
          @if ($kegiatan->lokasi)<div class="info-row"><span class="ic"><x-icon name="pin"/></span><span><b>{{ $kegiatan->lokasi }}</b><small>Lokasi</small></span></div>@endif
          @if ($kegiatan->biro_label)<div class="info-row"><span class="ic"><x-icon name="people"/></span><span><b>{{ $kegiatan->biro_label }}</b><small>Penyelenggara</small></span></div>@endif
        </div>

        @if ($lainnya->count())
        <div class="info-card glass other-keg">
          <h4>Kegiatan Lainnya</h4>
          @foreach ($lainnya as $k)
          <a href="{{ route('kegiatan.show', $k) }}">
            <div class="meta">{{ $k->tanggal->translatedFormat('d M Y') }}</div>
            {{ Str::limit($k->judul, 46) }}
          </a>
          @endforeach
        </div>
        @endif
      </aside>
    </div>
  </div>
</section>
@endsection
