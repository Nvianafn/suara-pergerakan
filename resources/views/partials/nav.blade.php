@php use App\Models\Setting; @endphp
<header id="hdr" class="site-header">
  <div class="wrap">
    <nav class="nav glass">
      <a class="brand" href="{{ route('home') }}">
        <img class="mark" src="{{ Setting::imageUrl('logo', 'images/logo.png') }}" alt="Logo PMII Rayon Saintek" style="background:none;padding:0;border-radius:50%;object-fit:cover">
        <span class="brand-txt"><b>{{ Setting::get('nama_rayon', 'PMII Rayon Saintek') }}</b><span>{{ Setting::get('tagline') ?: 'Bergerak · Berpikir · Berkarya' }}</span></span>
      </a>
      <div class="nav-links">
        <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Beranda</a>
        <a href="{{ route('tentang') }}" @class(['active' => request()->routeIs('tentang')])>Tentang</a>
        <a href="{{ route('biro.index') }}" @class(['active' => request()->routeIs('biro.*')])>Biro</a>
        <a href="{{ route('kegiatan.index') }}" @class(['active' => request()->routeIs('kegiatan.*')])>Kegiatan</a>
        <a href="{{ route('karya.index') }}" @class(['active' => request()->routeIs('karya.*')])>Karya</a>
        <a href="{{ route('kepengurusan') }}" @class(['active' => request()->routeIs('kepengurusan')])>Kepengurusan</a>
      </div>
      <a href="{{ route('kontak') }}" class="btn btn-primary">Mari terhubung <x-icon :size="18"/></a>
      <button type="button" class="btn btn-ghost menu-btn" aria-label="Buka menu navigasi" aria-controls="mobile-nav" aria-expanded="false"><x-icon name="menu"/></button>
    </nav>
    <div id="mobile-nav" class="glass">
      <a href="{{ route('home') }}">Beranda</a>
      <a href="{{ route('tentang') }}">Tentang</a>
      <a href="{{ route('biro.index') }}">Biro</a>
      <a href="{{ route('kegiatan.index') }}">Kegiatan</a>
      <a href="{{ route('karya.index') }}">Karya</a>
      <a href="{{ route('kepengurusan') }}">Kepengurusan</a>
      <a href="{{ route('kontak') }}">Gabung Kami</a>
    </div>
  </div>
</header>

<style>
/* === Navbar mobile fix (self-contained, override app.css via #hdr specificity) === */
@media(max-width:960px){
  #hdr{padding:.7rem 0}
  #hdr.scrolled{padding:.45rem 0}
  #hdr .nav{gap:.5rem;padding:.45rem .5rem .45rem 1rem}
  #hdr .nav > .btn-primary{display:none}
  #hdr .menu-btn{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;font-size:20px;padding:0;border-radius:12px;margin-left:auto}
  #hdr .brand{gap:.55rem;min-width:0}
  #hdr .brand .mark{width:36px;height:36px;font-size:15px}
  #hdr .brand b{font-size:14.5px;white-space:nowrap}
  #hdr .brand .brand-txt span{font-size:9.5px;letter-spacing:.1em}
  #mobile-nav{margin-top:.5rem;padding:.75rem .9rem}
  #mobile-nav a{padding:.8rem .9rem;font-size:15px}
  #mobile-nav a:last-child{margin-top:.35rem;background:var(--primary);color:#fff;text-align:center;font-weight:600}
  #mobile-nav a:last-child:hover{background:#003399;color:#fff}
}
@media(max-width:420px){
  #hdr .brand .brand-txt span{display:none}
  #hdr .brand b{font-size:14px}
}
</style>
