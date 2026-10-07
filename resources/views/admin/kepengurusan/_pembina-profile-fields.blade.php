<div class="field"><label>Nama lengkap Pembina <input name="nama_lengkap" value="{{ old('nama_lengkap', $profil?->nama_lengkap) }}" maxlength="150" required></label></div>
<div class="field"><label>Keterangan (opsional) <textarea name="keterangan" maxlength="500">{{ old('keterangan', $profil?->keterangan) }}</textarea></label><p class="hint">Misalnya latar belakang atau keterangan singkat Pembina.</p></div>
<div class="field">
  @if($profil?->foto)<img src="{{ route('media.pembina', $profil) }}" alt="Foto Pembina" width="80">@endif
  <label>Foto (opsional, JPG/PNG/WebP, maks. 5 MB) <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-image-preview></label>
  @if($profil)<label><input type="checkbox" name="hapus_foto" value="1"> Hapus foto</label>@endif
</div>
<div class="field"><label><input type="checkbox" name="setuju_publikasi" value="1" @checked(session()->hasOldInput() ? old('setuju_publikasi', false) : $profil?->setuju_publikasi)> Sudah mendapat izin publikasi foto dan keterangan</label><p class="hint">Tanpa izin, foto dan keterangan tidak ditampilkan ke publik.</p></div>
<details class="field">
  <summary>Hubungkan ke anggota/alumni (opsional)</summary>
  <p class="hint">Lewati bagian ini untuk Pembina dari luar organisasi. Tidak perlu membuat anggota baru.</p>
  <label>Anggota/alumni <select name="anggota_id"><option value="">Tanpa relasi anggota</option>@foreach($anggotaList as $anggota)<option value="{{ $anggota->id }}" @selected(old('anggota_id', $profil?->anggota_id) == $anggota->id)>{{ $anggota->nama_lengkap }}</option>@endforeach</select></label>
</details>
