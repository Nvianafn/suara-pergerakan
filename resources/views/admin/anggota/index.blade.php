@extends('layouts.admin')

@section('title', 'Anggota')

@push('styles')
<style>
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap}
.toolbar p{color:var(--on-surface-variant);font-size:.92rem}
.data-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;overflow:hidden}
.data-table th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--on-surface-variant);padding:.85rem 1rem;background:var(--sc-low);border-bottom:1px solid var(--outline-variant)}
.data-table td{padding:.8rem 1rem;border-bottom:1px solid var(--outline-variant);font-size:.92rem;vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:var(--sc-lowest)}
.cell-name{display:flex;align-items:center;gap:.7rem}
.av-sm{width:38px;height:38px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-serif);font-weight:700;color:var(--primary);flex:none;overflow:hidden}
.av-sm img{width:100%;height:100%;object-fit:cover}
.cell-name b{font-weight:600;display:block}
.cell-name small{color:var(--on-surface-variant);font-size:12px}
.row-actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-sm{padding:.4rem .8rem;font-size:12.5px;border-radius:.45rem;border:1px solid var(--outline-variant);background:#fff;color:var(--on-surface);cursor:pointer;font-family:var(--font-sans);text-decoration:none}
.btn-sm:hover{border-color:var(--primary-container);color:var(--primary)}
.btn-sm.danger:hover{border-color:var(--error);color:var(--error)}
.pill{font-size:11px;padding:.2rem .6rem;border-radius:9999px;font-weight:600;text-transform:capitalize}
.pill-aktif{background:rgba(0,51,153,.1);color:var(--primary)}
.pill-alumni{background:#eef2f7;color:#5a6b82}
.pill-nonaktif{background:#fdecec;color:#b3261e}
.empty-cell{text-align:center;padding:3rem;color:var(--on-surface-variant)}
.pager{margin-top:1.5rem}
</style>
@endpush

@section('content')
@include('admin.anggota.filters')
<div class="toolbar">
  <p>Total {{ $anggota->total() }} anggota terdata.</p>
  <a href="{{ route('admin.anggota.create') }}" class="btn btn-primary">+ Tambah Anggota</a>
</div>

<table class="data-table">
  <thead>
    <tr><th>Nama</th><th>NIM</th><th>Angkatan</th><th>Prodi</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
  </thead>
  <tbody>
    @forelse ($anggota as $a)
    <tr>
      <td>
        <div class="cell-name">
          <span class="av-sm">@if($a->foto_url)<img src="{{ $a->foto_url }}" alt="">@else {{ $a->initial() }} @endif</span>
          <span><b>{{ $a->nama_lengkap }}</b>@if($a->nama_panggilan)<small>{{ $a->nama_panggilan }}</small>@endif</span>
        </div>
      </td>
      <td>{{ $a->nim }}</td>
      <td>{{ $a->angkatan }}</td>
      <td>{{ $a->prodi ?? '&mdash;' }}</td>
      <td><span class="pill pill-{{ str_replace('-', '', $a->status) }}">{{ $a->status }}</span></td>
      <td>
        <div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.anggota.edit', $a) }}">Edit</a>
          <form method="POST" action="{{ route('admin.anggota.destroy', $a) }}" onsubmit="return confirm('Hapus anggota ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-sm danger">Hapus</button>
          </form>
        </div>
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="empty-cell">Belum ada anggota terdata.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="pager">{{ $anggota->links() }}</div>
@endsection
