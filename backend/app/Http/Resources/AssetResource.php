<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
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
            'asset_code' => $this->asset_code,
            'name' => $this->name,
            'category' => $this->category,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'assigned_user' => $this->whenLoaded('assignedUser', fn (): ?array => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ] : null),
            'purchase_date' => $this->purchase_date?->toDateString(),
            'warranty_end_date' => $this->warranty_end_date?->toDateString(),
            'notes' => $this->notes,
            'assignments' => AssetAssignmentResource::collection($this->whenLoaded('assignments')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
