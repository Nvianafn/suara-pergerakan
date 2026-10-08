@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
@php
  $rayon = \App\Models\Setting::get('nama_rayon', 'PMII Rayon Saintek');
  $icons = ['book', 'people', 'chat', 'globe', 'spark', 'shield'];
@endphp
<div class="home-redesign">
  <section class="movement-hero">
    <div class="wrap movement-grid">
      <div class="movement-copy">
        <span class="eyebrow">Selamat datang di rumah pergerakan</span>
        <h1>{{ $rayon }}</h1>
        <div class="hero-motto">Dzikir, Fikir, Amal Sholeh.</div>
        <p>Ruang bertumbuh, berpikir, dan berkarya bersama {{ $rayon }}. Merawat tradisi, menyalakan keberanian, membawa ilmu menjadi aksi.</p>
        <div class="movement-actions">
          <a href="{{ route('tentang') }}" class="btn btn-primary">Kenali rayon kami <x-icon name="arrow" :size="20"/></a>
          <a href="{{ route('karya.index') }}" class="movement-text-link">Jelajahi gagasan <x-icon name="arrow" :size="20"/></a>
        </div>
        <div class="hero-highlights">
          <div class="hero-highlight"><div><strong>{{ $stats['anggota'] }}</strong><span>Anggota aktif</span></div><x-icon name="people" :size="32"/></div>
          <div class="hero-highlight"><div><strong>{{ $periodeAktif?->tahun_mulai ?? '—' }}</strong><span>{{ $periodeAktif?->nama ?? 'Periode belum ditetapkan' }}</span></div><x-icon name="shield" :size="32"/></div>
        </div>
      </div>
      <div class="movement-visual">
        <div class="identity-orbit"><span class="orbit-ring orbit-ring-outer"></span><span class="orbit-ring orbit-ring-inner"></span><x-icon name="spark" :size="36"/><img src="{{ \App\Models\Setting::imageUrl('logo', 'images/logo.png') }}" alt="Lambang PMII Rayon Saintek" fetchpriority="high" width="280" height="280"><span class="identity-word">SAINTEK<br>BERGERAK.</span></div>
        <span class="visual-label">BERAKAR DALAM NILAI · BERGERAK UNTUK PERUBAHAN</span>
      </div>
    </div>
  </section>
  <div class="values-strip" aria-label="Nilai pergerakan"><div class="wrap"><span>Kaderisasi</span><x-icon name="spark" :size="18"/><span>Intelektualitas</span><x-icon name="spark" :size="18"/><span>Keislaman</span><x-icon name="spark" :size="18"/><span>Kebangsaan</span></div></div>

  <section class="movement-about" id="tentang">
    <div class="wrap">
      <div class="intro-grid"><div><span class="eyebrow">Lebih dari sebuah organisasi</span><h2>Tempat bertemu.<br>Ruang untuk <em>bertumbuh.</em></h2></div><div><p>{{ \App\Models\Setting::get('deskripsi_singkat') ?: 'PMII Rayon Saintek adalah rumah kaderisasi bagi mahasiswa Sains dan Teknologi. Di sini, keilmuan bertemu nilai keislaman, dan gagasan tumbuh menjadi kontribusi bagi masyarakat.' }}</p><a href="{{ route('tentang') }}" class="movement-text-link">Cerita tentang kami <x-icon :size="20"/></a></div></div>
      <figure class="rayon-documentation"><img src="{{ \App\Models\Setting::imageUrl('hero_image', 'images/hero.png') }}" alt="Kebersamaan kader PMII Rayon Saintek" loading="lazy" width="1200" height="640"><figcaption>Satu rumah, banyak cerita. Bersama dalam pergerakan.</figcaption></figure>
      <div class="movement-stats">
        @foreach(['anggota' => 'Anggota aktif', 'biro' => 'Biro organisasi', 'kegiatan' => 'Kegiatan terdokumentasi', 'karya' => 'Karya dipublikasikan'] as $key => $label)
        <div><strong>{{ $stats[$key] }}</strong><span>{{ $label }}</span></div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="movement-works" id="karya">
    <div class="wrap">
      <div class="movement-section-head"><div><span class="eyebrow">Suara dari barisan</span><h2>Gagasan yang <em>hidup.</em></h2></div><a href="{{ route('karya.index') }}" class="movement-text-link">Semua karya <x-icon :size="20"/></a></div>
      <div class="editorial-grid">
        @forelse($karyaPilihan as $ky)
        <a href="{{ route('karya.show', $ky) }}" class="editorial-card"><div class="editorial-top"><span class="k-type k-{{ $ky->tipe }}">{{ ucfirst($ky->tipe) }}</span><x-icon :size="22"/></div><h3>{{ $ky->judul }}</h3><p>{{ Str::limit($ky->excerpt, 140) }}</p><div class="editorial-author"><span>{{ $ky->penulis() }}</span><span>Suara Pergerakan</span></div></a>
        @empty
        <div class="movement-empty"><x-icon name="book" :size="36"/><h3>Setiap gagasan punya tempat.</h3><p>Karya pertama sedang menanti untuk dibagikan. Jelajahi ruang karya kami dan ikuti tulisan terbaru dari kader.</p></div>
        @endforelse
      </div>
    </div>
  </section>

  <section id="kegiatan" class="movement-events">
    <div class="wrap">
      <div class="movement-section-head"><div><span class="eyebrow">Dari gagasan menjadi aksi</span><h2>Jejak <em>pergerakan.</em></h2></div><a href="{{ route('kegiatan.index') }}" class="movement-text-link">Semua kegiatan <x-icon :size="20"/></a></div>
      <div class="movement-event-grid">
        @forelse($kegiatanTerbaru as $k)
        <a href="{{ route('kegiatan.show', $k) }}" class="movement-event-card"><div class="event-photo"><img src="{{ $k->thumbnail ? \App\Services\PublicMedia::url($k->thumbnail) : asset('images/hero.png') }}" alt="{{ $k->judul }}" loading="lazy" width="600" height="400">@if($k->biro_label)<span>{{ $k->biro_label }}</span>@endif</div><div class="event-content"><span class="event-date">{{ $k->tanggal->translatedFormat('d F Y') }}</span><h3>{{ $k->judul }}</h3><span class="movement-text-link">Lihat cerita <x-icon :size="20"/></span></div></a>
        @empty
        <div class="movement-empty"><x-icon name="calendar" :size="36"/><h3>Langkah kecil, dampak bersama.</h3><p>Dokumentasi kegiatan akan hadir di sini. Temukan kabar dan agenda rayon melalui halaman kegiatan.</p></div>
        @endforelse
      </div>
    </div>
  </section>

  <section id="biro" class="movement-bureaus">
    <div class="wrap"><div class="movement-section-head"><div><span class="eyebrow">Banyak peran, satu tujuan</span><h2>Bergerak <em>bersama.</em></h2></div><p>Setiap biro membuka ruang kontribusi. Saling melengkapi, menguatkan satu barisan.</p></div><div class="movement-bureau-grid">
      @forelse($biro as $b)
      <a href="{{ route('biro.show', $b) }}" class="movement-bureau"><span class="bureau-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><x-icon :name="$icons[$loop->index % count($icons)]" :size="32"/><h3>{{ $b->nama }}</h3><p>{{ Str::limit($b->deskripsi, 110) }}</p><span class="movement-text-link">Kenali biro <x-icon :size="20"/></span></a>
      @empty
      <div class="movement-empty"><x-icon name="people" :size="36"/><h3>Ruang kontribusi bersama.</h3><p>Profil biro akan hadir setelah struktur organisasi ditetapkan.</p></div>
      @endforelse
    </div></div>
  </section>

  <section class="movement-community" id="kepengurusan"><div class="wrap"><div class="community-panel"><div><span class="eyebrow">Kenali barisan kita</span><h2>Satu rumah.<br>Beragam cerita.<br><em>Satu pergerakan.</em></h2></div><div><p>Kenali Pembina dan pengurus yang membersamai perjalanan rayon. Temukan struktur periode aktif serta jejak kepengurusan dari masa ke masa.</p><a href="{{ route('kepengurusan') }}" class="btn btn-primary">Lihat kepengurusan <x-icon :size="20"/></a>@if($periodeAktif)<span class="community-period">{{ $periodeAktif->nama }}</span>@endif</div></div></div></section>
  <section class="movement-contact"><div class="wrap"><span class="eyebrow">Mari terhubung</span><h2>Pergerakan dimulai<br>dari sebuah <em>percakapan.</em></h2><a href="{{ route('kontak') }}" class="btn btn-primary">Hubungi kami <x-icon :size="20"/></a></div></section>
</div>
@endsection
