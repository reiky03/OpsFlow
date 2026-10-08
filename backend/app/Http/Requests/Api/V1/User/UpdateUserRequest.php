<?php

namespace App\Http\Requests\Api\V1\User;

use App\PermissionName;
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
        return $this->user()?->can(PermissionName::UserUpdate->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:150'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'employee_number' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('users', 'employee_number')->ignore($this->route('user'))],
            'department_id' => ['sometimes', 'nullable', 'integer', 'exists:departments,id'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:150'],
        ];
    }
}
