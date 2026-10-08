<?php

namespace App\Http\Requests\Api\V1\User;

use App\PermissionName;
use App\UserStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::UserView->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:150'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'role' => ['sometimes', 'string', 'exists:roles,name'],
            'per_page' => ['sometimes', 'integer', Rule::in([20, 50, 100])],
        ];
    }
}
