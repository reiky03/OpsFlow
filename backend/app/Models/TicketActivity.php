<?php

namespace App\Models;

use App\TicketActivityType;
use Database\Factories\TicketActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'actor_id', 'type', 'description', 'old_values', 'new_values', 'metadata'])]

class TicketActivity extends Model
{
    /** @use HasFactory<TicketActivityFactory> */
    use HasFactory;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return [
            'type' => TicketActivityType::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }
}
