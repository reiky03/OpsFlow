<?php

namespace Database\Seeders;

use App\Models\Role;
use App\SystemRole;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            SystemRole::Employee->value => ['Employee', 'Creates and tracks personal IT requests.'],
            SystemRole::Technician->value => ['IT Technician', 'Handles assigned IT support work.'],
            SystemRole::ItManager->value => ['IT Manager', 'Coordinates IT operations and technician workload.'],
            SystemRole::Admin->value => ['Administrator', 'Manages users, access, and system configuration.'],
        ];

        foreach ($roles as $name => [$displayName, $description]) {
            Role::query()->updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $displayName,
                    'description' => $description,
                    'is_system' => true,
                ],
            );
        }
    }
}
