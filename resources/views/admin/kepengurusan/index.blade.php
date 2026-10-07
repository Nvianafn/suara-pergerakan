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

@if ($dataIssues > 0)
<div style="display:flex;gap:.6rem;align-items:center;padding:.85rem 1rem;border-radius:.6rem;border:1px solid #f5c518;background:#fffbe6;color:#5c4b00;font-size:13px;margin-bottom:1.2rem">
  <b>Perhatian:</b>
  <span>Ada {{ $dataIssues }} data pengurus tanpa struktur/bagian. Perbaiki lewat tombol Edit.</span>
</div>
@endif

@include('admin.kepengurusan._pembina')
<table class="data-table">
  <thead>
    <tr><th>Nama</th><th>Jabatan</th><th>Ketua</th><th>Urut</th><th style="text-align:right">Aksi</th></tr>
  </thead>
  <tbody>
    @if ($pengurus->isEmpty())
      <tr><td colspan="5" class="empty-cell">Belum ada data kepengurusan untuk periode ini.</td></tr>
    @else
      @foreach ($units as $unit)
        @php $rows = $pengurusByUnit[$unit->id] ?? collect(); @endphp
        @if ($rows->count())
        <tr class="grp-row">
          <td colspan="5">
            {{ $rows->first()->biro_nama ?? $unit->nama }}
            &middot; {{ $rows->count() }} pengurus
          </td>
        </tr>
          @foreach ($rows as $k)
          <tr>
            <td><b style="font-weight:600">{{ $k->anggota->nama_lengkap ?? '(anggota terhapus)' }}</b></td>
            <td>{{ $k->jabatan }}</td>
            <td>@if ($k->is_ketua)<span class="lvl">Ketua</span>@else&mdash;@endif</td>
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
        @endif
      @endforeach
    @endif
  </tbody>
</table>
@endsection
