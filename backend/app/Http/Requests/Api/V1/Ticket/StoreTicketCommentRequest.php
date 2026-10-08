<?php

namespace App\Http\Requests\Api\V1\Ticket;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        if (! $this->user()?->can('comment', $ticket)) {
            return false;
        }

        return ! $this->boolean('is_internal') || $this->user()->can('addInternalNote', $ticket);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:5000'],
            'is_internal' => ['sometimes', 'boolean'],
        ];
    }
}
