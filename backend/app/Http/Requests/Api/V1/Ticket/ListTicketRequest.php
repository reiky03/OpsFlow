<?php

namespace App\Http\Requests\Api\V1\Ticket;

use App\Models\Ticket;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Ticket::class) ?? false;
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
            'status' => ['sometimes', Rule::enum(TicketStatus::class)],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'category_id' => ['sometimes', 'integer', 'exists:ticket_categories,id'],
            'technician_id' => ['sometimes', 'integer', 'exists:users,id'],
            'reporter_id' => ['sometimes', 'integer', 'exists:users,id'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'sort_by' => ['sometimes', Rule::in(['created_at', 'updated_at', 'priority', 'status'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', Rule::in([20, 50, 100])],
        ];
    }
}
