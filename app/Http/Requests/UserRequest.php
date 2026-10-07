<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;
        $isCreate = $this->isMethod('post');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', 'in:super_admin,admin,admin_biro'],
            'biro_id' => ['required_if:role,admin_biro', 'nullable', 'exists:biro,id'],
            'is_active' => ['required', 'boolean'],
            'anggota_id' => ['nullable', 'exists:anggota,id'],
            'password' => [$isCreate ? 'required' : 'nullable', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'anggota_id' => 'anggota terkait',
        ];
    }
}
