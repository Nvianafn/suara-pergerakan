@extends('layouts.app')

@section('title', 'Beranda')

@push('styles')
<style>
.hero{position:relative;min-height:100vh;display:flex;align-items:center;padding-top:7rem;padding-bottom:4rem;overflow:hidden}
.hero-bg{position:absolute;inset:0;z-index:-1}
.hero-bg img{width:100%;height:100%;object-fit:cover}
.hero-bg::after{content:"";position:absolute;inset:0;background:linear-gradient(105deg,rgba(0,20,60,.92) 0%,rgba(0,32,104,.7) 42%,rgba(0,51,153,.28) 70%,rgba(0,32,104,.15) 100%)}
.hero-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:3rem;align-items:center;width:100%;position:relative;z-index:2}
.hero-copy .eyebrow{color:var(--gold)}
.hero-copy h1{color:#fff;font-size:clamp(2.6rem,5.2vw,4rem);letter-spacing:-.025em;margin:1.1rem 0 1.25rem}
.hero-copy h1 em{font-style:normal;color:var(--gold)}
.hero-copy p{color:#dce9ff;font-size:1.15rem;max-width:42ch;margin-bottom:2rem}
.hero-cta{display:flex;gap:1rem;flex-wrap:wrap}
.hero-card{padding:1.9rem;border-radius:var(--r-card)}
.hero-card h3{font-size:1.4rem;margin:0 0 .8rem}
.hero-card p{font-size:.97rem;color:var(--on-surface-variant);margin-bottom:1.3rem}
.hero-card .quote{border-left:3px solid var(--gold);padding-left:1rem;font-family:var(--font-serif);font-style:italic;color:var(--primary);font-size:1.02rem}
.about{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center}
.about-text h2{font-size:clamp(1.9rem,3.6vw,2.6rem);margin:0 0 1.1rem}
.about-text p{color:var(--on-surface-variant);margin-bottom:1.1rem}
.about-values{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.4rem}
.stats{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem}
.stat{padding:1.7rem;border-radius:var(--r-card)}
.stat b{font-family:var(--font-display);font-size:2.7rem;color:var(--primary);line-height:1;display:block;letter-spacing:-.02em}
.stat b .plus{color:var(--gold)}
.stat span{display:block;margin-top:.5rem;color:var(--on-surface-variant);font-size:.92rem}
.stat:nth-child(2),.stat:nth-child(3){transform:translateY(1.4rem)}
.biro-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.4rem}
.biro-card{padding:1.9rem;border-radius:var(--r-card);position:relative;overflow:hidden}
.biro-card .ic{width:54px;height:54px;border-radius:14px;display:grid;place-items:center;font-size:24px;margin-bottom:1.15rem;background:var(--sc);color:var(--primary)}
.biro-card h3{font-size:1.22rem;margin-bottom:.5rem}
.biro-card p{font-size:.92rem;color:var(--on-surface-variant);margin-bottom:1.15rem}
.biro-card .more{font-family:var(--font-display);font-size:13.5px;font-weight:600;color:var(--primary-container);display:inline-flex;gap:.35rem;align-items:center;transition:gap .25s ease}
.biro-card:hover .more{gap:.6rem}
.biro-card .accent{position:absolute;top:0;left:0;height:4px;width:100%}
.keg-head{display:flex;justify-content:space-between;align-items:flex-end;gap:2rem;margin-bottom:2.6rem;flex-wrap:wrap}
.keg-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:1.5rem}
.keg-card{border-radius:var(--r-card);overflow:hidden;background:var(--sc-lowest);border:1px solid var(--outline-variant);transition:transform .3s ease,box-shadow .3s ease}
.keg-card:first-child{grid-row:span 2;display:flex;flex-direction:column}
.keg-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-md)}
.keg-thumb{height:180px;position:relative;overflow:hidden}
.keg-card:first-child .keg-thumb{height:300px;flex:1}
.keg-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
.keg-card:hover .keg-thumb img{transform:scale(1.06)}
.keg-thumb .tag{position:absolute;top:.8rem;left:.8rem;background:rgba(255,255,255,.85);backdrop-filter:blur(8px);color:var(--primary);font-family:var(--font-display);font-size:11px;font-weight:600;letter-spacing:.05em;padding:.32rem .72rem;border-radius:var(--r-pill)}
.keg-body{padding:1.35rem}
.keg-body .meta{font-family:var(--font-display);font-size:12px;color:var(--on-surface-variant);letter-spacing:.03em;margin-bottom:.5rem}
.keg-body h3{font-size:1.14rem;line-height:1.3}
.keg-card:first-child .keg-body h3{font-size:1.5rem}
.karya{background:linear-gradient(180deg,#eff4ff,#e5eeff)}
.karya-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.3rem}
.karya-card{padding:1.6rem;border-radius:var(--r-card);background:var(--sc-lowest);border:1px solid var(--outline-variant);box-shadow:var(--shadow-sm);display:flex;flex-direction:column;transition:transform .3s ease,box-shadow .3s ease}
.karya-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-md)}
.karya-card.tall{grid-row:span 2}
.karya-card h3{font-size:1.16rem;line-height:1.32;margin:1rem 0 .6rem}
.karya-card p{font-size:.9rem;color:var(--on-surface-variant);flex:1}
.karya-card .k-foot{display:flex;align-items:center;gap:.6rem;margin-top:1.2rem;padding-top:1rem;border-top:1px solid var(--outline-variant)}
.karya-card .k-foot .av{width:30px;height:30px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-display);font-size:12px;font-weight:700;color:var(--primary)}
.karya-card .k-foot small{font-size:12.5px;color:var(--on-surface-variant)}
.peng{position:relative;border-radius:24px;overflow:hidden;padding:3.5rem;background:linear-gradient(120deg,#002068,#003399)}
.peng::after{content:"";position:absolute;right:-8%;top:-30%;width:44%;height:160%;background:radial-gradient(circle,rgba(252,212,0,.22),transparent 65%)}
.peng-grid{position:relative;z-index:2;display:grid;grid-template-columns:1fr auto;gap:3rem;align-items:center}
.peng-tag{display:inline-block;font-family:var(--font-display);font-size:12px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--primary);background:var(--gold);padding:.32rem .8rem;border-radius:var(--r-pill);margin-bottom:1rem}
.peng-grid h2{color:#fff;font-size:clamp(1.8rem,3.4vw,2.4rem);margin:0 0 1rem}
.peng-grid p{color:#dce9ff;max-width:44ch;margin-bottom:1.8rem}
.leaders{display:flex;gap:1.2rem}
.leader{text-align:center}
.leader .ph{width:112px;height:132px;border-radius:var(--r-media);background:linear-gradient(160deg,#dce9ff,#8aa4ff);border:2px solid rgba(255,255,255,.35);display:grid;place-items:center;font-family:var(--font-display);font-weight:700;font-size:2rem;color:var(--primary);margin-bottom:.7rem;overflow:hidden}
.leader .ph img{width:100%;height:100%;object-fit:cover}
.leader b{color:#fff;font-family:var(--font-sans);font-weight:600;font-size:.92rem;display:block}
.leader small{color:var(--on-primary-container);font-size:12px}
@media(max-width:960px){.hero-grid,.about,.peng-grid{grid-template-columns:1fr;gap:2rem}.hero-card{display:none}.biro-grid,.karya-grid{grid-template-columns:1fr 1fr}.keg-grid{grid-template-columns:1fr 1fr}.keg-card:first-child{grid-row:auto;grid-column:span 2}.keg-card:first-child .keg-thumb{height:220px}.karya-card.tall{grid-row:auto}.stat:nth-child(2),.stat:nth-child(3){transform:none}.peng-grid{text-align:center}.leaders{justify-content:center} }
@media(max-width:560px){.biro-grid,.keg-grid,.karya-grid,.stats{grid-template-columns:1fr}.keg-card:first-child{grid-column:auto}.peng{padding:2rem} }
</style>
@endpush

@section('content')
@php
  $biroIcons = ['&#128218;','&#129309;','&#128241;','&#127757;','&#9819;','&#128737;'];
@endphp

<section class="hero">
  <div class="hero-bg"><img src="{{ asset('images/hero.png') }}" alt="Mahasiswa PMII Rayon Saintek berdiskusi di kampus"></div>
  <div class="wrap">
    <div class="hero-grid">
      <div class="hero-copy">
        <span class="eyebrow">Pergerakan Mahasiswa Islam Indonesia</span>
        <h1>Intelektual Muda, <em>Bergerak</em> untuk Umat &amp; Bangsa</h1>
        <p>Rumah kaderisasi, gagasan, dan karya bagi mahasiswa Sains &amp; Teknologi yang progresif dan berakhlak.</p>
        <div class="hero-cta">
          <a href="{{ route('tentang') }}" class="btn btn-primary btn-lg">Kenali Kami &rarr;</a>
          <a href="{{ route('karya.index') }}" class="btn btn-onnavy btn-lg">Lihat Karya</a>
        </div>
      </div>
      <div class="hero-card glass">
        <h3>Dzikir, Fikir, Amal Sholeh</h3>
        <p>Berlandaskan Ahlussunnah wal Jama&rsquo;ah An-Nahdliyah, kami memadukan spiritualitas, nalar ilmiah, dan aksi nyata.</p>
        <div class="quote">&ldquo;Tangan terkepal dan maju ke muka.&rdquo;</div>
      </div>
    </div>
  </div>
</section>

<section id="tentang">
  <div class="wrap">
    <div class="about">
      <div class="about-text reveal">
        <h2>Wadah Kader Saintek yang Kritis &amp; Berkarakter</h2>
        <p>PMII Rayon Saintek adalah komunitas pergerakan mahasiswa di lingkungan Fakultas Sains dan Teknologi. Kami membina kader agar tumbuh menjadi pribadi yang intelektual, religius, dan peduli pada persoalan sosial-kemasyarakatan.</p>
        <p>Lewat diskusi, riset, pelatihan, hingga aksi kemasyarakatan, kami menjembatani ilmu pengetahuan dengan nilai keislaman dan semangat kebangsaan.</p>
        <div class="about-values">
          <span class="chip">Aswaja An-Nahdliyah</span>
          <span class="chip">Intelektualitas</span>
          <span class="chip">Kaderisasi</span>
          <span class="chip">Sosial Kemasyarakatan</span>
        </div>
        <div style="margin-top:1.8rem"><a href="{{ route('tentang') }}" class="btn btn-outline">Selengkapnya &rarr;</a></div>
      </div>
      <div class="stats reveal">
        <div class="stat glass"><b>{{ $stats['anggota'] }}<span class="plus">+</span></b><span>Anggota aktif lintas angkatan</span></div>
        <div class="stat glass"><b>{{ $stats['biro'] }}</b><span>Biro yang bergerak di bidangnya</span></div>
        <div class="stat glass"><b>{{ $stats['kegiatan'] }}<span class="plus">+</span></b><span>Kegiatan &amp; program terdokumentasi</span></div>
        <div class="stat glass"><b>{{ $stats['karya'] }}<span class="plus">+</span></b><span>Karya tulis anggota dipublikasi</span></div>
      </div>
    </div>
  </div>
</section>

<section id="biro">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">Struktur Gerakan</span>
      <h2>Enam Biro, Satu Barisan</h2>
      <p>Setiap biro menjadi motor penggerak di bidangnya masing-masing, saling melengkapi dalam satu tujuan pergerakan.</p>
    </div>
    <div class="biro-grid">
      @foreach ($biro as $b)
      <a href="{{ route('biro.show', $b) }}" class="biro-card glass reveal">
        <span class="accent" style="background:{{ $b->warna_aksen ?? '#003399' }}"></span>
        <div class="ic">{!! $biroIcons[$loop->index % count($biroIcons)] !!}</div>
        <h3>{{ $b->nama }}</h3>
        <p>{{ Str::limit($b->deskripsi, 90) }}</p>
        <span class="more">Lihat profil &rarr;</span>
      </a>
      @endforeach
    </div>
  </div>
</section>

<section id="kegiatan">
  <div class="wrap">
    <div class="keg-head">
      <div class="sec-head reveal" style="margin-bottom:0">
        <h2>Kegiatan Terbaru</h2>
      </div>
      <a href="{{ route('kegiatan.index') }}" class="btn btn-outline reveal">Lihat Semua Kegiatan</a>
    </div>
    <div class="keg-grid">
      @foreach ($kegiatanTerbaru as $k)
      <a href="{{ route('kegiatan.show', $k) }}" class="keg-card reveal">
        <div class="keg-thumb">
          <img src="{{ $k->thumbnail ? asset('storage/'.$k->thumbnail) : asset('images/hero.png') }}" alt="{{ $k->judul }}">
          @if ($k->biro)<span class="tag">{{ Str::of($k->biro->nama)->replace('Biro ', '') }}</span>@endif
        </div>
        <div class="keg-body">
          <div class="meta">{{ $k->tanggal->translatedFormat('d F Y') }} @if($k->lokasi)&middot; {{ $k->lokasi }} @endif</div>
          <h3>{{ $k->judul }}</h3>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</section>

<section id="karya" class="karya">
  <div class="wrap">
    <div class="sec-head center reveal">
      <span class="eyebrow">Ruang Gagasan</span>
      <h2>Karya Pilihan Anggota</h2>
      <p>Artikel, esai, puisi, dan berita. Wujud nyata tradisi intelektual pergerakan.</p>
    </div>
    <div class="karya-grid">
      @foreach ($karyaPilihan as $ky)
      <a href="{{ route('karya.show', $ky) }}" class="karya-card reveal @if($loop->first) tall @endif">
        <span class="k-type k-{{ $ky->tipe }}">{{ ucfirst($ky->tipe) }}</span>
        <h3>{{ $ky->judul }}</h3>
        <p>{{ Str::limit($ky->excerpt, $loop->first ? 150 : 80) }}</p>
        <div class="k-foot"><span class="av">{{ strtoupper(Str::substr($ky->penulis(), 0, 2)) }}</span><small>{{ $ky->penulis() }}</small></div>
      </a>
      @endforeach
    </div>
    <div style="text-align:center;margin-top:2.6rem" class="reveal">
      <a href="{{ route('karya.index') }}" class="btn btn-primary">Jelajahi Semua Karya &rarr;</a>
    </div>
  </div>
</section>

<section id="kepengurusan">
  <div class="wrap">
    <div class="peng reveal">
      <div class="peng-grid">
        <div>
          <span class="peng-tag">Periode {{ $periodeAktif?->nama ?? '2025 / 2026' }}</span>
          <h2>Barisan yang Menggerakkan</h2>
          <p>Dipimpin oleh kader-kader terbaik, struktur kepengurusan kami solid dan siap melayani anggota serta menjalankan program pergerakan.</p>
          <a href="{{ route('kepengurusan') }}" class="btn btn-primary">Lihat Struktur Lengkap &rarr;</a>
        </div>
        <div class="leaders">
          @foreach ($bph as $p)
          <div class="leader">
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
    </div>
  </div>
</section>
@endsection
