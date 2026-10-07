<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard') &middot; Admin PMII Saintek</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
.admin-shell{display:grid;grid-template-columns:264px 1fr;min-height:100vh;background:var(--surface)}
.admin-side{background:linear-gradient(180deg,#002068,#001842);color:#fff;padding:1.6rem 1.1rem;position:sticky;top:0;height:100vh;overflow-y:auto}
.admin-side .brand{display:flex;align-items:center;gap:.65rem;padding:0 .5rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.12);margin-bottom:1.2rem}
.admin-side .brand .mark{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.1);color:var(--gold);display:grid;place-items:center;font-family:var(--font-display);font-weight:700}
.admin-side .brand b{font-family:var(--font-display);font-size:.98rem;line-height:1.1;display:block}
.admin-side .brand small{font-size:11px;color:rgba(255,255,255,.6)}
.nav-group{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);padding:.4rem .7rem;margin-top:.8rem}
.nav-link{display:flex;align-items:center;gap:.7rem;padding:.62rem .7rem;border-radius:.6rem;color:rgba(255,255,255,.82);font-size:14px;font-weight:500;transition:.15s;margin-bottom:.15rem}
.nav-link:hover{background:rgba(255,255,255,.08);color:#fff}
.nav-link.active{background:var(--gold);color:#002068;font-weight:600}
.nav-link .ic{width:18px;text-align:center}
.admin-main{display:flex;flex-direction:column;min-width:0}
.admin-top{display:flex;align-items:center;justify-content:space-between;padding:1rem 2rem;background:rgba(255,255,255,.8);backdrop-filter:blur(12px);border-bottom:1px solid var(--outline-variant);position:sticky;top:0;z-index:20}
.admin-top h1{font-size:1.25rem;font-family:var(--font-display)}
.admin-user{display:flex;align-items:center;gap:.8rem}
.admin-user .av{width:38px;height:38px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;font-size:14px}
.admin-user b{font-size:13.5px;display:block;line-height:1.1}
.admin-user small{font-size:11.5px;color:var(--on-surface-variant)}
.btn-logout{background:none;border:1px solid var(--outline-variant);color:var(--on-surface-variant);padding:.5rem .9rem;border-radius:.5rem;font-size:13px;cursor:pointer;font-family:var(--font-sans)}
.btn-logout:hover{border-color:var(--error);color:var(--error)}
.admin-content{padding:2rem;flex:1}
.flash{padding:.9rem 1.2rem;border-radius:.7rem;margin-bottom:1.5rem;font-size:.93rem}
.flash-success{background:rgba(0,51,153,.08);border:1px solid var(--primary-container);color:var(--primary)}
.flash-error{background:#fdecec;border:1px solid #f5b5b5;color:#b3261e}
@media(max-width:860px){.admin-shell{grid-template-columns:1fr}.admin-side{position:fixed;z-index:60;width:264px;transform:translateX(-105%);transition:.25s}.admin-side.open{transform:none}}
</style>
@stack('styles')
    <style>[x-cloak]{display:none!important}</style>
</head>
<body>
@php
  $menu = [
    ['group' => 'Utama'],
    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'ic' => '&#128202;'],
    ['group' => 'Konten'],
    ['label' => 'Kegiatan', 'route' => 'admin.kegiatan.index', 'ic' => '&#128197;'],
    ['label' => 'Karya', 'route' => 'admin.karya.index', 'ic' => '&#128221;'],
    ['group' => 'Organisasi'],
    ['label' => 'Anggota', 'route' => 'admin.anggota.index', 'ic' => '&#128100;'],
    ['label' => 'Struktur Organisasi', 'route' => 'admin.biro.index', 'ic' => '&#127970;'],
    ['label' => 'Periode', 'route' => 'admin.periode.index', 'ic' => '&#128198;'],
    ['label' => 'Kepengurusan', 'route' => 'admin.kepengurusan.index', 'ic' => '&#129309;'],
    ['group' => 'Sistem'],
    ['label' => 'Pengaturan', 'route' => 'admin.settings.edit', 'ic' => '&#9881;'],
    ['label' => 'Riwayat Aktivitas', 'route' => 'admin.riwayat.index', 'ic' => '&#128203;'],
    ['label' => 'Sampah Konten', 'route' => 'admin.trash.index', 'ic' => '&#128465;'],
  ];
@endphp
<div class="admin-shell">
  <aside class="admin-side" id="adminSide">
    <div class="brand">
      <span class="mark">P</span>
      <span><b>PMII Saintek</b><small>Panel Admin</small></span>
    </div>
    <nav>
      @foreach ($menu as $item)
        @continue(auth()->user()->role === 'admin_biro' && isset($item['route']) && ! in_array($item['route'], ['admin.dashboard', 'admin.kegiatan.index', 'admin.karya.index', 'admin.anggota.index'], true))
        @if (isset($item['group']))
          <div class="nav-group">{{ $item['group'] }}</div>
        @else
          @php $has = \Illuminate\Support\Facades\Route::has($item['route']); @endphp
          <a class="nav-link @if($has && request()->routeIs($item['route'].'*')) active @endif" href="{{ $has ? route($item['route']) : '#' }}">
            <span class="ic">{!! $item['ic'] !!}</span> {{ $item['label'] }}
          </a>
        @endif
      @endforeach
      @if (auth()->user()?->isSuperAdmin())
        <div class="nav-group">Super Admin</div>
        <a class="nav-link @if(\Illuminate\Support\Facades\Route::has('admin.users.index') && request()->routeIs('admin.users.*')) active @endif" href="{{ \Illuminate\Support\Facades\Route::has('admin.users.index') ? route('admin.users.index') : '#' }}">
          <span class="ic">&#128272;</span> Pengguna
        </a>
      @endif
    </nav>
    @if(auth()->user()->role === 'admin_biro')
      <a class="nav-link" href="{{ route('admin.biro.description.edit', auth()->user()->biro_id) }}">Deskripsi Biro Saya</a>
    @endif
  </aside>

  <div class="admin-main">
    <header class="admin-top">
      <div style="display:flex;align-items:center;gap:1rem">
        <button class="btn-logout" style="display:none" id="sideToggle" onclick="document.getElementById('adminSide').classList.toggle('open')">&#9776;</button>
        <h1>@yield('title', 'Dashboard')</h1>
      </div>
      <div class="admin-user">
        <a class="btn-logout" href="{{ route('password.change') }}">Ganti password</a>
        <span class="av">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <span><b>{{ auth()->user()->name }}</b><small>{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</small></span>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn-logout">Keluar</button>
        </form>
      </div>
    </header>
    <main class="admin-content">
      @if (session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
      @if (session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif
      @yield('content')
    </main>
  </div>
</div>
@stack('scripts')
</body>
</html>
