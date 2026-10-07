@extends('layouts.admin')
@section('title', 'Edit Pembina')
@section('content')
<p>Perubahan profil berlaku pada seluruh periode yang memakai Pembina ini. Pembina tidak wajib terdaftar sebagai anggota.</p>
<form method="POST" enctype="multipart/form-data" action="{{ $periode ? route('admin.pembina.update', [$periode, $profil]) : route('admin.pembina.update-profile', $profil) }}" style="max-width:900px">
  @csrf @method('PUT')
  <section class="card" style="padding:1.5rem;margin-bottom:1.5rem">
    <h2 style="margin-bottom:1rem">Profil Pembina</h2>
    @include('admin.kepengurusan._pembina-profile-fields')
    @if($periode)
    <div class="field"><label for="urutan">Urutan tampil pada {{ $periode->nama }}</label><input id="urutan" type="number" name="urutan" value="{{ old('urutan', $urutan) }}" min="0" max="255" required></div>
    @endif
  </section>
  <div class="form-actions"><button class="btn btn-primary">Simpan perubahan</button> <a class="btn-sm" href="{{ route('admin.kepengurusan.index', array_filter(['periode' => $periode?->id])) }}">Batal</a></div>
</form>
@endsection
