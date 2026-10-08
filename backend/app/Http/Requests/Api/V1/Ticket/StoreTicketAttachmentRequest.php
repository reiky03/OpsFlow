<?php

namespace App\Http\Requests\Api\V1\Ticket;

use App\Models\TicketComment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('comment', $this->route('ticket')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,txt,log'],
            'ticket_comment_id' => ['nullable', 'integer', 'exists:ticket_comments,id'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($validator->errors()->has('ticket_comment_id') || ! $this->filled('ticket_comment_id')) {
                return;
            }

            $belongsToTicket = TicketComment::query()
                ->whereKey($this->integer('ticket_comment_id'))
                ->where('ticket_id', $this->route('ticket')->id)
                ->exists();
            if (! $belongsToTicket) {
                $validator->errors()->add('ticket_comment_id', 'The selected comment does not belong to this ticket.');
            }
        }];
    }
}
