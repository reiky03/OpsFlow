<?php

namespace App\Http\Requests\Api\V1\Asset;

use App\Models\Asset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Asset::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'category' => ['required', 'string', 'min:2', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:150', 'unique:assets,serial_number'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'warranty_end_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
