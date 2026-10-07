@push('styles')
<style>
.form-narrow{max-width:640px;background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.6rem}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field textarea{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field textarea{min-height:100px;resize:vertical}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.field .hint{font-size:12px;color:var(--on-surface-variant);margin-top:.3rem}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.switch{display:flex;align-items:center;gap:.6rem;padding:.8rem 1rem;border:1px solid var(--outline-variant);border-radius:.6rem;background:var(--sc-lowest)}
.switch input{width:18px;height:18px}
.switch b{font-size:13.5px}
.switch small{display:block;color:var(--on-surface-variant);font-size:12px}
.form-actions{display:flex;gap:.7rem;margin-top:1.2rem}
@media(max-width:560px){.form-grid-2{grid-template-columns:1fr}}
</style>
@endpush

@php $p = $periode ?? null; @endphp

<div class="form-narrow">
  <div class="field">
    <label for="nama">Nama Periode</label>
    <input type="text" id="nama" name="nama" class="input" value="{{ old('nama', $p?->nama) }}" placeholder="mis. 2025/2026" required>
    @error('nama')<div class="err">{{ $message }}</div>@enderror
  </div>
  <div class="form-grid-2">
    <div class="field">
      <label for="tahun_mulai">Tahun Mulai</label>
      <input type="number" id="tahun_mulai" name="tahun_mulai" class="input" value="{{ old('tahun_mulai', $p?->tahun_mulai) }}" required>
      @error('tahun_mulai')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="tahun_selesai">Tahun Selesai</label>
      <input type="number" id="tahun_selesai" name="tahun_selesai" class="input" value="{{ old('tahun_selesai', $p?->tahun_selesai) }}" required>
      @error('tahun_selesai')<div class="err">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="field">
    <label for="tema">Tema Periode</label>
    <input type="text" id="tema" name="tema" class="input" value="{{ old('tema', $p?->tema) }}" placeholder="mis. Menguatkan Nalar, Merawat Pergerakan">
  </div>
  <div class="field">
    <label for="deskripsi">Deskripsi</label>
    <textarea id="deskripsi" name="deskripsi">{{ old('deskripsi', $p?->deskripsi) }}</textarea>
  </div>
  @if(auth()->user()->role === 'super_admin')
  <div class="field">
    <label class="switch">
      <input type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', $p?->is_aktif))>
      <span><b>Jadikan periode aktif</b><small>Periode aktif yang tampil sebagai default di situs. Periode lain akan otomatis dinonaktifkan.</small></span>
    </label>
  </div>
  @endif
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.periode.index') }}" class="btn-sm" style="display:inline-flex;align-items:center">Batal</a>
  </div>
</div>
