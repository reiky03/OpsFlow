<?php

namespace App\Http\Requests\Api\V1\Ticket;

use App\Models\Ticket;
use App\TicketPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'integer', Rule::exists('ticket_categories', 'id')->where('is_active', true)],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'asset_id' => [
                'nullable',
                'integer',
                Rule::exists('assets', 'id')->where(fn ($query) => $query
                    ->where('assigned_user_id', $this->user()?->id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }
}
