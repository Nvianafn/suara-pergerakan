@extends('layouts.admin')

@section('title', 'Periode')

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
.pill-on{background:rgba(0,51,153,.1);color:var(--primary)}
.pill-off{background:#eef2f7;color:#5a6b82}
.tema{color:var(--on-surface-variant);font-size:12.5px;font-style:italic}
.empty-cell{text-align:center;padding:3rem;color:var(--on-surface-variant)}
</style>
@endpush

@section('content')
<div class="toolbar">
  <p>{{ $periode->count() }} periode tercatat. Hanya satu periode yang bisa aktif.</p>
  <a href="{{ route('admin.periode.create') }}" class="btn btn-primary">+ Tambah Periode</a>
</div>

<table class="data-table">
  <thead>
    <tr><th>Periode</th><th>Tahun</th><th>Pengurus</th><th>Status</th><th style="text-align:right">Aksi</th></tr>
  </thead>
  <tbody>
    @forelse ($periode as $p)
    <tr>
      <td><b style="font-weight:600">{{ $p->nama }}</b>@if($p->tema)<div class="tema">&ldquo;{{ Str::limit($p->tema, 60) }}&rdquo;</div>@endif</td>
      <td>{{ $p->tahun_mulai }} &ndash; {{ $p->tahun_selesai }}</td>
      <td>{{ $p->kepengurusan_count }}</td>
      <td>@if($p->is_aktif)<span class="pill pill-on">Aktif</span>@else<span class="pill pill-off">Non-aktif</span>@endif</td>
      <td>
        <div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.periode.edit', $p) }}">Edit</a>
          <a class="btn-sm" href="{{ route('admin.periode.pengurus', $p) }}">Kelola Pengurus</a>
          <form method="POST" action="{{ route('admin.periode.destroy', $p) }}" onsubmit="return confirm('Hapus periode kosong ini? Periode aktif atau yang memiliki relasi tidak dapat dihapus.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-sm danger">Hapus</button>
          </form>
        </div>
      </td>
    </tr>
    @empty
    <tr><td colspan="5" class="empty-cell">Belum ada periode.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
