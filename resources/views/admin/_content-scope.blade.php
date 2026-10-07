@php
  $restricted = auth()->user()->role === 'admin_biro';
  $periodId = $record?->periode_id ?? \App\Models\Periode::aktif()->value('id');
@endphp
<div class="field">
  <label for="periode_id">Periode</label>
  <select id="periode_id" name="periode_id" @disabled($restricted)>
    <option value="">Tanpa periode</option>
    @foreach(\App\Models\Periode::orderByDesc('id')->get() as $periode)
    <option value="{{ $periode->id }}" @selected((string) old('periode_id', $periodId) === (string) $periode->id)>{{ $periode->nama }}</option>
    @endforeach
  </select>
  @error('periode_id')<div class="err">{{ $message }}</div>@enderror
  @if($restricted)<p>Periode aktif saat membuat; periode arsip dipertahankan saat mengedit.</p>@endif
</div>
@if($withBiro)
<div class="field">
  <label for="biro_id">Biro</label>
  <select id="biro_id" name="biro_id" @disabled($restricted)>
    <option value="">Tingkat rayon</option>
    @foreach(\App\Models\Biro::orderBy('urutan')->get() as $biro)
    <option value="{{ $biro->id }}" @selected((string) old('biro_id', $restricted ? auth()->user()->biro_id : $record?->biro_id) === (string) $biro->id)>{{ $biro->nama }}</option>
    @endforeach
  </select>
</div>
@endif
