@extends('layouts.app')

@section('title', 'Biro')

@push('styles')
<style>
.biro-list{display:grid;grid-template-columns:repeat(2,1fr);gap:1.5rem}
.biro-item{position:relative;padding:2rem;border-radius:1rem;overflow:hidden;display:flex;gap:1.4rem;align-items:flex-start;transition:transform .3s ease,box-shadow .3s ease}
.biro-item:hover{transform:translateY(-4px);box-shadow:0 24px 60px -18px rgba(0,32,104,.25)}
.biro-item .bar{position:absolute;left:0;top:0;bottom:0;width:5px}
.biro-item .ic{width:58px;height:58px;border-radius:16px;display:grid;place-items:center;font-size:26px;background:var(--sc);color:var(--primary);flex:none}
.biro-item h3{font-size:1.25rem;margin-bottom:.4rem}
.biro-item p{font-size:.92rem;color:var(--on-surface-variant);margin-bottom:.9rem}
.biro-item .meta{display:flex;gap:1rem;font-size:12.5px;color:var(--on-surface-variant)}
.biro-item .meta b{color:var(--primary)}
.biro-item .more{font-size:13.5px;font-weight:600;color:var(--primary-container);margin-top:.9rem;display:inline-flex;gap:.35rem}
@media(max-width:760px){.biro-list{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php $icons = ['&#128218;','&#129309;','&#128241;','&#127757;','&#9819;','&#128737;']; @endphp
<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><span>Biro</span></div>
    <span class="eyebrow">Struktur Gerakan</span>
    <h1>{{ $biro->count() }} Biro, Satu Barisan</h1>
    <p>Setiap biro menjadi motor penggerak di bidangnya, saling melengkapi dalam satu tujuan pergerakan.</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="biro-list">
      @foreach ($biro as $b)
      <a href="{{ route('biro.show', $b) }}" class="biro-item glass reveal">
        <span class="bar" style="background:{{ $b->warna_aksen ?? '#003399' }}"></span>
        <div class="ic">{!! $icons[$loop->index % count($icons)] !!}</div>
        <div>
          <h3>{{ $b->nama }}</h3>
          <p>{{ Str::limit($b->deskripsi, 110) }}</p>
          <div class="meta">
            <span><b>{{ $b->kepengurusan_count }}</b> pengurus</span>
            <span><b>{{ $b->kegiatan_count }}</b> kegiatan</span>
          </div>
          <span class="more">Lihat profil biro &rarr;</span>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</section>
@endsection
