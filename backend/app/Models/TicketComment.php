<?php

namespace App\Models;

use Database\Factories\TicketCommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ticket_id', 'user_id', 'message', 'is_internal'])]

class TicketComment extends Model
{
    /** @use HasFactory<TicketCommentFactory> */
    use HasFactory;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    protected function casts(): array
    {
        return ['is_internal' => 'boolean'];
    }
}
