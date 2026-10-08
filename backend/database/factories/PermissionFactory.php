<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resource = fake()->unique()->word();

        return [
            'name' => Str::of($resource)->snake()->append('.view')->toString(),
            'description' => fake()->sentence(),
        ];
    }
}
