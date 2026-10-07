@extends('layouts.app')

@section('title', $biro->nama)

@push('styles')
<style>
.biro-detail{display:grid;grid-template-columns:1.4fr .6fr;gap:3rem;align-items:start}
.biro-detail .body p{color:var(--on-surface-variant);margin-bottom:1.1rem;font-size:1.02rem}
.ketua-card{padding:2rem;border-radius:1.25rem;text-align:center}
.ketua-card .av{width:120px;height:140px;margin:0 auto 1rem;border-radius:1rem;border:3px solid var(--gold);background:linear-gradient(160deg,#dce9ff,#8aa4ff);display:grid;place-items:center;font-family:var(--font-display);font-size:2.4rem;color:var(--primary);overflow:hidden}
.ketua-card .av img{width:100%;height:100%;object-fit:cover}
.ketua-card .role{font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--secondary)}
.ketua-card b{display:block;font-family:var(--font-display);font-size:1.25rem;color:var(--primary);margin:.3rem 0}
.ketua-card small{color:var(--on-surface-variant)}
.pengurus-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:1.5rem}
.person{display:flex;align-items:center;gap:.8rem;padding:.9rem;border-radius:.8rem;background:var(--sc-low)}
.person .av{width:44px;height:44px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-display);font-weight:700;color:var(--primary);flex:none;overflow:hidden}
.person .av img{width:100%;height:100%;object-fit:cover}
.person b{font-size:.92rem;color:var(--on-surface);display:block;line-height:1.2}
.person small{font-size:12px;color:var(--on-surface-variant)}
.keg-mini{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;margin-top:1.5rem}
.keg-mini a{padding:1.2rem;border-radius:.9rem;background:var(--sc-lowest);border:1px solid var(--outline-variant);transition:.2s}
.keg-mini a:hover{border-color:var(--primary-container);transform:translateY(-3px)}
.keg-mini .meta{font-size:12px;color:var(--on-surface-variant);margin-bottom:.3rem}
.keg-mini h4{font-size:1rem;font-family:var(--font-sans);color:var(--on-surface)}
@media(max-width:960px){.biro-detail{grid-template-columns:1fr}.pengurus-grid,.keg-mini{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.pengurus-grid,.keg-mini{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><a href="{{ route('biro.index') }}">Biro</a><span>&rsaquo;</span><span>{{ $biro->nama }}</span></div>
    <span class="eyebrow">Profil Biro</span>
    <h1>{{ $biro->nama }}</h1>
    @if(! $biro->is_aktif)<p>Arsip — biro nonaktif</p>@endif
    <p>{{ $biro->deskripsi }}</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="biro-detail">
      <div class="body reveal">
        
        <h2 style="margin:.5rem 0 1.2rem">Peran &amp; Fokus Gerak</h2>
        <p>{{ $biro->deskripsi }}</p>
        <p>Biro ini menjadi salah satu motor penggerak di Rayon Saintek, menjalankan program kerja yang selaras dengan visi pergerakan dan kebutuhan kader.</p>

        <h3 style="margin:2rem 0 0">Susunan Pengurus</h3>
        <div class="pengurus-grid">
          @forelse ($biro->kepengurusan as $p)
          <div class="person">
            <span class="av">
              @if ($p->anggota->foto_url)
              <img src="{{ $p->anggota->foto_url }}" alt="{{ $p->anggota->nama_lengkap }}" loading="lazy">
              @else
              {{ $p->anggota->initial() }}
              @endif
            </span>
            <span><b>{{ $p->anggota->nama_lengkap }}</b><small>{{ $p->jabatan }}</small></span>
          </div>
          @empty
          <p style="color:var(--on-surface-variant)">Belum ada data pengurus untuk periode aktif.</p>
          @endforelse
        </div>

        @if ($kegiatan->count())
        <h3 style="margin:2.5rem 0 0">Kegiatan Biro</h3>
        <div class="keg-mini">
          @foreach ($kegiatan as $k)
          <a href="{{ route('kegiatan.show', $k) }}">
            <div class="meta">{{ $k->tanggal->translatedFormat('d M Y') }}</div>
            <h4>{{ $k->judul }}</h4>
          </a>
          @endforeach
        </div>
        @endif
      </div>

      <aside>
        @if ($ketua)
        <div class="ketua-card glass reveal">
          <div class="av">
            @if ($ketua->anggota->foto_url)
            <img src="{{ $ketua->anggota->foto_url }}" alt="{{ $ketua->anggota->nama_lengkap }}" loading="lazy">
            @else
            {{ $ketua->anggota->initial() }}
            @endif
          </div>
          <div class="role">Ketua Biro</div>
          <b>{{ $ketua->anggota->nama_lengkap }}</b>
          <small>{{ $ketua->anggota->prodi }} &middot; {{ $ketua->anggota->angkatan }}</small>
        </div>
        @endif
      </aside>
    </div>
  </div>
</section>
@endsection
