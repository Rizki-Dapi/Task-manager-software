<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:users', Rule::unique('users', 'name')->whereNull('deleted_at')],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['sometimes', 'string', 'min:6'],
            'role_id' => ['sometimes', 'integer', 'exists:roles,id'],
        ];
    }
}
