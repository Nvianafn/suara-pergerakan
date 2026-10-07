@extends('layouts.admin')
@section('title', 'Tambah Pembina')
@section('content')
<div class="toolbar" style="margin-bottom:1.5rem">
  <p>Pembina memiliki profil sendiri. Tidak perlu terdaftar sebagai anggota dan tidak dibuatkan akun.</p>
</div>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.pembina.create-profile') }}" style="max-width:900px">
  @csrf
  <section class="card" style="padding:1.5rem;margin-bottom:1.5rem">
    <h2 style="margin-bottom:1rem">Profil Pembina</h2>
    @include('admin.kepengurusan._pembina-profile-fields', ['profil' => null])
  </section>
  <section class="card" style="padding:1.5rem;margin-bottom:1.5rem">
    <h2 style="margin-bottom:1rem">Penempatan periode</h2>
    <div class="field"><label for="periode_id">Periode (opsional)</label>
      <select id="periode_id" name="periode_id"><option value="">Simpan profil saja — tempatkan nanti</option>
        @foreach($periodeList as $periode)<option value="{{ $periode->id }}" @selected(old('periode_id', $selectedId) == $periode->id)>{{ $periode->nama }}{{ $periode->is_aktif ? ' (aktif)' : '' }}</option>@endforeach
      </select>
    </div>
    <div class="field"><label for="urutan">Urutan tampil</label><input id="urutan" type="number" name="urutan" value="{{ old('urutan', 0) }}" min="0" max="255"><p class="hint">Angka lebih kecil ditampilkan lebih dahulu dalam bagian Pembina.</p></div>
  </section>
  <div class="form-actions"><button class="btn btn-primary">Simpan Pembina</button> <a class="btn-sm" href="{{ route('admin.kepengurusan.index', array_filter(['periode' => $selectedId])) }}">Batal</a></div>
</form>
@endsection
