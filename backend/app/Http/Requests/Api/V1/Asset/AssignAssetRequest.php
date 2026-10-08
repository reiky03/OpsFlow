<?php

namespace App\Http\Requests\Api\V1\Asset;

use App\UserStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('assign', $this->route('asset')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('status', UserStatus::Active->value)
                    ->whereNull('deleted_at')),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
