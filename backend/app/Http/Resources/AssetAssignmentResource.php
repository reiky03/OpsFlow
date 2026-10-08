<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetAssignmentResource extends JsonResource
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
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'assigned_by' => $this->whenLoaded('assigner', fn (): array => [
                'id' => $this->assigner->id,
                'name' => $this->assigner->name,
            ]),
            'returned_by' => $this->whenLoaded('returner', fn (): ?array => $this->returner ? [
                'id' => $this->returner->id,
                'name' => $this->returner->name,
            ] : null),
            'assigned_at' => $this->assigned_at,
            'returned_at' => $this->returned_at,
            'notes' => $this->notes,
        ];
    }
}
