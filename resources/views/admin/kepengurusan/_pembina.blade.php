<section style="margin:1.5rem 0">
  <h2 style="margin-bottom:.5rem">Pembina</h2>
  <p style="margin-bottom:1rem">Profil mandiri, tidak wajib menjadi anggota.</p>
  @if($selectedPeriode)
  <table class="data-table">
    <thead><tr><th>Nama</th><th>Jabatan</th><th>Urutan</th><th style="text-align:right">Aksi</th></tr></thead>
    <tbody>
    @forelse($selectedPeriode->pembina()->orderBy('periode_pembina.urutan')->orderBy('periode_pembina.id')->get() as $profil)
      <tr>
        <td><b>{{ $profil->nama_lengkap }}</b></td><td>Pembina</td><td>{{ $profil->pivot->urutan }}</td>
        <td><div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.pembina.edit', ['pembina' => $profil, 'periode' => $selectedPeriode->id]) }}">Edit</a>
          <form method="POST" action="{{ route('admin.pembina.destroy', [$selectedPeriode, $profil]) }}" onsubmit="return confirm('Lepaskan Pembina dari periode ini? Profil tetap tersimpan.')">
            @csrf @method('DELETE')<button class="btn-sm danger">Lepaskan</button>
          </form>
        </div></td>
      </tr>
    @empty
      <tr><td colspan="4" class="empty-cell">Belum ada Pembina pada periode ini. Klik Tambah Pembina untuk menambahkan.</td></tr>
    @endforelse
    </tbody>
  </table>
  <details class="card" style="padding:1rem;margin:1rem 0">
    <summary>Gunakan profil Pembina yang sudah ada pada {{ $selectedPeriode->nama }}</summary>
    <form method="POST" action="{{ route('admin.pembina.store', $selectedPeriode) }}" style="margin-top:1rem">
      @csrf
      <div class="field"><label>Profil <select name="pembina_id" required><option value="">Pilih profil</option>@foreach($pembinaList as $profil)<option value="{{ $profil->id }}">{{ $profil->nama_lengkap }}</option>@endforeach</select></label></div>
      <div class="field"><label>Urutan <input type="number" name="urutan" value="0" min="0" max="255" required></label></div>
      <button class="btn btn-primary">Tempatkan Pembina</button>
    </form>
  </details>
  @else
    <p>Profil boleh dibuat sekarang. Buat periode terlebih dahulu untuk menempatkan Pembina.</p>
  @endif
  <details style="margin:1rem 0">
    <summary>Semua profil Pembina ({{ $pembinaList->count() }})</summary>
    <table class="data-table" style="margin-top:1rem">
      <thead><tr><th>Nama</th><th>Digunakan pada</th><th style="text-align:right">Aksi</th></tr></thead>
      <tbody>
      @forelse($pembinaList as $profil)
        <tr><td>{{ $profil->nama_lengkap }}</td><td>{{ $profil->periode_count }} periode</td><td><div class="row-actions">
          <a class="btn-sm" href="{{ route('admin.pembina.edit', $profil) }}">Edit</a>
          @if($profil->periode_count === 0)
          <form method="POST" action="{{ route('admin.pembina.delete-profile', $profil) }}" onsubmit="return confirm('Hapus profil Pembina yang belum digunakan ini?')">
            @csrf @method('DELETE')<button class="btn-sm danger">Hapus profil</button>
          </form>
          @endif
        </div></td></tr>
      @empty
        <tr><td colspan="3" class="empty-cell">Belum ada profil Pembina.</td></tr>
      @endforelse
      </tbody>
    </table>
  </details>
</section>
