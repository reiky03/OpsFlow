<?php

namespace App\Http\Requests\Api\V1\Ticket;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('ticket')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:5', 'max:150'],
            'description' => ['sometimes', 'string', 'min:10'],
            'category_id' => ['sometimes', 'integer', Rule::exists('ticket_categories', 'id')->where('is_active', true)],
            'asset_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('assets', 'id')->where(fn ($query) => $query
                    ->where('assigned_user_id', $this->user()?->id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }
}
