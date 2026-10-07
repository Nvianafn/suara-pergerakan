@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
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
.av-sm{width:38px;height:38px;border-radius:50%;background:var(--sc-high);display:grid;place-items:center;font-family:var(--font-sans);font-weight:700;color:var(--primary);flex:none;overflow:hidden;font-size:13px}
.av-sm img{width:100%;height:100%;object-fit:cover}
.cell-name b{font-weight:600;display:block}
.row-actions{display:flex;gap:.5rem;justify-content:flex-end}
.btn-sm{padding:.4rem .8rem;font-size:12.5px;border-radius:.45rem;border:1px solid var(--outline-variant);background:#fff;color:var(--on-surface);cursor:pointer;font-family:var(--font-sans);text-decoration:none}
.btn-sm:hover{border-color:var(--primary-container);color:var(--primary)}
.btn-sm.danger:hover{border-color:var(--error);color:var(--error)}
.pill{font-size:11px;padding:.2rem .6rem;border-radius:9999px;font-weight:600;text-transform:capitalize}
.pill-super{background:rgba(0,51,153,.1);color:var(--primary)}
.pill-admin{background:rgba(252,212,0,.15);color:#b27a00}
.empty-cell{text-align:center;padding:3rem;color:var(--on-surface-variant)}
.pager{margin-top:1.5rem}
</style>
@endpush

<div class="toolbar">
  <p>Total {{ $users->total() }} pengguna terdaftar.</p>
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
</div>

<table class="data-table">
  <thead>
    <tr>
      <th>Nama</th>
      <th>Email</th>
      <th>Peran (Role)</th>
      <th>Anggota Terkait</th>
      <th style="text-align:right">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($users as $u)
    <tr>
      <td>
        <div class="cell-name">
          <span class="av-sm">
            @if($u->anggota && $u->anggota->foto)
              <img src="{{ route('media.anggota', $u->anggota->id) }}" alt="">
            @else
              {{ strtoupper(substr($u->name, 0, 2)) }}
            @endif
          </span>
          <span><b>{{ $u->name }}</b></span>
        </div>
      </td>
      <td>{{ $u->email }}</td>
      <td>
        @if($u->role === 'super_admin')
          <span class="pill pill-super">Super Admin</span>
        @else
          <span class="pill pill-admin">Admin</span>
        @endif
      </td>
      <td>
        @if($u->anggota)
          <span>{{ $u->anggota->nama_lengkap }} <small style="display:block;color:var(--on-surface-variant);font-size:11px">{{ $u->anggota->nim }}</small></span>
        @else
          <span style="color:var(--on-surface-variant)">&mdash;</span>
        @endif
      </td>
      <td>
        <div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.users.edit', $u) }}">Edit</a>
          @if ($u->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun pengguna ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-sm danger">Hapus</button>
            </form>
          @endif
        </div>
      </td>
    </tr>
    @empty
    <tr><td colspan="5" class="empty-cell">Belum ada pengguna terdaftar.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="pager">{{ $users->links() }}</div>
@endsection
