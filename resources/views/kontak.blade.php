@extends('layouts.app')

@section('title', 'Kontak')

@php use App\Models\Setting; @endphp

@push('styles')
<style>
.kontak-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:3rem;align-items:start}
.form-card{padding:2.2rem;border-radius:1.25rem}
.form-card h2{margin-bottom:.4rem}
.form-card>p{color:var(--on-surface-variant);margin-bottom:1.5rem;font-size:.95rem}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.alert-success{background:rgba(0,51,153,.08);border:1px solid var(--primary-container);color:var(--primary);padding:1rem 1.2rem;border-radius:.7rem;margin-bottom:1.5rem;font-size:.95rem}
.info-side .info-card{padding:1.8rem;border-radius:1rem;margin-bottom:1.2rem}
.info-side .info-card h4{font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:var(--primary);margin-bottom:1.1rem}
.info-row{display:flex;gap:.8rem;align-items:flex-start;margin-bottom:1rem;font-size:.93rem}
.info-row .ic{font-size:1.1rem;opacity:.7}
.info-row b{display:block;color:var(--on-surface)}
.info-row small{color:var(--on-surface-variant)}
.info-row a{color:var(--primary-container)}
.map-embed{height:220px;border-radius:1rem;overflow:hidden;background:linear-gradient(135deg,#dce9ff,#8aa4ff);display:grid;place-items:center;color:var(--primary);font-family:var(--font-display);position:relative}
.map-embed span{position:relative;z-index:2;font-size:2.4rem}
@media(max-width:960px){.kontak-grid{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<section class="page-hero">
  <div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Beranda</a><span>&rsaquo;</span><span>Kontak</span></div>
    <span class="eyebrow">Hubungi Kami</span>
    <h1>Mari Terhubung &amp; Bergerak Bersama</h1>
    <p>Punya pertanyaan, ingin berkolaborasi, atau tertarik bergabung? Sampaikan lewat form di bawah.</p>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="kontak-grid">
      <div class="form-card glass reveal">
        <h2>Kirim Pesan</h2>
        <p>Kami akan membalas pesanmu secepatnya.</p>

        @if (session('success'))
          <div class="alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('kontak.submit') }}">
          @csrf
          <div class="form-row">
            <div class="field">
              <label for="nama">Nama</label>
              <input type="text" id="nama" name="nama" class="input" value="{{ old('nama') }}" required>
              @error('nama')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" class="input" value="{{ old('email') }}" required>
              @error('email')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="field">
            <label for="pesan">Pesan</label>
            <textarea id="pesan" name="pesan" class="input" required>{{ old('pesan') }}</textarea>
            @error('pesan')<div class="err">{{ $message }}</div>@enderror
          </div>
          <button type="submit" class="btn btn-primary">Kirim Pesan &rarr;</button>
        </form>
      </div>

      <div class="info-side reveal">
        <div class="info-card glass">
          <h4>Informasi Kontak</h4>
          <div class="info-row"><span class="ic"><x-icon name="mail"/></span><span><b>Email</b><a href="mailto:{{ Setting::get('email_kontak') }}">{{ Setting::get('email_kontak') }}</a></span></div>
          <div class="info-row"><span class="ic"><x-icon name="chat"/></span><span><b>WhatsApp</b><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', Setting::get('no_wa', '')) }}">{{ Setting::get('no_wa') }}</a></span></div>
          <div class="info-row"><span class="ic"><x-icon name="pin"/></span><span><b>Sekretariat</b><small>{{ Setting::get('alamat') }}</small></span></div>
          @foreach(['instagram' => 'Instagram', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'x' => 'X'] as $network => $label)
          @php($url = Setting::get('sosmed_'.$network))
          @if($url && preg_match('~^https?://~i', $url))
          <div class="info-row"><span class="ic"><x-icon name="globe"/></span><span><b>{{ $label }}</b><a href="{{ $url }}" target="_blank" rel="noopener noreferrer nofollow">{{ $label }}</a></span></div>
          @endif
          @endforeach
        </div>
        @if(Setting::get('peta_url') && str_starts_with(Setting::get('peta_url'), 'https://'))
        <a class="btn btn-primary" href="{{ Setting::get('peta_url') }}" target="_blank" rel="noopener noreferrer nofollow">Buka lokasi di Google Maps</a>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
