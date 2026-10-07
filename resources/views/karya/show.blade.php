@extends('layouts.app')

@section('title', $karya->judul)

@push('styles')
<style>
.karya-article{max-width:760px;margin:0 auto}
.karya-head{text-align:center;margin-bottom:2.5rem}
.karya-head h1{font-size:clamp(1.8rem,3.6vw,2.6rem);margin:1rem 0}
.karya-byline{display:flex;align-items:center;justify-content:center;gap:.8rem;color:var(--on-surface-variant);font-size:.95rem}
.karya-byline .av{width:40px;height:40px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-display);font-weight:700;color:var(--primary)}
.prose{font-size:1.1rem;line-height:1.9;color:var(--on-surface)}
.prose p{margin-bottom:1.4rem}
.prose h2,.prose h3{margin:2rem 0 1rem}
.prose blockquote{border-left:3px solid var(--gold);padding-left:1.2rem;font-family:var(--font-serif);font-style:italic;color:var(--primary);margin:1.5rem 0}
.tags{display:flex;gap:.5rem;flex-wrap:wrap;margin:2.5rem 0;padding-top:1.5rem;border-top:1px solid var(--outline-variant)}
.tag-chip{font-size:12.5px;padding:.35rem .8rem;border-radius:9999px;background:var(--sc);color:var(--primary)}
.related{display:grid;grid-template-columns:repeat(3,1fr);gap:1.3rem;margin-top:1.5rem}
.related a{padding:1.4rem;border-radius:1rem;background:var(--sc-lowest);border:1px solid var(--outline-variant);transition:.2s;display:flex;flex-direction:column}
.related a:hover{transform:translateY(-4px);box-shadow:0 20px 50px -18px rgba(0,32,104,.22)}
.related h4{font-size:1.02rem;font-family:var(--font-display);color:var(--primary);margin:.7rem 0 .4rem}
.related small{font-size:12px;color:var(--on-surface-variant)}
@media(max-width:760px){.related{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><a href="{{ route('karya.index') }}">Karya</a><span>&rsaquo;</span><span>{{ Str::limit($karya->judul, 40) }}</span></div>
    <span class="eyebrow">{{ ucfirst($karya->tipe) }}</span>
    <h1 style="max-width:24ch">{{ $karya->judul }}</h1>
  </div>
</section>

<section>
  <div class="wrap">
    <article class="karya-article reveal">
      <div class="karya-head">
        <span class="k-type k-{{ $karya->tipe }}">{{ ucfirst($karya->tipe) }}</span>
        <div class="karya-byline" style="margin-top:1.2rem">
          <span class="av">{{ strtoupper(Str::substr($karya->penulis(), 0, 2)) }}</span>
          <span>Oleh <b>{{ $karya->penulis() }}</b> &middot; {{ optional($karya->published_at)->translatedFormat('d F Y') }}</span>
        </div>
      </div>

      <div class="prose">
        {!! $karya->safe_html !!}
      </div>

      @if (!empty($karya->tags))
      <div class="tags">
        @foreach ($karya->tags as $t)<span class="tag-chip">#{{ $t }}</span>@endforeach
      </div>
      @endif
    </article>
  </div>
</section>

@if ($terkait->count())
<section style="padding-top:0">
  <div class="wrap">
    <div class="sec-head"><h2>Karya Terkait</h2></div>
    <div class="related">
      @foreach ($terkait as $ky)
      <a href="{{ route('karya.show', $ky) }}">
        <span class="k-type k-{{ $ky->tipe }}">{{ ucfirst($ky->tipe) }}</span>
        <h4>{{ Str::limit($ky->judul, 60) }}</h4>
        <small>{{ $ky->penulis() }}</small>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
