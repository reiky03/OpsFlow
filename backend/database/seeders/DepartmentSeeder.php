<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'IT' => 'Information technology operations and support.',
            'Finance' => 'Financial planning and company accounting.',
            'HR' => 'People operations and employee services.',
            'Engineering' => 'Product and platform engineering.',
            'Operations' => 'Day-to-day business operations.',
        ];

        foreach ($departments as $name => $description) {
            Department::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }
    }
}
