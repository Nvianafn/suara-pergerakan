@push('styles')
<style>
.form-wrap{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start}
.form-main,.form-side{background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.5rem}
.form-side{position:sticky;top:5.5rem}
.form-side h3{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--on-surface-variant);margin-bottom:1rem}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field select,.field textarea{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field textarea{min-height:110px;resize:vertical}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.foto-preview{width:120px;height:120px;border-radius:50%;object-fit:cover;border:2px solid var(--outline-variant);margin-bottom:.6rem;display:block}
.form-actions{display:flex;gap:.7rem;margin-top:1rem}
@media(max-width:860px){.form-wrap{grid-template-columns:1fr}.form-side{position:static}.form-grid-2{grid-template-columns:1fr}}
</style>
@endpush

@php $a = $anggota ?? null; @endphp

<div class="form-wrap">
  <div class="form-main">
    <div class="form-grid-2">
      <div class="field">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" class="input" value="{{ old('nama_lengkap', $a?->nama_lengkap) }}" required>
        @error('nama_lengkap')<div class="err">{{ $message }}</div>@enderror
      </div>
      <div class="field">
        <label for="nama_panggilan">Nama Panggilan</label>
        <input type="text" id="nama_panggilan" name="nama_panggilan" class="input" value="{{ old('nama_panggilan', $a?->nama_panggilan) }}">
      </div>
    </div>
    <div class="form-grid-2">
      <div class="field">
        <label for="nim">NIM</label>
        <input type="text" id="nim" name="nim" class="input" value="{{ old('nim', $a?->nim) }}" required>
        @error('nim')<div class="err">{{ $message }}</div>@enderror
      </div>
      <div class="field">
        <label for="angkatan">Angkatan</label>
        <input type="number" id="angkatan" name="angkatan" class="input" value="{{ old('angkatan', $a?->angkatan) }}" placeholder="2023" required>
        @error('angkatan')<div class="err">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="form-grid-2">
      <div class="field">
        <label for="fakultas">Fakultas</label>
        <input type="text" id="fakultas" name="fakultas" class="input" value="{{ old('fakultas', $a?->fakultas ?? 'Sains dan Teknologi') }}">
      </div>
      <div class="field">
        <label for="prodi">Program Studi</label>
        <input type="text" id="prodi" name="prodi" class="input" value="{{ old('prodi', $a?->prodi) }}">
      </div>
    </div>
    <div class="form-grid-2">
      <div class="field">
        <label for="no_hp">No. HP</label>
        <input type="text" id="no_hp" name="no_hp" class="input" value="{{ old('no_hp', $a?->no_hp) }}">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="input" value="{{ old('email', $a?->email) }}">
        @error('email')<div class="err">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="field">
      <label for="bio">Bio Singkat</label>
      <textarea id="bio" name="bio">{{ old('bio', $a?->bio) }}</textarea>
      <label><input type="checkbox" name="setuju_publikasi" value="1" @checked(old('setuju_publikasi', $a?->setuju_publikasi))> Setuju publikasi foto dan bio</label>
    </div>
  </div>

  <aside class="form-side">
    <h3>Foto & Status</h3>
    <div class="field" style="text-align:center">
      @if ($a && $a->foto)
        <img class="foto-preview" src="{{ route('media.anggota', $a->id) }}" alt="" style="margin-inline:auto">
      @else
        <div class="foto-preview" style="margin-inline:auto;display:grid;place-items:center;background:var(--sc-high);font-family:var(--font-serif);font-size:2.4rem;color:var(--primary)">{{ $a?->initial() ?? '?' }}</div>
      @endif
      <input type="file" name="foto" accept="image/*" class="input">
      @error('foto')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="status">Status Keanggotaan</label>
      <select id="status" name="status">
        @foreach (['aktif' => 'Aktif', 'alumni' => 'Alumni', 'non-aktif' => 'Non-aktif'] as $val => $lbl)
          <option value="{{ $val }}" @selected(old('status', $a?->status) === $val)>{{ $lbl }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.anggota.index') }}" class="btn-sm" style="display:inline-flex;align-items:center">Batal</a>
    </div>
  </aside>
</div>
