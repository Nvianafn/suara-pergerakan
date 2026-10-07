@extends('layouts.admin')
@section('content')
<h1>Riwayat Aktivitas</h1>
<form method="GET" style="display:flex;flex-wrap:wrap;gap:.75rem;margin:1rem 0">
  <label>Pengguna <select name="user_id"><option value="">Semua</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
  <label>Objek <select name="subjek_tipe"><option value="">Semua</option>@foreach($types as $type)<option @selected(request('subjek_tipe') === $type)>{{ $type }}</option>@endforeach</select></label>
  <label>Dari (WIB) <input type="date" name="from" value="{{ request('from') }}"></label>
  <label>Sampai (WIB) <input type="date" name="to" value="{{ request('to') }}"></label>
  <button class="btn btn-primary">Filter</button><a href="{{ route('admin.riwayat.index') }}">Reset</a>
</form>
<table class="data-table">
  <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Subjek</th><th>Ringkasan</th></tr></thead>
  <tbody>
  @forelse($activities as $activity)
    <tr><td>{{ \Carbon\Carbon::parse($activity->created_at, 'UTC')->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</td><td>{{ $activity->user_name ?? 'Sistem' }}</td><td>{{ $activity->aksi }}</td><td>{{ $activity->subjek_tipe }} #{{ $activity->subjek_id }}</td><td>{{ $activity->ringkasan }}</td></tr>
  @empty
    <tr><td colspan="5">Belum ada riwayat aktivitas.</td></tr>
  @endforelse
  </tbody>
</table>
{{ $activities->links() }}
@endsection
