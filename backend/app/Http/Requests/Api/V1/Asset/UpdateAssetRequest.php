<?php

namespace App\Http\Requests\Api\V1\Asset;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('asset')) ?? false;
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
            'category' => ['sometimes', 'string', 'min:2', 'max:100'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:100'],
            'model' => ['sometimes', 'nullable', 'string', 'max:100'],
            'serial_number' => [
                'sometimes', 'nullable', 'string', 'max:150',
                Rule::unique('assets', 'serial_number')->ignore($this->route('asset')),
            ],
            'purchase_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'warranty_end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:purchase_date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
