<?php

namespace Database\Factories;

use App\AssetStatus;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_code' => 'AST-LAP-'.fake()->unique()->numerify('#####'),
            'name' => fake()->randomElement(['Developer Laptop', 'Office Monitor', 'Workstation']),
            'category' => fake()->randomElement(['Laptop', 'Monitor', 'Desktop PC']),
            'brand' => fake()->randomElement(['Dell', 'Lenovo', 'HP', 'Apple']),
            'model' => fake()->bothify('Model-###??'),
            'serial_number' => fake()->unique()->bothify('SN-########'),
            'status' => AssetStatus::Available,
            'assigned_user_id' => null,
            'purchase_date' => fake()->dateTimeBetween('-4 years', '-1 month'),
            'warranty_end_date' => fake()->dateTimeBetween('+1 month', '+3 years'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
