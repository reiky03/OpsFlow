<?php

namespace App\Http\Requests\Api\V1\Ticket;

use App\Models\User;
use App\SystemRole;
use App\UserStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('assign', $this->route('ticket')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'technician_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('status', UserStatus::Active->value)
                    ->whereNull('deleted_at')),
            ],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($validator->errors()->has('technician_id')) {
                return;
            }

            $isTechnician = User::query()
                ->whereKey($this->integer('technician_id'))
                ->whereHas('roles', fn ($query) => $query->where('name', SystemRole::Technician->value))
                ->exists();

            if (! $isTechnician) {
                $validator->errors()->add('technician_id', 'The selected user is not a technician.');
            }
        }];
    }
}
