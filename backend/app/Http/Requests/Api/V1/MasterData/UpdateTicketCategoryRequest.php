<?php

namespace App\Http\Requests\Api\V1\MasterData;

use App\PermissionName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::TicketCategoryManage->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:100', Rule::unique('ticket_categories', 'name')->ignore($this->route('ticket_category'))],
            'slug' => ['sometimes', 'string', 'max:120', Rule::unique('ticket_categories', 'slug')->ignore($this->route('ticket_category'))],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge(['slug' => str($this->input('name'))->slug()->toString()]);
        }
    }
}
