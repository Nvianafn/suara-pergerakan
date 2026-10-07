@push('styles')
<style>
.form-wrap{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start}
.form-main,.form-side{background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.5rem}
.form-side{position:sticky;top:5.5rem}
.form-side h3{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--on-surface-variant);margin-bottom:1rem}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field textarea{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field textarea{min-height:150px;resize:vertical;line-height:1.6}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.field .hint{font-size:12px;color:var(--on-surface-variant);margin-top:.3rem}
.color-row{display:flex;gap:.6rem;align-items:center}
.color-row input[type=color]{width:48px;height:42px;padding:2px;border:1px solid var(--outline-variant);border-radius:.5rem;background:#fff;cursor:pointer}
.logo-preview{width:80px;height:80px;object-fit:contain;border-radius:.6rem;border:1px solid var(--outline-variant);background:var(--sc);margin-bottom:.6rem}
.form-actions{display:flex;gap:.7rem;margin-top:1rem}
@media(max-width:860px){.form-wrap{grid-template-columns:1fr}.form-side{position:static}}
</style>
@endpush

@php $b = $biro ?? null; @endphp

<div class="form-wrap">
  <div class="form-main">
    <div class="field">
      <label><input type="hidden" name="is_aktif" value="0"><input type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', ($biro ?? null)?->is_aktif ?? true))> Biro aktif</label>
      <label for="nama">Nama Biro</label>
      <input type="text" id="nama" name="nama" class="input" value="{{ old('nama', $b?->nama) }}" placeholder="mis. Biro Keilmuan" required>
      @error('nama')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="deskripsi" placeholder="Peran dan fokus gerak biro ini...">{{ old('deskripsi', $b?->deskripsi) }}</textarea>
      @error('deskripsi')<div class="err">{{ $message }}</div>@enderror
    </div>
  </div>

  <aside class="form-side">
    <h3>Tampilan</h3>
    <div class="field">
      <label for="warna_aksen">Warna Aksen</label>
      <div class="color-row">
        <input type="color" value="{{ old('warna_aksen', $b?->warna_aksen ?? '#003399') }}" oninput="document.getElementById('warna_aksen').value=this.value">
        <input type="text" id="warna_aksen" name="warna_aksen" class="input" value="{{ old('warna_aksen', $b?->warna_aksen ?? '#003399') }}">
      </div>
      @error('warna_aksen')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="urutan">Urutan Tampil</label>
      <input type="number" id="urutan" name="urutan" class="input" value="{{ old('urutan', $b?->urutan) }}" placeholder="otomatis">
      <div class="hint">Kosongkan untuk otomatis di urutan terakhir.</div>
    </div>
    <div class="field">
      <label>Logo (opsional)</label>
      @if ($b && $b->logo)<img class="logo-preview" src="{{ \App\Services\PublicMedia::url($b->logo) }}" alt="">@endif
      <input type="file" name="logo" accept="image/*" class="input">
      @error('logo')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.biro.index') }}" class="btn-sm" style="display:inline-flex;align-items:center">Batal</a>
    </div>
  </aside>
</div>
