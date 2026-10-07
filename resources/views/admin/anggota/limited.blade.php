@extends('layouts.admin')
@section('title', 'Anggota')
@section('content')
<h1>Anggota</h1>
<table class="data-table">
  <thead><tr><th>Nama</th><th>Angkatan</th><th>Status</th></tr></thead>
  <tbody>@foreach($anggota as $a)<tr><td>{{ $a->nama_lengkap }}</td><td>{{ $a->angkatan }}</td><td>{{ $a->status }}</td></tr>@endforeach</tbody>
</table>
{{ $anggota->links() }}
@endsection
