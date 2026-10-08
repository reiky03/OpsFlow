<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Hardware', 'Software', 'Network', 'Account Access', 'Email', 'VPN', 'Printer', 'Application', 'Other'] as $name) {
            TicketCategory::query()->updateOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name, 'description' => "Requests related to {$name}.", 'is_active' => true],
            );
        }
    }
}
