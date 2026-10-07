@php use App\Models\Setting; @endphp
<footer class="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <a class="brand" href="{{ route('home') }}">
          <img class="mark" src="{{ asset('images/logo.png') }}" alt="Logo PMII Rayon Saintek" style="background:none;padding:0;border-radius:50%;object-fit:cover">
          <span class="brand-txt"><b>{{ Setting::get('nama_rayon', 'PMII Rayon Saintek') }}</b><span>{{ Setting::get('tagline') ?: 'Bergerak · Berpikir · Berkarya' }}</span></span>
        </a>
        <p>{{ Setting::get('deskripsi_singkat') }}</p>
        <div class="socials">
          @foreach(['instagram' => 'Instagram', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'x' => 'X'] as $network => $label)
          @php($socialUrl = Setting::get('sosmed_'.$network))
          @if($socialUrl && preg_match('~^https?://~i', $socialUrl))
          <a href="{{ $socialUrl }}" aria-label="{{ $label }}" target="_blank" rel="noopener noreferrer nofollow">{{ $label }}</a>
          @endif
          @endforeach
          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', Setting::get('no_wa', '')) }}" aria-label="WhatsApp" target="_blank" rel="noopener">&#128172;</a>
        </div>
      </div>
      <div class="foot-col">
        <h4>Jelajahi</h4>
        <a href="{{ route('tentang') }}">Tentang</a>
        <a href="{{ route('biro.index') }}">Biro</a>
        <a href="{{ route('kegiatan.index') }}">Kegiatan</a>
        <a href="{{ route('karya.index') }}">Karya</a>
      </div>
      <div class="foot-col">
        <h4>Organisasi</h4>
        <a href="{{ route('kepengurusan') }}">Kepengurusan</a>
        <a href="{{ route('biro.index') }}">Struktur Biro</a>
        <a href="{{ route('kontak') }}">Gabung Kami</a>
        <a href="{{ route('kontak') }}">Kontak</a>
      </div>
      <div class="foot-col">
        <h4>Kontak</h4>
        <a href="mailto:{{ Setting::get('email_kontak') }}">{{ Setting::get('email_kontak') }}</a>
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', Setting::get('no_wa', '')) }}">{{ Setting::get('no_wa') }}</a>
        <a href="{{ route('kontak') }}">{{ Setting::get('alamat') }}</a>
      </div>
    </div>
    <div class="copy">
      <span>&copy; {{ date('Y') }} {{ Setting::get('nama_rayon', 'PMII Rayon Saintek') }}. Seluruh hak cipta dilindungi.</span>
      <span>Dibuat dengan &#10084; untuk pergerakan.</span>
    </div>
  </div>
</footer>
