<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'super_admin'], true);
    }

    public function rules(): array
    {
        $id = $this->route('anggota')?->id;

        return [
            'nim' => ['required', 'string', 'max:20', Rule::unique('anggota', 'nim')->ignore($id)],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nama_panggilan' => ['nullable', 'string', 'max:50'],
            'angkatan' => ['required', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'fakultas' => ['nullable', 'string', 'max:100'],
            'prodi' => ['nullable', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('anggota', 'email')->ignore($id)],
            'bio' => ['nullable', 'string'],
            'setuju_publikasi' => ['nullable', 'boolean'],
            'status' => ['required', 'in:aktif,alumni,non-aktif'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nim' => 'NIM',
            'nama_lengkap' => 'nama lengkap',
            'angkatan' => 'angkatan',
        ];
    }
}
