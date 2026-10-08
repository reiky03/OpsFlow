<?php

namespace App\Http\Requests\Api\V1\Audit;

use App\AuditAction;
use App\PermissionName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAuditLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionName::AuditView->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['sometimes', Rule::enum(AuditAction::class)],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'entity_type' => ['sometimes', Rule::in(['Asset', 'Ticket', 'User'])],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', Rule::in([20, 50, 100])],
        ];
    }
}
