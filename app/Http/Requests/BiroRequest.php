<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['super_admin', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'is_aktif' => ['nullable', 'boolean'],
            'deskripsi' => ['nullable', 'string'],
            'warna_aksen' => ['nullable', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:99'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'warna_aksen.regex' => 'Warna aksen harus berupa kode hex, mis. #003399.',
        ];
    }
}
