<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use App\Services\ActivityLog;
use App\Services\GalleryLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class KegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->filled('hapus_foto') && $this->user()->role !== 'super_admin') {
            ActivityLog::record('akses', 'penolakan', $this->user()->id, 'Penghapusan permanen foto galeri ditolak.');

            return false;
        }

        return $this->route('kegiatan')
            ? $this->user()->can('update', $this->route('kegiatan'))
            : $this->user()->can('create', Kegiatan::class);
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'periode_id' => ['nullable', 'exists:periode,id'],
            'biro_id' => ['nullable', 'exists:biro,id'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'foto' => ['nullable', 'array', 'max:20'],
            'foto.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'array'],
            'caption.*' => ['nullable', 'string', 'max:255'],
            'existing_caption' => ['nullable', 'array'],
            'existing_caption.*' => ['nullable', 'string', 'max:255'],
            'hapus_foto' => ['nullable', 'array'],
            'hapus_foto.*' => ['integer', 'distinct'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $files = $this->file('foto', []);
            $bytes = collect($files)->sum(fn ($file) => $file->getSize());
            $bytes += $this->file('thumbnail')?->getSize() ?? 0;
            if ($bytes > 40 * 1024 * 1024) {
                $validator->errors()->add('foto', 'Total upload maksimal 40 MB per sekali simpan, termasuk thumbnail.');
            }
            try {
                app(GalleryLimits::class)->validate($this->route('kegiatan'), count($files), $this->input('hapus_foto', []));
            } catch (ValidationException $exception) {
                $validator->errors()->add('foto', $exception->errors()['foto'][0]);
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'judul' => 'judul kegiatan',
            'biro_id' => 'biro',
            'tanggal' => 'tanggal',
        ];
    }
}
