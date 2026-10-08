<?php

namespace App\Http\Requests\Api\V1\Asset;

use App\AssetStatus;
use App\Models\Asset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Asset::class) ?? false;
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
            'status' => ['sometimes', Rule::enum(AssetStatus::class)],
            'category' => ['sometimes', 'string', 'max:100'],
            'assigned_user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'sort_by' => ['sometimes', Rule::in(['created_at', 'updated_at', 'asset_code', 'name', 'status'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', Rule::in([20, 50, 100])],
        ];
    }
}
