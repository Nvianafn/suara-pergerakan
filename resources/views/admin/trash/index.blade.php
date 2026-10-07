@extends('layouts.admin')
@section('title', 'Sampah Konten')
@section('content')
<h1>Sampah Konten</h1>
<p>Pemulihan mengembalikan konten sebagai draft. Penghapusan permanen tidak dapat dibatalkan.</p>
@foreach(['karya' => $karya, 'kegiatan' => $kegiatan] as $type => $records)
<h2>{{ ucfirst($type) }}</h2>
<table class="data-table">
  <thead><tr><th>Judul</th><th>Dihapus pada</th><th>Tindakan</th></tr></thead>
  <tbody>
  @forelse($records as $record)
    <tr><td>{{ $record->judul }}</td><td>{{ $record->deleted_at }}</td><td>
      <form method="POST" action="{{ route('admin.trash.restore', [$type, $record->id]) }}">@csrf <button class="btn-sm">Pulihkan sebagai draft</button></form>
      @if(auth()->user()->isSuperAdmin())
      <form method="POST" action="{{ route('admin.trash.purge', [$type, $record->id]) }}" onsubmit="return confirm('Hapus konten dan medianya secara permanen?')">@csrf @method('DELETE')<button class="btn-sm danger">Hapus permanen</button></form>
      @endif
    </td></tr>
  @empty
    <tr><td colspan="3">Sampah kosong.</td></tr>
  @endforelse
  </tbody>
</table>
{{ $records->links() }}
@endforeach
@endsection
