@extends('layouts.admin')
@section('content')
<h1>Riwayat Aktivitas</h1>
<table class="data-table">
  <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Subjek</th><th>Ringkasan</th></tr></thead>
  <tbody>
  @forelse($activities as $activity)
    <tr><td>{{ $activity->created_at }}</td><td>{{ $activity->user_name ?? 'Sistem' }}</td><td>{{ $activity->aksi }}</td><td>{{ $activity->subjek_tipe }} #{{ $activity->subjek_id }}</td><td>{{ $activity->ringkasan }}</td></tr>
  @empty
    <tr><td colspan="5">Belum ada riwayat aktivitas.</td></tr>
  @endforelse
  </tbody>
</table>
{{ $activities->links() }}
@endsection
