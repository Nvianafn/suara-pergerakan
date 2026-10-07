@extends('layouts.admin')
@section('title', 'Deskripsi Biro')
@section('content')
<h1>Deskripsi {{ $biro->nama }}</h1>
<form method="POST" action="{{ route('admin.biro.description.update', $biro) }}">
  @csrf @method('PUT')
  <label for="deskripsi">Deskripsi biro</label>
  <textarea id="deskripsi" name="deskripsi" rows="10" maxlength="10000">{{ old('deskripsi', $biro->deskripsi) }}</textarea>
  @error('deskripsi')<p>{{ $message }}</p>@enderror
  <button type="submit" class="btn btn-primary">Simpan deskripsi</button>
</form>
@endsection
