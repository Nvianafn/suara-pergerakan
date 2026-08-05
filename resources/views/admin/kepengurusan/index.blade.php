@extends('layouts.admin')

@section('title', 'Kepengurusan')

@push('styles')
<style>
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap}
.periode-picker{display:flex;align-items:center;gap:.6rem}
.periode-picker select{padding:.55rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.periode-picker label{font-size:13px;color:var(--on-surface-variant);font-weight:600}
.data-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;overflow:hidden}
.data-table th{text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--on-surface-variant);padding:.85rem 1rem;background:var(--sc-low);border-bottom:1px solid var(--outline-variant)}
.data-table td{padding:.8rem 1rem;border-bottom:1px solid var(--outline-variant);font-size:.92rem;vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:var(--sc-lowest)}
.grp-row td{background:var(--sc)!important;font-family:var(--font-serif);font-weight:700;color:var(--primary);font-size:13px;text-transform:uppercase;letter-spacing:.05em}
.row-actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-sm{padding:.4rem .8rem;font-size:12.5px;border-radius:.45rem;border:1px solid var(--outline-variant);background:#fff;color:var(--on-surface);cursor:pointer;font-family:var(--font-sans);text-decoration:none}
.btn-sm:hover{border-color:var(--primary-container);color:var(--primary)}
.btn-sm.danger:hover{border-color:var(--error);color:var(--error)}
.lvl{font-size:11px;padding:.2rem .6rem;border-radius:9999px;background:var(--sc);color:var(--primary);font-weight:600}
.empty-cell{text-align:center;padding:3rem;color:var(--on-surface-variant)}
</style>
@endpush

@section('content')
<div class="toolbar">
  <form method="GET" class="periode-picker">
    <label for="periode">Periode:</label>
    <select id="periode" name="periode" onchange="this.form.submit()">
      @foreach ($periodeList as $p)
        <option value="{{ $p->id }}" @selected($selectedId == $p->id)>{{ $p->nama }} @if($p->is_aktif) (aktif) @endif</option>
      @endforeach
    </select>
  </form>
  <a href="{{ route('admin.kepengurusan.create') }}" class="btn btn-primary">+ Tambah Pengurus</a>
</div>

@php $labels = ['bph' => 'Badan Pengurus Harian', 'ketua_biro' => 'Ketua Biro', 'anggota_biro' => 'Anggota Biro']; @endphp

@if ($dataIssues > 0)
<div style="display:flex;gap:.6rem;align-items:center;padding:.85rem 1rem;border-radius:.6rem;border:1px solid #f5c518;background:#fffbe6;color:#5c4b00;font-size:13px;margin-bottom:1.2rem">
  <b>Perhatian:</b>
  <span>Ada {{ $dataIssues }} data kepengurusan yang tidak konsisten (anggota biro tanpa biro, atau level BPH dengan biro terisi). Perbaiki lewat tombol Edit.</span>
</div>
@endif

<table class="data-table">
  <thead>
    <tr><th>Nama</th><th>Jabatan</th><th>Biro</th><th>Urut</th><th style="text-align:right">Aksi</th></tr>
  </thead>
  <tbody>
    @forelse ($pengurus->groupBy('level') as $level => $rows)
    <tr class="grp-row"><td colspan="5">{{ $labels[$level] ?? $level }}</td></tr>
      @foreach ($rows as $k)
      <tr>
        <td><b style="font-weight:600">{{ $k->anggota->nama_lengkap ?? '(anggota terhapus)' }}</b></td>
        <td>{{ $k->jabatan }}</td>
        <td>{{ $k->biro->nama ?? '&mdash;' }}</td>
        <td>{{ $k->urutan }}</td>
        <td>
          <div class="row-actions">
            <a class="btn-sm" href="{{ route('admin.kepengurusan.edit', $k) }}">Edit</a>
            <form method="POST" action="{{ route('admin.kepengurusan.destroy', $k) }}" onsubmit="return confirm('Hapus pengurus ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-sm danger">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      @endforeach
    @empty
    <tr><td colspan="5" class="empty-cell">Belum ada pengurus di periode ini.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
