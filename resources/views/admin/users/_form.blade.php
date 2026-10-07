@push('styles')
<style>
.form-wrap{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start}
.form-main,.form-side{background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.5rem}
.form-side{position:sticky;top:5.5rem}
.form-side h3{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--on-surface-variant);margin-bottom:1rem}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field select{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.field .hint{font-size:12px;color:var(--on-surface-variant);margin-top:.3rem}
.form-actions{display:flex;gap:.7rem;margin-top:1rem}
@media(max-width:860px){.form-wrap{grid-template-columns:1fr}.form-side{position:static}}
</style>
@endpush

@php $u = $user ?? null; @endphp

<div class="form-wrap">
  <div class="form-main">
    <div class="field">
      <label for="name">Nama Lengkap</label>
      <input type="text" id="name" name="name" class="input" value="{{ old('name', $u?->name) }}" placeholder="mis. Ahmad Fauzi" required>
      @error('name')<div class="err">{{ $message }}</div>@enderror
    </div>
    
    <div class="field">
      <label for="email">Alamat Email</label>
      <input type="email" id="email" name="email" class="input" value="{{ old('email', $u?->email) }}" placeholder="mis. fauzi@pmii-saintek.id" required>
      @error('email')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label for="password">Password @if($u)<small style="font-weight:normal;color:var(--on-surface-variant)">(kosongkan jika tidak diubah)</small>@endif</label>
      <input type="password" id="password" name="password" class="input" placeholder="Minimal 8 karakter" @if(!$u) required @endif>
      @error('password')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label for="password_confirmation">Konfirmasi Password</label>
      <input type="password" id="password_confirmation" name="password_confirmation" class="input" placeholder="Ulangi password" @if(!$u) required @endif>
    </div>
  </div>

  <aside class="form-side">
    <h3>Pengaturan Akun</h3>
    <div class="field">
      <label for="is_active">Status akun</label>
      <select id="is_active" name="is_active"><option value="1" @selected(old('is_active', $u?->is_active ?? true))>Aktif</option><option value="0" @selected(! old('is_active', $u?->is_active ?? true))>Nonaktif</option></select>
    </div>
    <div class="field">
      <label for="biro_id">Biro (wajib untuk Admin Biro)</label>
      <select id="biro_id" name="biro_id"><option value="">Tanpa biro</option>@foreach($biroList as $biro)<option value="{{ $biro->id }}" @selected(old('biro_id', $u?->biro_id) == $biro->id)>{{ $biro->nama }}</option>@endforeach</select>
      @error('biro_id')<div class="err">{{ $message }}</div>@enderror
    </div>
    
    <div class="field">
      <label for="role">Peran (Role)</label>
      <select id="role" name="role" class="input" required>
        <option value="admin" {{ old('role', $u?->role) == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="admin_biro" @selected(old('role', $u?->role) == 'admin_biro')>Admin Biro</option>
        <option value="super_admin" {{ old('role', $u?->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
      </select>
      @error('role')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label for="anggota_id">Anggota Terkait <small style="font-weight:normal;color:var(--on-surface-variant)">(opsional)</small></label>
      <select id="anggota_id" name="anggota_id" class="input">
        <option value="">-- Tanpa Anggota terkait --</option>
        @foreach($anggotaList as $a)
          <option value="{{ $a->id }}" {{ old('anggota_id', $u?->anggota_id) == $a->id ? 'selected' : '' }}>
            {{ $a->nama_lengkap }} ({{ $a->nim }})
          </option>
        @endforeach
      </select>
      @error('anggota_id')<div class="err">{{ $message }}</div>@enderror
      <div class="hint">Hubungkan akun ini dengan data anggota PMII jika ada.</div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.users.index') }}" class="btn-sm" style="display:inline-flex;align-items:center">Batal</a>
    </div>
  </aside>
</div>
<p>Password yang ditetapkan admin bersifat sementara; pengguna wajib menggantinya saat login berikutnya.</p>
