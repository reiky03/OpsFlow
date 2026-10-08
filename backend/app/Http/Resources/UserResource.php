<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'employee_number' => $this->employee_number,
            'job_title' => $this->job_title,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at,
            'department' => $this->whenLoaded('department', fn (): ?array => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null),
            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles->pluck('name')->values(),
            ),
            'permissions' => $this->whenLoaded(
                'roles',
                fn () => $this->roles
                    ->flatMap(fn ($role) => $role->permissions->pluck('name'))
                    ->unique()
                    ->sort()
                    ->values(),
            ),
        ];
    }
}
