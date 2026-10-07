<?php

namespace App\Http\Requests;

use App\Models\Karya;
use App\Services\ActivityLog;
use Illuminate\Foundation\Http\FormRequest;

class KaryaRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->role === 'admin_biro' && ($this->input('status') === 'published' || $this->boolean('is_featured'))) {
            ActivityLog::record('akses', 'penolakan', $this->user()->id, 'Publikasi karya oleh admin_biro ditolak.');

            return false;
        }

        return $this->route('karya')
            ? $this->user()->can('update', $this->route('karya'))
            : $this->user()->can('create', Karya::class);
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'biro_id' => ['nullable', 'exists:biro,id'],
            'periode_id' => ['nullable', 'exists:periode,id'],
            'tipe' => ['required', 'in:artikel,esai,puisi,berita'],
            'penulis_tipe' => ['required', 'in:anggota,nama_bebas,anonim,redaksi'],
            'penulis_nama' => ['nullable', 'required_if:penulis_tipe,nama_bebas', 'prohibited_unless:penulis_tipe,nama_bebas', 'string', 'max:150'],
            'anggota_id' => ['nullable', 'required_if:penulis_tipe,anggota', 'prohibited_unless:penulis_tipe,anggota', 'exists:anggota,id'],
            'excerpt' => ['nullable', 'string', 'max:200'],
            'konten' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'judul' => 'judul karya',
            'tipe' => 'tipe karya',
            'konten' => 'isi karya',
            'anggota_id' => 'penulis',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('penulis_tipe')) {
            $this->merge(['penulis_tipe' => $this->filled('anggota_id') ? 'anggota' : 'redaksi']);
        }
    }
}
