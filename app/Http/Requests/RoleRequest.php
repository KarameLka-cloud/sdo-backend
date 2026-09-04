<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', Rule::in(UserRole::values())],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('role')) {
            $this->merge([
                'role' => strtoupper((string) $this->input('role')),
            ]);
        }
    }
}
