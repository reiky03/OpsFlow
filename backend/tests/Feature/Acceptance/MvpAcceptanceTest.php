<?php

namespace Tests\Feature\Acceptance;

use App\Models\AssetAssignment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TicketCategory;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MvpAcceptanceTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_scenario_one_ticket_flows_from_employee_report_to_employee_closure(): void
    {
        $employee = $this->userWithPermissions(SystemRole::Employee, [
            PermissionName::TicketCreate,
            PermissionName::TicketViewOwn,
            PermissionName::TicketComment,
        ]);
        $manager = $this->userWithPermissions(SystemRole::ItManager, [
            PermissionName::TicketViewAll,
            PermissionName::TicketAssign,
        ]);
        $technician = $this->userWithPermissions(SystemRole::Technician, [
            PermissionName::TicketViewAssigned,
            PermissionName::TicketUpdateStatus,
            PermissionName::TicketComment,
        ]);
        $category = TicketCategory::factory()->create();

        $created = $this->actingAs($employee)->postJson('/api/v1/tickets', [
            'title' => 'VPN fails during remote work',
            'description' => 'The VPN disconnects repeatedly after authentication.',
            'category_id' => $category->id,
        ])->assertCreated()->assertJsonPath('data.status', 'OPEN');
        $ticketId = $created->json('data.id');

        $this->actingAs($manager)
            ->postJson("/api/v1/tickets/{$ticketId}/assign", ['technician_id' => $technician->id])
            ->assertOk()->assertJsonPath('data.status', 'ASSIGNED');
        $this->actingAs($technician)
            ->postJson("/api/v1/tickets/{$ticketId}/status", ['status' => 'IN_PROGRESS'])
            ->assertOk();
        $this->actingAs($technician)
            ->postJson("/api/v1/tickets/{$ticketId}/comments", ['message' => 'VPN profile has been renewed.'])
            ->assertCreated();
        $this->actingAs($employee)
            ->postJson("/api/v1/tickets/{$ticketId}/comments", ['message' => 'Connection is stable now, thank you.'])
            ->assertCreated();
        $this->actingAs($technician)
            ->postJson("/api/v1/tickets/{$ticketId}/status", ['status' => 'RESOLVED'])
            ->assertOk()->assertJsonPath('data.status', 'RESOLVED');
        $this->actingAs($employee)
            ->getJson("/api/v1/tickets/{$ticketId}")
            ->assertOk()->assertJsonPath('data.status', 'RESOLVED')->assertJsonCount(2, 'data.comments');
        $this->actingAs($employee)
            ->postJson("/api/v1/tickets/{$ticketId}/status", ['status' => 'CLOSED'])
            ->assertOk()->assertJsonPath('data.status', 'CLOSED');
    }

    public function test_scenario_two_asset_assignment_ticket_history_and_return_are_preserved(): void
    {
        $admin = $this->userWithPermissions(SystemRole::Admin, [
            PermissionName::AssetViewAll,
            PermissionName::AssetCreate,
            PermissionName::AssetAssign,
        ]);
        $employee = $this->userWithPermissions(SystemRole::Employee, [
            PermissionName::AssetViewOwn,
            PermissionName::TicketCreate,
            PermissionName::TicketViewOwn,
        ]);
        $category = TicketCategory::factory()->create();

        $created = $this->actingAs($admin)->postJson('/api/v1/assets', [
            'name' => 'Portfolio Demo Laptop',
            'category' => 'Laptop',
            'serial_number' => 'ACCEPTANCE-ASSET-01',
        ])->assertCreated()->assertJsonPath('data.status', 'AVAILABLE');
        $assetId = $created->json('data.id');

        $this->actingAs($admin)
            ->postJson("/api/v1/assets/{$assetId}/assign", ['user_id' => $employee->id])
            ->assertOk()->assertJsonPath('data.status', 'ASSIGNED');
        $this->actingAs($employee)
            ->getJson('/api/v1/assets')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $assetId);
        $this->actingAs($employee)->postJson('/api/v1/tickets', [
            'title' => 'Assigned laptop camera is unavailable',
            'description' => 'The integrated camera cannot be detected by meeting applications.',
            'category_id' => $category->id,
            'asset_id' => $assetId,
        ])->assertCreated()->assertJsonPath('data.asset.id', $assetId);
        $this->actingAs($employee)
            ->getJson("/api/v1/assets/{$assetId}")
            ->assertOk()->assertJsonCount(1, 'data.tickets');
        $this->actingAs($admin)
            ->postJson("/api/v1/assets/{$assetId}/return")
            ->assertOk()->assertJsonPath('data.status', 'AVAILABLE');

        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $assetId,
            'user_id' => $employee->id,
        ]);
        $this->assertNotNull(
            AssetAssignment::query()->where('asset_id', $assetId)->value('returned_at'),
        );
    }

    public function test_scenario_three_employee_is_forbidden_from_user_administration_api(): void
    {
        $employee = $this->userWithPermissions(SystemRole::Employee, [PermissionName::TicketViewOwn]);

        $this->actingAs($employee)
            ->getJson('/api/v1/users')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    /** @param list<PermissionName> $permissions */
    private function userWithPermissions(SystemRole $systemRole, array $permissions): User
    {
        $role = Role::factory()->create(['name' => $systemRole->value]);
        $permissionModels = collect($permissions)->map(
            fn (PermissionName $permission) => Permission::query()->firstOrCreate(['name' => $permission->value]),
        );
        $role->permissions()->attach($permissionModels);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
