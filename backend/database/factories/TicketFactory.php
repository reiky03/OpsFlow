<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'TCK-'.now()->format('Y').'-'.fake()->unique()->numerify('######'),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'category_id' => TicketCategory::factory(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::Open,
            'reporter_id' => User::factory(),
            'assigned_technician_id' => null,
            'asset_id' => null,
            'assigned_by' => null,
            'assigned_at' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }
}
