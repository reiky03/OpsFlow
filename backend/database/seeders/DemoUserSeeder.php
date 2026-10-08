<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\SystemRole;
use App\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['OpsFlow Admin', 'admin@opsflow.local', 'EMP-00001', 'IT', 'System Administrator', SystemRole::Admin],
            ['IT Manager', 'manager@opsflow.local', 'EMP-00002', 'IT', 'IT Manager', SystemRole::ItManager],
            ['Andi Technician', 'technician@opsflow.local', 'EMP-00003', 'IT', 'IT Support Technician', SystemRole::Technician],
            ['Reiky Employee', 'employee@opsflow.local', 'EMP-00004', 'Engineering', 'Software Engineer', SystemRole::Employee],
            ['Sinta Technician', 'sinta.technician@opsflow.local', 'EMP-00005', 'IT', 'Network Technician', SystemRole::Technician],
            ['Budi Finance', 'budi.finance@opsflow.local', 'EMP-00006', 'Finance', 'Finance Analyst', SystemRole::Employee],
            ['Maya HR', 'maya.hr@opsflow.local', 'EMP-00007', 'HR', 'HR Specialist', SystemRole::Employee],
            ['Dian Engineer', 'dian.engineer@opsflow.local', 'EMP-00008', 'Engineering', 'QA Engineer', SystemRole::Employee],
            ['Rudi Operations', 'rudi.operations@opsflow.local', 'EMP-00009', 'Operations', 'Operations Officer', SystemRole::Employee],
            ['Nadia Employee', 'nadia.employee@opsflow.local', 'EMP-00010', 'Engineering', 'Product Designer', SystemRole::Employee],
        ];

        foreach ($users as [$name, $email, $employeeNumber, $departmentName, $jobTitle, $roleName]) {
            $department = Department::query()->where('name', $departmentName)->firstOrFail();
            $role = Role::query()->where('name', $roleName->value)->firstOrFail();

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'employee_number' => $employeeNumber,
                    'department_id' => $department->id,
                    'job_title' => $jobTitle,
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ],
            );

            $user->roles()->sync([$role->id]);
        }
    }
}
