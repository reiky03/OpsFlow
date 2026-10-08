<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\TicketActivityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketActivity>
 */
class TicketActivityFactory extends Factory
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
            'actor_id' => User::factory(),
            'type' => TicketActivityType::Updated,
            'description' => fake()->sentence(),
            'old_values' => null,
            'new_values' => null,
            'metadata' => null,
        ];
    }
}
