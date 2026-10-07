@extends('layouts.admin')

@section('title', 'Kegiatan')

@push('styles')
<style>
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap}
.toolbar p{color:var(--on-surface-variant);font-size:.92rem}
.data-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;overflow:hidden}
.data-table th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--on-surface-variant);padding:.85rem 1rem;background:var(--sc-low);border-bottom:1px solid var(--outline-variant)}
.data-table td{padding:.8rem 1rem;border-bottom:1px solid var(--outline-variant);font-size:.92rem;vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:var(--sc-lowest)}
.cell-title{display:flex;align-items:center;gap:.8rem}
.thumb-sm{width:56px;height:42px;border-radius:.4rem;object-fit:cover;flex:none;background:var(--sc)}
.cell-title b{font-weight:600}
.row-actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-sm{padding:.4rem .8rem;font-size:12.5px;border-radius:.45rem;border:1px solid var(--outline-variant);background:#fff;color:var(--on-surface);cursor:pointer;font-family:var(--font-sans);text-decoration:none}
.btn-sm:hover{border-color:var(--primary-container);color:var(--primary)}
.btn-sm.danger:hover{border-color:var(--error);color:var(--error)}
.pill{font-size:11px;padding:.2rem .6rem;border-radius:9999px;font-weight:600}
.pill-pub{background:rgba(0,51,153,.1);color:var(--primary)}
.pill-draft{background:#fdecec;color:#b3261e}
.empty-cell{text-align:center;padding:3rem;color:var(--on-surface-variant)}
.pager{margin-top:1.5rem}
</style>
@endpush

@section('content')
<div class="toolbar">
  <p>Total {{ $kegiatan->total() }} kegiatan terdata.</p>
  <a href="{{ route('admin.kegiatan.create') }}" class="btn btn-primary">+ Tambah Kegiatan</a>
</div>

<table class="data-table">
  <thead>
    <tr><th>Kegiatan</th><th>Biro</th><th>Tanggal</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
  </thead>
  <tbody>
    @forelse ($kegiatan as $k)
    <tr>
      <td>
        <div class="cell-title">
          <img class="thumb-sm" src="{{ $k->thumbnail ? \App\Services\PublicMedia::url($k->thumbnail) : asset('images/hero.png') }}" alt="">
          <b>{{ Str::limit($k->judul, 48) }}</b>
        </div>
      </td>
      <td>{{ optional($k->biro)->nama ?? '&mdash;' }}</td>
      <td>{{ $k->tanggal->translatedFormat('d M Y') }}</td>
      <td><span class="pill @if($k->status === 'published') pill-pub @else pill-draft @endif">{{ ucfirst($k->status) }}</span></td>
      <td>
        <div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.kegiatan.edit', $k) }}">Edit</a>
          <form method="POST" action="{{ route('admin.kegiatan.destroy', $k) }}" onsubmit="return confirm('Hapus kegiatan ini beserta seluruh fotonya?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-sm danger">Hapus</button>
          </form>
        </div>
      </td>
    </tr>
    @empty
    <tr><td colspan="5" class="empty-cell">Belum ada kegiatan. Klik "Tambah Kegiatan" untuk mulai.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="pager">{{ $kegiatan->links() }}</div>
@endsection
