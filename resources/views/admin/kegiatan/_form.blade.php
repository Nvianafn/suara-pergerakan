@push('styles')
<style>
.form-wrap{display:grid;grid-template-columns:1fr 330px;gap:1.5rem;align-items:start}
.form-main,.form-side{background:#fff;border:1px solid var(--outline-variant);border-radius:1rem;padding:1.5rem}
.form-side{position:sticky;top:5.5rem}
.form-side h3{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--on-surface-variant);margin-bottom:1rem}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--on-surface);margin-bottom:.4rem}
.field .input,.field select,.field textarea{width:100%;padding:.6rem .8rem;border:1px solid var(--outline-variant);border-radius:.5rem;font-family:var(--font-sans);font-size:14px;background:#fff;color:var(--on-surface)}
.field textarea{min-height:160px;resize:vertical;line-height:1.6}
.field .hint{font-size:12px;color:var(--on-surface-variant);margin-top:.3rem}
.field .err{color:var(--error);font-size:12.5px;margin-top:.3rem}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.img-preview{width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:.6rem;margin-bottom:.6rem;border:1px solid var(--outline-variant);background:var(--sc)}
.foto-item{display:flex;gap:.7rem;align-items:center;margin-bottom:.7rem;padding:.5rem;border:1px solid var(--outline-variant);border-radius:.5rem}
.foto-item img{width:70px;height:52px;object-fit:cover;border-radius:.35rem;flex:none}
.foto-item label{font-size:12.5px;color:var(--error);display:flex;align-items:center;gap:.3rem;cursor:pointer}
.form-actions{display:flex;gap:.7rem;margin-top:1rem}
@media(max-width:860px){.form-wrap{grid-template-columns:1fr}.form-side{position:static}}
</style>
@endpush

@php $k = $kegiatan ?? null; @endphp

<div class="form-wrap">
  <div class="form-main">
    <div class="field">
      <label for="judul">Judul Kegiatan</label>
      <input type="text" id="judul" name="judul" class="input" value="{{ old('judul', $k?->judul) }}" required>
      @error('judul')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label for="deskripsi">Deskripsi</label>
      <textarea id="deskripsi" name="deskripsi" data-rich-text placeholder="Ceritakan jalannya kegiatan...">{{ old('deskripsi', $k?->deskripsi) }}</textarea>
      @error('deskripsi')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label>Foto Dokumentasi @if($k)(tambahan)@endif</label>
      <input id="galeri-upload" type="file" name="foto[]" accept="image/jpeg,image/png,image/webp" multiple class="input" data-gallery-upload>
      <div data-gallery-captions aria-live="polite"></div>
      <div class="hint">JPG, PNG, atau WebP. Maksimal 20 foto galeri termasuk yang tersimpan, 5 MB per file, dan 40 MB total upload termasuk thumbnail.</div>
      @error('foto')<div class="err">{{ $message }}</div>@enderror
      @error('foto.*')<div class="err">{{ $message }}</div>@enderror
      @error('caption.*')<div class="err">{{ $message }}</div>@enderror
    </div>

    @if ($k && $k->foto->count())
    <div class="field">
      <label>Foto Tersimpan</label>
      @foreach ($k->foto as $f)
      <div class="foto-item">
        <img src="{{ Str::startsWith($f->path, 'seed/') ? asset('images/hero.png') : \App\Services\PublicMedia::url($f->path) }}" alt="">
        <span style="flex:1;font-size:13px;color:var(--on-surface-variant)">{{ $f->caption ?? 'Tanpa keterangan' }}</span>
        @if(auth()->user()->isSuperAdmin())<label><input type="checkbox" name="hapus_foto[]" value="{{ $f->id }}"> Hapus permanen</label>@endif
      </div>
      @endforeach
    </div>
    @endif
  </div>

  <aside class="form-side">
    <h3>Publikasi</h3>
    <div class="field">
      <label for="status">Status</label>
      @include('admin._content-scope', ['record' => $k, 'withBiro' => false])
      <select id="status" name="status">
        <option value="draft" @selected(old('status', $k?->status) === 'draft')>Draft</option>
        <option value="published" @selected(old('status', $k?->status) === 'published')>Published</option>
      </select>
    </div>
    <div class="field">
      <label for="tanggal">Tanggal</label>
      <input type="date" id="tanggal" name="tanggal" class="input" value="{{ old('tanggal', $k?->tanggal?->format('Y-m-d')) }}" required>
      @error('tanggal')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label for="biro_id">Biro Penyelenggara</label>
      <select id="biro_id" name="biro_id" @disabled(auth()->user()->role === 'admin_biro')>
        <option value="">&mdash; Umum &mdash;</option>
        @foreach ($biroList as $b)
          <option value="{{ $b->id }}" @selected((string) old('biro_id', auth()->user()->role === 'admin_biro' ? auth()->user()->biro_id : $k?->biro_id) === (string) $b->id)>{{ $b->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label for="lokasi">Lokasi</label>
      <input type="text" id="lokasi" name="lokasi" class="input" value="{{ old('lokasi', $k?->lokasi) }}" placeholder="mis. Aula FST">
    </div>
    <div class="field">
      <label>Thumbnail</label>
      @if ($k && $k->thumbnail)<img class="img-preview" src="{{ \App\Services\PublicMedia::url($k->thumbnail) }}" alt="">@endif
      <input type="file" name="thumbnail" accept="image/*" class="input">
      @error('thumbnail')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.kegiatan.index') }}" class="btn-sm" style="display:inline-flex;align-items:center">Batal</a>
    </div>
  </aside>
</div>
