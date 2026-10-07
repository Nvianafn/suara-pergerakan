<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk &middot; Admin PMII Rayon Saintek</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@vite(['resources/css/app.css'])
<style>
.auth-body{min-height:100vh;display:grid;place-items:center;padding:1.5rem;background:linear-gradient(130deg,#001235,#002068 55%,#003399);font-family:var(--font-sans)}
.auth-card{width:100%;max-width:420px;background:rgba(255,255,255,.96);backdrop-filter:blur(20px);border-radius:1.5rem;padding:2.6rem;box-shadow:0 40px 90px -30px rgba(0,0,0,.55)}
.auth-brand{display:flex;align-items:center;gap:.7rem;margin-bottom:1.8rem}
.auth-brand .mark{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#002068,#003399);color:var(--gold);display:grid;place-items:center;font-family:var(--font-display);font-weight:700;font-size:1.4rem}
.auth-brand b{font-family:var(--font-display);font-size:1.1rem;color:var(--primary);display:block;line-height:1.1}
.auth-brand small{color:var(--on-surface-variant);font-size:12px}
.auth-card h1{font-size:1.5rem;margin-bottom:.3rem}
.auth-card .sub{color:var(--on-surface-variant);font-size:.92rem;margin-bottom:1.6rem}
.auth-error{background:#fdecec;border:1px solid #f5b5b5;color:#b3261e;padding:.7rem 1rem;border-radius:.6rem;font-size:13.5px;margin-bottom:1.2rem}
.remember{display:flex;align-items:center;gap:.5rem;font-size:13.5px;color:var(--on-surface-variant);margin:.2rem 0 1.4rem}
.auth-card .btn{width:100%;justify-content:center}
.auth-foot{text-align:center;margin-top:1.4rem;font-size:13px}
.auth-foot a{color:var(--primary-container)}
</style>
</head>
<body class="auth-body">
<div class="auth-card">
  <div class="auth-brand">
    <span class="mark">P</span>
    <span><b>PMII Rayon Saintek</b><small>Panel Administrasi</small></span>
  </div>
  <h1>Selamat datang</h1>
  <p class="sub">Masuk untuk mengelola konten website.</p>

  @if ($errors->any())
    <div class="auth-error">{{ $errors->first() }}</div>
  @endif
  @if(session('status'))<p role="status">{{ session('status') }}</p>@endif

  <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" class="input" value="{{ old('email') }}" required autofocus>
    </div>
    <div class="field">
      <label for="password">Kata Sandi</label>
      <input type="password" id="password" name="password" class="input" required>
    </div>
    <label class="remember"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
    <button type="submit" class="btn btn-primary">Masuk &rarr;</button>
  </form>

   <div class="auth-foot"><a href="{{ route('password.request') }}">Lupa password?</a> · <a href="{{ route('home') }}">&larr; Kembali ke situs</a></div>
</div>
</body>
</html>
