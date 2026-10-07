@extends('layouts.admin')

@section('title', 'Karya')

@push('styles')
<style>
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap}
.toolbar p{color:var(--on-surface-variant);font-size:.92rem}
.data-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;overflow:hidden}
.data-table th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--on-surface-variant);padding:.85rem 1rem;background:var(--sc-low);border-bottom:1px solid var(--outline-variant)}
.data-table td{padding:.8rem 1rem;border-bottom:1px solid var(--outline-variant);font-size:.92rem;vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:var(--sc-lowest)}
.row-actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-sm{padding:.4rem .8rem;font-size:12.5px;border-radius:.45rem;border:1px solid var(--outline-variant);background:#fff;color:var(--on-surface);cursor:pointer;font-family:var(--font-sans);text-decoration:none}
.btn-sm:hover{border-color:var(--primary-container);color:var(--primary)}
.btn-sm.danger:hover{border-color:var(--error);color:var(--error)}
.pill{font-size:11px;padding:.2rem .6rem;border-radius:9999px;font-weight:600}
.pill-pub{background:rgba(0,51,153,.1);color:var(--primary)}
.pill-draft{background:#fdecec;color:#b3261e}
.tag-tipe{font-size:11px;padding:.2rem .6rem;border-radius:9999px;background:var(--sc);color:var(--primary);font-weight:600}
.empty-cell{text-align:center;padding:3rem;color:var(--on-surface-variant)}
.pager{margin-top:1.5rem}
</style>
@endpush

@section('content')
<div class="toolbar">
  <p>Total {{ $karya->total() }} karya terdata.</p>
  <a href="{{ route('admin.karya.create') }}" class="btn btn-primary">+ Tambah Karya</a>
</div>

<form method="GET" style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
  <label>Tipe <select name="tipe"><option value="">Semua</option>@foreach(['artikel','esai','puisi','berita'] as $type)<option @selected(request('tipe') === $type)>{{ $type }}</option>@endforeach</select></label>
  <label>Status <select name="status"><option value="">Semua</option>@foreach(['draft','published'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
  <label>Penulis <input name="penulis" value="{{ request('penulis') }}"></label>
  @if(auth()->user()->role !== 'admin_biro')<label>Biro <select name="biro_id"><option value="">Semua</option>@foreach($biroList as $biro)<option value="{{ $biro->id }}" @selected(request('biro_id') == $biro->id)>{{ $biro->nama }}</option>@endforeach</select></label>@endif
  <button class="btn btn-primary">Filter</button><a href="{{ route('admin.karya.index') }}">Reset</a>
</form>
@if(auth()->user()->role !== 'admin_biro')
<form id="bulk-karya" method="POST" action="{{ route('admin.karya.bulk') }}" onsubmit="return confirm('Terapkan aksi pada karya yang dipilih?')">@csrf
  <select name="action"><option value="publish">Publish</option><option value="draft">Jadikan draft</option><option value="delete">Pindahkan ke sampah</option></select>
  <button class="btn btn-primary">Terapkan pilihan</button>
</form>
@endif
<table class="data-table">
  <thead>
    <tr><th>Judul</th><th>Penulis</th><th>Tipe</th><th>Status</th><th>Publikasi</th><th style="text-align:right">Aksi</th></tr>
  </thead>
  <tbody>
    @forelse ($karya as $ky)
    <tr>
      <td>@if(auth()->user()->role !== 'admin_biro')<input type="checkbox" name="ids[]" value="{{ $ky->id }}" form="bulk-karya" aria-label="Pilih {{ $ky->judul }}">@endif <b style="font-weight:600">{{ Str::limit($ky->judul, 46) }}</b></td>
      <td>{{ $ky->penulis() }}</td>
      <td><span class="tag-tipe">{{ ucfirst($ky->tipe) }}</span></td>
      <td><span class="pill @if($ky->status === 'published') pill-pub @else pill-draft @endif">{{ ucfirst($ky->status) }}</span></td>
      <td>{{ $ky->published_at?->translatedFormat('d M Y') ?? '&mdash;' }}</td>
      <td>
        <div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.karya.edit', $ky) }}">Edit</a>
          <form method="POST" action="{{ route('admin.karya.destroy', $ky) }}" onsubmit="return confirm('Hapus karya ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-sm danger">Hapus</button>
          </form>
        </div>
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="empty-cell">Belum ada karya. Klik "Tambah Karya" untuk mulai menulis.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="pager">{{ $karya->links() }}</div>
@endsection
