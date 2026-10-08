<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\PermissionName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionName::cases() as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission->value],
                ['description' => Str::of($permission->value)->replace(['.', '_'], ' ')->title()->toString()],
            );
        }
    }
}
