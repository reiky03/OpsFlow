<?php

namespace App\Http\Requests\Api\V1\MasterData;

use App\PermissionName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::DepartmentManage->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:100', Rule::unique('departments', 'name')->ignore($this->route('department'))],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
