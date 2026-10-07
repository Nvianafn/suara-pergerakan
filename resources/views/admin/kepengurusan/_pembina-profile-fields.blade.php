<label>Nama <input name="nama_lengkap" value="{{ $profil?->nama_lengkap }}" maxlength="150" required></label>
<label>Anggota/alumni (opsional)
  <select name="anggota_id"><option value="">Orang luar / tanpa relasi anggota</option>@foreach($anggotaList as $anggota)<option value="{{ $anggota->id }}" @selected($profil?->anggota_id === $anggota->id)>{{ $anggota->nama_lengkap }}</option>@endforeach</select>
</label>
<label>Keterangan <input name="keterangan" value="{{ $profil?->keterangan }}" maxlength="500"></label>
@if($profil?->foto)<img src="{{ route('media.pembina', $profil) }}" alt="Foto Pembina" width="80">@endif
<label>Foto (JPG/PNG/WebP, maks. 5 MB) <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"></label>
@if($profil)<label><input type="checkbox" name="hapus_foto" value="1"> Hapus foto</label>@endif
<label><input type="checkbox" name="setuju_publikasi" value="1" @checked($profil?->setuju_publikasi)> Izin publikasi foto/keterangan</label>
