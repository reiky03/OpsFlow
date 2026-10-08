<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'ticket_number' => $this->ticket_number,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'category' => new TicketCategoryResource($this->whenLoaded('category')),
            'reporter' => $this->whenLoaded('reporter', fn (): array => [
                'id' => $this->reporter->id,
                'name' => $this->reporter->name,
                'email' => $this->reporter->email,
            ]),
            'assigned_technician' => $this->whenLoaded('assignedTechnician', fn (): ?array => $this->assignedTechnician ? [
                'id' => $this->assignedTechnician->id,
                'name' => $this->assignedTechnician->name,
                'email' => $this->assignedTechnician->email,
            ] : null),
            'asset' => new AssetSummaryResource($this->whenLoaded('asset')),
            'comments' => TicketCommentResource::collection($this->whenLoaded('comments')),
            'attachments' => TicketAttachmentResource::collection($this->whenLoaded('attachments')),
            'activities' => TicketActivityResource::collection($this->whenLoaded('activities')),
            'assigned_at' => $this->assigned_at,
            'resolved_at' => $this->resolved_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
