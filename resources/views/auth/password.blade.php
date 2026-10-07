@extends('layouts.app')
@section('title', 'Password Akun')
@section('content')
<section><div class="wrap" style="max-width:560px">
  <h1>{{ $mode === 'change' ? 'Ganti password' : ($mode === 'forgot' ? 'Lupa password' : 'Reset password') }}</h1>
  @if($mode === 'change' && auth()->user()->must_change_password)<p>Anda wajib mengganti password sebelum menggunakan CMS.</p>@endif
  @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
  @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
  <form method="POST" action="{{ route($mode === 'change' ? 'password.update' : ($mode === 'forgot' ? 'password.email' : 'password.store')) }}">
    @csrf
    @if($mode === 'change')
      @method('PUT')
      <label for="current_password">Password saat ini</label>
      <input class="input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
    @else
      <label for="email">Email akun</label>
      <input class="input" id="email" name="email" type="email" value="{{ old('email', $email ?? '') }}" autocomplete="email" required>
    @endif
    @if($mode === 'reset')<input type="hidden" name="token" value="{{ $token }}">@endif
    @if($mode !== 'forgot')
      <label for="password">Password baru (minimal 8 karakter)</label>
      <input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
      <label for="password_confirmation">Ulangi password baru</label>
      <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
    @endif
    <button class="btn btn-primary" type="submit">{{ $mode === 'forgot' ? 'Kirim tautan reset' : 'Simpan password' }}</button>
  </form>
  @auth<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn">Keluar</button></form>@else<a href="{{ route('login') }}">Kembali ke login</a>@endauth
</div></section>
@endsection
