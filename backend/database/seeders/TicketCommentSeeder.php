<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\TicketActivityType;
use Illuminate\Database\Seeder;

class TicketCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technician = User::query()->where('email', 'technician@opsflow.local')->firstOrFail();
        $ticket = Ticket::query()->whereLike('title', '%VPN disconnects%', caseSensitive: false)->firstOrFail();
        $comment = $ticket->comments()->updateOrCreate(
            ['user_id' => $technician->id, 'message' => 'I reviewed the client logs. Please test again after the profile refresh.'],
            ['is_internal' => false],
        );
        $ticket->activities()->updateOrCreate(
            ['type' => TicketActivityType::CommentAdded, 'description' => "Comment added by {$technician->name}."],
            ['actor_id' => $technician->id, 'metadata' => ['comment_id' => $comment->id, 'is_internal' => false]],
        );
        $ticket->comments()->updateOrCreate(
            ['user_id' => $technician->id, 'message' => 'Internal: probable stale VPN profile; monitor authentication logs.'],
            ['is_internal' => true],
        );
    }
}
