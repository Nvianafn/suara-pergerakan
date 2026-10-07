@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>.setup-guide{padding:1rem;margin-bottom:1rem;border:1px solid var(--outline-variant);border-radius:1rem}.setup-guide a{margin-right:1rem}</style>
@endpush

@push('styles')
<style>
.stat-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1.2rem;margin-bottom:2rem}
.stat-card{padding:1.5rem;border-radius:1rem;background:#fff;border:1px solid var(--outline-variant);display:flex;align-items:center;gap:1rem}
.stat-card .ic{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;font-size:24px;background:var(--sc);flex:none}
.stat-card b{font-family:var(--font-serif);font-size:1.9rem;color:var(--primary);line-height:1;display:block}
.stat-card small{color:var(--on-surface-variant);font-size:13px}
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
.panel{background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.5rem}
.panel h2{font-size:1.05rem;margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center}
.panel h2 a{font-size:12.5px;font-weight:600;color:var(--primary-container)}
.list-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.7rem 0;border-bottom:1px solid var(--outline-variant);font-size:.92rem}
.list-row:last-child{border-bottom:none}
.list-row .meta{font-size:12px;color:var(--on-surface-variant)}
.pill{font-size:11px;padding:.2rem .6rem;border-radius:9999px;font-weight:600}
.pill-pub{background:rgba(0,51,153,.1);color:var(--primary)}
.pill-draft{background:#fdecec;color:#b3261e}
@media(max-width:860px){.stat-cards{grid-template-columns:1fr 1fr}.dash-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@if(auth()->user()->role !== 'admin_biro' && (!\App\Models\Periode::exists() || !\App\Models\Biro::exists() || !\App\Models\Setting::get('nama_rayon')))
<section class="setup-guide">
  <h2>Mulai dari sini</h2>
  <p>Lengkapi settings, biro, periode, anggota, dan pengurus; lalu aktifkan periode. Buat super admin cadangan dan lakukan backup pertama beserta uji pemulihan.</p>
  <a href="{{ route('admin.settings.edit') }}">Settings</a><a href="{{ route('admin.biro.index') }}">Biro</a><a href="{{ route('admin.periode.index') }}">Periode &amp; Pengurus</a><a href="{{ route('admin.anggota.index') }}">Anggota</a>
</section>
@endif
<div class="stat-cards">
  <div class="stat-card"><span class="ic">&#128100;</span><span><b>{{ $stats['anggota'] }}</b><small>Anggota</small></span></div>
  <div class="stat-card"><span class="ic">&#127970;</span><span><b>{{ $stats['biro'] }}</b><small>Biro</small></span></div>
  <div class="stat-card"><span class="ic">&#128197;</span><span><b>{{ $stats['kegiatan'] }}</b><small>Kegiatan</small></span></div>
  <div class="stat-card"><span class="ic">&#128221;</span><span><b>{{ $stats['karya'] }}</b><small>Karya</small></span></div>
</div>

@if ($draftKarya > 0)
<div class="flash flash-error" style="background:#fff7e6;border-color:#f5d78a;color:#8a5a00">
  Ada <b>{{ $draftKarya }}</b> karya berstatus draft yang menunggu untuk dipublikasikan.
</div>
@endif

<div class="dash-grid">
  <div class="panel">
    <h2>Kegiatan Terbaru @if(\Illuminate\Support\Facades\Route::has('admin.kegiatan.index'))<a href="{{ route('admin.kegiatan.index') }}">Lihat semua</a>@endif</h2>
    @forelse ($kegiatanTerbaru as $k)
      <div class="list-row">
        <span><b style="font-weight:600">{{ Str::limit($k->judul, 42) }}</b><div class="meta">{{ optional($k->biro)->nama ?? 'Umum' }}</div></span>
        <span class="meta">{{ $k->tanggal->translatedFormat('d M Y') }}</span>
      </div>
    @empty
      <p style="color:var(--on-surface-variant);font-size:.9rem">Belum ada kegiatan.</p>
    @endforelse
  </div>

  <div class="panel">
    <h2>Karya Terbaru @if(\Illuminate\Support\Facades\Route::has('admin.karya.index'))<a href="{{ route('admin.karya.index') }}">Lihat semua</a>@endif</h2>
    @forelse ($karyaTerbaru as $ky)
      <div class="list-row">
        <span><b style="font-weight:600">{{ Str::limit($ky->judul, 38) }}</b><div class="meta">{{ $ky->penulis() }}</div></span>
        <span class="pill @if($ky->status === 'published') pill-pub @else pill-draft @endif">{{ ucfirst($ky->status) }}</span>
      </div>
    @empty
      <p style="color:var(--on-surface-variant);font-size:.9rem">Belum ada karya.</p>
    @endforelse
  </div>
</div>
@endsection
