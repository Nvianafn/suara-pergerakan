<form method="GET" style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
  <label>Nama <input name="q" value="{{ request('q') }}" maxlength="150"></label>
  <label>Angkatan <input type="number" name="angkatan" value="{{ request('angkatan') }}" min="1900" max="2200"></label>
  <label>Status <select name="status"><option value="">Semua</option>@foreach(['aktif', 'alumni', 'non-aktif'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
  <label>Prodi <input name="prodi" value="{{ request('prodi') }}" maxlength="100"></label>
  <button class="btn btn-primary">Filter</button><a href="{{ route('admin.anggota.index') }}">Reset</a>
</form>
