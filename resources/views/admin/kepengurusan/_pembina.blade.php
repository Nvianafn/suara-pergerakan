<section style="margin:1.5rem 0">
  <h2>Pembina</h2>
  <p>Semua berlabel Pembina. Perubahan profil berlaku pada seluruh periode yang memakai profil ini.</p>
  @if($errors->any())<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
  @if($selectedPeriode)
    @foreach($selectedPeriode->pembina()->orderBy('periode_pembina.urutan')->orderBy('periode_pembina.id')->get() as $profil)
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.pembina.update', [$selectedPeriode, $profil]) }}" style="margin:1rem 0">
      @csrf @method('PUT')
      @include('admin.kepengurusan._pembina-profile-fields', ['profil' => $profil])
      <label>Urutan <input type="number" name="urutan" value="{{ $profil->pivot->urutan }}" min="0" max="255" required></label>
      <button class="btn-sm">Simpan profil dan urutan</button>
    </form>
    <form method="POST" action="{{ route('admin.pembina.destroy', [$selectedPeriode, $profil]) }}">
      @csrf @method('DELETE')<button class="btn-sm danger">Lepaskan dari periode</button>
    </form>
    @endforeach
    <h3>Tempatkan profil pada {{ $selectedPeriode->nama }}</h3>
    <form method="POST" action="{{ route('admin.pembina.store', $selectedPeriode) }}">
      @csrf
      <label>Profil <select name="pembina_id" required><option value="">Pilih profil</option>@foreach($pembinaList as $profil)<option value="{{ $profil->id }}">{{ $profil->nama_lengkap }}</option>@endforeach</select></label>
      <label>Urutan <input type="number" name="urutan" value="0" min="0" max="255" required></label>
      <button class="btn btn-primary">Tempatkan Pembina</button>
    </form>
  @else
    <p>Profil boleh dibuat sekarang. Buat periode terlebih dahulu untuk menempatkan Pembina.</p>
  @endif
  <h3>Tambah Profil Pembina</h3>
  <form method="POST" enctype="multipart/form-data" action="{{ route('admin.pembina.create-profile') }}">
    @csrf
    @include('admin.kepengurusan._pembina-profile-fields', ['profil' => null])
    <button class="btn btn-primary">Tambah Profil Pembina</button>
  </form>
  <h3>Profil tersedia</h3>
  @foreach($pembinaList as $profil)
    <details style="margin:1rem 0">
      <summary>{{ $profil->nama_lengkap }} — {{ $profil->periode_count }} periode</summary>
      <form method="POST" enctype="multipart/form-data" action="{{ route('admin.pembina.update-profile', $profil) }}">
        @csrf @method('PUT')
        @include('admin.kepengurusan._pembina-profile-fields', ['profil' => $profil])
        <button class="btn-sm">Simpan profil</button>
      </form>
      @if($profil->periode_count === 0)
      <form method="POST" action="{{ route('admin.pembina.delete-profile', $profil) }}" onsubmit="return confirm('Hapus profil Pembina yang belum digunakan ini?')">
        @csrf @method('DELETE')<button class="btn-sm danger">Hapus profil tidak terpakai</button>
      </form>
      @endif
    </details>
  @endforeach
</section>
