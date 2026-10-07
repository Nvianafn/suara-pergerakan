@extends('layouts.admin')

@section('title', 'Pengaturan Situs')

@push('styles')
<style>
.settings-wrap{max-width:720px}
.card{background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.6rem;margin-bottom:1.4rem}
.card h3{font-family:var(--font-serif);font-size:1.05rem;color:var(--primary);margin-bottom:.3rem}
.card .sub{font-size:12.5px;color:var(--on-surface-variant);margin-bottom:1.2rem}
.field{margin-bottom:1.1rem}
.field:last-child{margin-bottom:0}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field textarea{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field textarea{min-height:90px;resize:vertical}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.save-bar{position:sticky;bottom:0;background:var(--surface);padding:1rem 0;display:flex;gap:.7rem}
@media(max-width:560px){.form-grid-2{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="settings-wrap" enctype="multipart/form-data">
  @csrf @method('PUT')
  <nav aria-label="Bagian pengaturan" class="settings-tabs" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
    <a href="#settings-general">Umum</a>
    <a href="#settings-about">Halaman Tentang</a>
    <a href="#settings-contact">Kontak &amp; Sosmed</a>
    <a href="#settings-appearance">Tampilan &amp; SEO</a>
  </nav>

  <div class="card" id="settings-general">
    <h3>Identitas Rayon</h3>
    <p class="sub">Nama dan deskripsi yang tampil di seluruh situs.</p>
    <div class="field">
      <label for="nama_rayon">Nama Rayon</label>
      <input type="text" id="nama_rayon" name="nama_rayon" class="input" value="{{ old('nama_rayon', $settings['nama_rayon']) }}" required>
      @error('nama_rayon')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="deskripsi_singkat">Deskripsi Singkat</label>
      <textarea id="deskripsi_singkat" name="deskripsi_singkat">{{ old('deskripsi_singkat', $settings['deskripsi_singkat']) }}</textarea>
      @error('deskripsi_singkat')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="tagline">Tagline</label>
      <input id="tagline" name="tagline" class="input" maxlength="200" value="{{ old('tagline', $settings['tagline']) }}">
      @error('tagline')<div class="err">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="card">
    <h3 id="settings-about">Halaman Tentang</h3>
    @foreach(['tentang_deskripsi' => 'Deskripsi organisasi', 'tentang_sejarah' => 'Sejarah', 'visi' => 'Visi', 'misi' => 'Misi'] as $key => $label)
    <div class="field">
      <label for="{{ $key }}">{{ $label }}</label>
      <textarea id="{{ $key }}" name="{{ $key }}" @if($key !== 'visi') data-rich-text @endif>{{ old($key, $settings[$key]) }}</textarea>
      @error($key)<div class="err">{{ $message }}</div>@enderror
    </div>
    @endforeach
  </div>

  <div class="card">
    <h3 id="settings-contact">Kontak</h3>
    <p class="sub">Digunakan di halaman Kontak dan footer.</p>
    <div class="form-grid-2">
      <div class="field">
        <label for="email_kontak">Email</label>
        <input type="email" id="email_kontak" name="email_kontak" class="input" value="{{ old('email_kontak', $settings['email_kontak']) }}">
        @error('email_kontak')<div class="err">{{ $message }}</div>@enderror
      </div>
      <div class="field">
        <label for="no_wa">No. WhatsApp</label>
        <input type="text" id="no_wa" name="no_wa" class="input" value="{{ old('no_wa', $settings['no_wa']) }}">
      </div>
    </div>
    <div class="field">
      <label for="alamat">Alamat Sekretariat</label>
      <input type="text" id="alamat" name="alamat" class="input" value="{{ old('alamat', $settings['alamat']) }}">
    </div>
    <div class="field">
      <label for="peta_url">Tautan Google Maps (tanpa iframe)</label>
      <input type="url" id="peta_url" name="peta_url" class="input" value="{{ old('peta_url', $settings['peta_url']) }}">
      @error('peta_url')<div class="err">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="card">
    <h3>Media Sosial</h3>
    <p class="sub">Tautan lengkap (mis. https://instagram.com/...). Kosongkan jika tidak dipakai.</p>
    @foreach(['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'x' => 'X'] as $network => $label)
    @php($key = 'sosmed_'.$network)
    <div class="field">
      <label for="{{ $key }}">{{ $label }}</label>
      <input type="url" id="{{ $key }}" name="{{ $key }}" class="input" value="{{ old($key, $settings[$key]) }}">
      @error($key)<div class="err">{{ $message }}</div>@enderror
    </div>
    @endforeach
  </div>

    <div class="card" id="settings-appearance">
      <h3>Tampilan &amp; SEO</h3>
      <p class="sub">JPG, PNG, atau WebP; maksimal 5 MB per file. Gambar diproses ulang menjadi WebP. Kosongkan untuk mempertahankan gambar saat ini.</p>
      @foreach(['logo' => 'Logo', 'favicon' => 'Favicon', 'hero_image' => 'Gambar Hero', 'og_image' => 'Gambar Social Media (OG)'] as $key => $label)
      <div class="field">
        <label for="{{ $key }}">{{ $label }}</label>
        @if($settings[$key])<img src="{{ \App\Models\Setting::imageUrl($key, 'og-image.png') }}" alt="Preview {{ $label }}" style="max-width:100%;max-height:180px;object-fit:contain">@endif
        <input type="file" id="{{ $key }}" name="{{ $key }}" accept="image/jpeg,image/png,image/webp" class="input">
        @error($key)<div class="err">{{ $message }}</div>@enderror
      </div>
      @endforeach
    </div>
  <div class="save-bar">
    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
  </div>
</form>
@endsection
