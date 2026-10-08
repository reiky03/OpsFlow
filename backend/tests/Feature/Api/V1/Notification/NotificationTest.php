<?php

namespace Tests\Feature\Api\V1\Notification;

use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_only_read_own_notifications_and_mark_them_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->notify(new TicketEventNotification('Ticket assigned', 'A ticket needs your attention.', ['ticket_id' => 99]));
        $other->notify(new TicketEventNotification('Private update', 'Not visible to the first user.'));
        $notification = $user->notifications()->firstOrFail();
        $otherNotification = $other->notifications()->firstOrFail();

        $this->actingAs($user)->getJson('/api/v1/notifications')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.title', 'Ticket assigned');
        $this->actingAs($user)->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()->assertJsonPath('data.read_at', fn ($value) => $value !== null);
        $this->actingAs($user)->postJson("/api/v1/notifications/{$otherNotification->id}/read")
            ->assertNotFound();
    }

    public function test_user_can_filter_unread_notifications_and_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new TicketEventNotification('Ticket assigned', 'The first update.', ['ticket_id' => 10]));
        $user->notify(new TicketEventNotification('Ticket status changed', 'The second update.', ['ticket_id' => 11]));
        $user->notifications()->latest()->firstOrFail()->markAsRead();

        $this->actingAs($user)->getJson('/api/v1/notifications?unread=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonCount(1, 'data');

        $this->actingAs($user)->postJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($user)->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.unread_count', 0);
    }
}
