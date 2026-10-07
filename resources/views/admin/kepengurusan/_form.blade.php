@push('styles')
<style>
.form-narrow{max-width:640px;background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.6rem}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field select{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.field .hint{font-size:12px;color:var(--on-surface-variant);margin-top:.3rem}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-actions{display:flex;gap:.7rem;margin-top:1.2rem}
@media(max-width:560px){.form-grid-2{grid-template-columns:1fr}}
</style>
@endpush

@php $k = $kepengurusan ?? null; @endphp

<div class="form-narrow">
  @error('unique_check')<div class="err" style="margin-bottom:1rem">{{ $message }}</div>@enderror

  <div class="field">
    <label for="periode_id">Periode</label>
    <select id="periode_id" name="periode_id" required>
      @foreach ($periodeList as $p)
        <option value="{{ $p->id }}" @selected((string) old('periode_id', $k?->periode_id ?? optional($periodeList->firstWhere('is_aktif', true))->id) === (string) $p->id)>{{ $p->nama }}</option>
      @endforeach
    </select>
    @error('periode_id')<div class="err">{{ $message }}</div>@enderror
  </div>

  <div class="field">
    <label for="anggota_id">Anggota</label>
    <select id="anggota_id" name="anggota_id" required>
      <option value="">&mdash; pilih anggota &mdash;</option>
      @foreach ($anggotaList as $a)
        <option value="{{ $a->id }}" @selected((string) old('anggota_id', $k?->anggota_id) === (string) $a->id)>{{ $a->nama_lengkap }} ({{ $a->angkatan }})</option>
      @endforeach
    </select>
    @error('anggota_id')<div class="err">{{ $message }}</div>@enderror
  </div>

  <div class="form-grid-2">
    <div class="field">
      <label for="biro_id">Struktur / Bagian</label>
      <select id="biro_id" name="biro_id">
        <option value="">&mdash; pilih struktur &mdash;</option>
        <optgroup label="Biro">
          @foreach ($unitList->where('tipe', 'biro') as $u)
            <option value="{{ $u->id }}" @selected((string) old('biro_id', $k?->biro_id) === (string) $u->id)>{{ $u->nama }}</option>
          @endforeach
        </optgroup>
      </select>
      @error('biro_id')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="jabatan">Jabatan</label>
      <input type="text" id="jabatan" name="jabatan" class="input" value="{{ old('jabatan', $k?->jabatan) }}" placeholder="mis. Ketua Rayon / Ketua Biro / Anggota" required>
      @error('jabatan')<div class="err">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="form-grid-2">
    <div class="field">
      <label for="urutan">Urutan Tampil</label>
      <input type="number" id="urutan" name="urutan" class="input" value="{{ old('urutan', $k?->urutan ?? 0) }}">
      <div class="hint">Kecil tampil lebih dulu.</div>
    </div>
    <div class="field">
      <label for="level">Level</label>
      <select id="level" name="level" required>
        @foreach(['bph' => 'BPH', 'ketua_biro' => 'Ketua Biro', 'anggota_biro' => 'Anggota Biro'] as $value => $label)
        <option value="{{ $value }}" @selected(old('level', $k?->level ?? 'bph') === $value)>{{ $label }}</option>
        @endforeach
      </select>
      <div class="hint">BPH tidak terikat biro. Ketua/anggota biro wajib memilih biro.</div>
      @error('level')<div class="err">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.kepengurusan.index') }}" class="btn-sm" style="display:inline-flex;align-items:center;padding:.4rem .8rem;border:1px solid var(--outline-variant);border-radius:.45rem;text-decoration:none;color:var(--on-surface)">Batal</a>
  </div>
</div>
