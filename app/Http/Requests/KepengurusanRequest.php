<?php

namespace App\Http\Requests;

use App\Models\Kepengurusan;
use Illuminate\Foundation\Http\FormRequest;

class KepengurusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('kepengurusan')?->id;

        return [
            'periode_id' => ['required', 'exists:periode,id'],
            'anggota_id' => ['required', 'exists:anggota,id'],
            'biro_id' => ['exists:biro,id', 'required_unless:level,bph', 'prohibited_if:level,bph'],
            'jabatan' => ['required', 'string', 'max:100'],
            'level' => ['required', 'in:bph,ketua_biro,anggota_biro'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:127'],
            'unique_check' => [
                function ($attribute, $value, $fail) use ($id) {
                    $exists = Kepengurusan::where('anggota_id', $this->anggota_id)
                        ->where('periode_id', $this->periode_id)
                        ->where('jabatan', $this->jabatan)
                        ->when($id, fn ($q) => $q->where('id', '!=', $id))
                        ->exists();
                    if ($exists) {
                        $fail('Anggota ini sudah punya jabatan tersebut di periode yang sama.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['unique_check' => true]);
    }

    public function attributes(): array
    {
        return [
            'periode_id' => 'periode',
            'anggota_id' => 'anggota',
            'biro_id' => 'biro',
        ];
    }
}
