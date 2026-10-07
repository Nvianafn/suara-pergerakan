@extends('layouts.app')

@section('title', 'Tentang Kami')

@push('styles')
<style>
.about-lead{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,.9fr);gap:3rem;align-items:center}
.about-lead p{color:var(--on-surface-variant);margin-bottom:1.1rem}
.about-quote{border-radius:1.25rem;padding:clamp(1.5rem,3vw,2.5rem);background:linear-gradient(120deg,#002068,#003399);color:#fff;position:relative;overflow:hidden;min-width:0;isolation:isolate}
.about-quote::after{content:"";position:absolute;right:-10%;top:-30%;width:50%;height:160%;background:radial-gradient(circle,rgba(252,212,0,.2),transparent 65%)}
.about-quote .mark{display:block;font-family:var(--font-serif);font-size:4rem;line-height:1;color:var(--gold);position:relative;z-index:2;margin-bottom:.5rem}
.about-lead .about-quote p{font-family:var(--font-serif);font-style:italic;font-size:clamp(1.25rem,2vw,1.5rem);line-height:1.6;color:#fff;position:relative;z-index:2;margin:0 0 1.25rem;overflow-wrap:anywhere}
.about-quote small{display:block;font-size:14px;line-height:1.6;color:#c8d6f2;position:relative;z-index:2}
.vm-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1rem}
.vm-card{padding:2rem;border-radius:1rem}
.vm-card .ic{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;font-size:24px;margin-bottom:1rem;background:var(--sc);color:var(--primary)}
.vm-card h3{font-size:1.3rem;margin-bottom:.7rem}
.vm-card ul{list-style:none;padding:0}
.vm-card li{padding-left:1.6rem;position:relative;margin-bottom:.6rem;color:var(--on-surface-variant);font-size:.95rem}
.vm-card li::before{content:"\2713";position:absolute;left:0;color:var(--secondary);font-weight:700}
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1.2rem}
.stat{padding:1.8rem;border-radius:1rem;text-align:center}
.stat b{font-family:var(--font-display);font-size:2.4rem;color:var(--primary);line-height:1;display:block}
.stat b .plus{color:var(--gold)}
.stat span{display:block;margin-top:.5rem;color:var(--on-surface-variant);font-size:.9rem}
.nilai-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.4rem}
.nilai{padding:1.8rem;border-radius:1rem}
.nilai h4{font-family:var(--font-display);font-size:1.15rem;margin-bottom:.5rem}
.nilai p{font-size:.92rem;color:var(--on-surface-variant)}
.cta-band{border-radius:1.5rem;padding:3rem;text-align:center;background:linear-gradient(120deg,#eff4ff,#dce9ff)}
.cta-band h2{margin-bottom:.8rem}
.cta-band p{color:var(--on-surface-variant);max-width:46ch;margin:0 auto 1.6rem}
@media(max-width:960px){.about-lead,.vm-grid{grid-template-columns:1fr}.stat-row{grid-template-columns:1fr 1fr}.nilai-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
  $about = app(\App\Services\HtmlSanitizer::class)->clean(\App\Models\Setting::get('tentang_deskripsi', ''));
  $history = app(\App\Services\HtmlSanitizer::class)->clean(\App\Models\Setting::get('tentang_sejarah', ''));
  $mission = app(\App\Services\HtmlSanitizer::class)->clean(\App\Models\Setting::get('misi', ''));
@endphp
<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><span>Tentang</span></div>
    <span class="eyebrow">Tentang Kami</span>
    <h1>Rumah Kaderisasi Intelektual Muda Saintek</h1>
    <p>Mengenal lebih dekat PMII Rayon Saintek: sejarah, nilai, dan arah gerak yang kami perjuangkan bersama.</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="about-lead">
      <div class="reveal">
        
        <h2 style="margin:.6rem 0 1.2rem">Pergerakan yang Berakar pada Ilmu &amp; Iman</h2>
        @if($about)
        <div class="prose">{!! $about !!}</div>
        @else
        <p>PMII Rayon Saintek adalah bagian dari Pergerakan Mahasiswa Islam Indonesia yang berhimpun di lingkungan Fakultas Sains dan Teknologi. Kami menjadi wadah bagi mahasiswa untuk tumbuh sebagai kader yang intelektual, religius, dan berpihak pada kepentingan umat serta bangsa.</p>
        <p>Berlandaskan nilai Ahlussunnah wal Jama&rsquo;ah An-Nahdliyah, kami memadukan tradisi keilmuan, spiritualitas, dan aksi sosial dalam satu tarikan napas pergerakan.</p>
        @endif
        <div class="about-values" style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.4rem">
          <span class="chip">Dzikir</span><span class="chip">Fikir</span><span class="chip">Amal Sholeh</span>
        </div>
      </div>
      <div class="about-quote reveal">
        <div class="mark" aria-hidden="true">&ldquo;</div>
        <p>Tangan terkepal dan maju ke muka.</p>
        <small>Semboyan Pergerakan</small>
      </div>
    </div>
  </div>
</section>

@if($history)
<section style="padding-top:0"><div class="wrap"><h2>Sejarah</h2><div class="prose">{!! $history !!}</div></div></section>
@endif
<section style="padding-top:0">
  <div class="wrap">
    <div class="stat-row">
      <div class="stat glass"><b>{{ $stats['anggota'] }}<span class="plus">+</span></b><span>Anggota aktif</span></div>
      <div class="stat glass"><b>{{ $stats['biro'] }}</b><span>Biro</span></div>
      <div class="stat glass"><b>{{ $stats['kegiatan'] }}<span class="plus">+</span></b><span>Kegiatan</span></div>
      <div class="stat glass"><b>{{ $stats['karya'] }}<span class="plus">+</span></b><span>Karya tulis</span></div>
    </div>
  </div>
</section>

<section style="padding-top:0">
  <div class="wrap">
    <div class="vm-grid">
      <div class="vm-card glass reveal">
        <div class="ic">&#127919;</div>
        <h3>Visi</h3>
        <p style="color:var(--on-surface-variant)">{{ \App\Models\Setting::get('visi') ?: 'Terbentuknya kader Saintek yang bertakwa, berintelektual tinggi, dan berkomitmen pada nilai kemanusiaan serta pergerakan.' }}</p>
      </div>
      <div class="vm-card glass reveal">
        <div class="ic">&#128203;</div>
        <h3>Misi</h3>
        @if($mission)
        <div class="prose">{!! $mission !!}</div>
        @else
        <ul>
          <li>Membina kader melalui kaderisasi berjenjang</li>
          <li>Mengembangkan tradisi keilmuan dan riset</li>
          <li>Menguatkan nilai keislaman Aswaja</li>
          <li>Menggerakkan pengabdian pada masyarakat</li>
        </ul>
        @endif
      </div>
    </div>
  </div>
</section>

<section style="padding-top:0">
  <div class="wrap">
    <div class="sec-head reveal"><h2>Nilai Dasar Pergerakan</h2></div>
    <div class="nilai-grid">
      <div class="nilai glass reveal"><h4>Tauhid</h4><p>Keyakinan pada keesaan Allah SWT.</p></div>
      <div class="nilai glass reveal"><h4>Hablumminallah</h4><p>Mengesakan Allah sebagai fondasi seluruh gerak dan pikir kader.</p></div>
      <div class="nilai glass reveal"><h4>Hablumminannas</h4><p>Menjunjung tinggi keadilan, persamaan, dan pluralisme.</p></div>
      <div class="nilai glass reveal"><h4>Hablumminalalam</h4><p> Menjaga kelestarian dan tanggung jawab terhadap lingkungan.</p></div>
    </div>
  </div>
</section>

<section style="padding-top:0">
  <div class="wrap">
    <div class="cta-band reveal">
      
      <h2>Jadi Bagian dari Barisan</h2>
      <p>Tumbuh bersama dalam ruang kaderisasi, diskusi, dan karya. Pintu pergerakan selalu terbuka untukmu.</p>
      <a href="{{ route('kontak') }}" class="btn btn-primary">Gabung Kami &rarr;</a>
    </div>
  </div>
</section>
@endsection
