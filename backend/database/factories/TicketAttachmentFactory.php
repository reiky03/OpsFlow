<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAttachment>
 */
class TicketAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'ticket_comment_id' => null,
            'uploaded_by' => User::factory(),
            'disk' => 'local',
            'path' => 'ticket-attachments/'.fake()->uuid().'.pdf',
            'stored_name' => fake()->uuid().'.pdf',
            'original_name' => 'evidence.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 1_000_000),
        ];
    }
}
