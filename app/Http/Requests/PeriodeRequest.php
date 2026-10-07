<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'super_admin'], true)
            && (! $this->boolean('is_aktif') || $this->user()?->role === 'super_admin');
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'tahun_mulai' => ['required', 'integer', 'min:2000', 'max:2100'],
            'tahun_selesai' => ['required', 'integer', 'gte:tahun_mulai', 'max:2100'],
            'tema' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'is_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'nama periode',
            'tahun_mulai' => 'tahun mulai',
            'tahun_selesai' => 'tahun selesai',
        ];
    }
}
