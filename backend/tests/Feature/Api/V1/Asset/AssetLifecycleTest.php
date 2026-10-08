<?php

namespace Tests\Feature\Api\V1\Asset;

use App\Models\Asset;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssetLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_can_create_assign_and_return_asset_atomically(): void
    {
        Notification::fake();
        $manager = $this->userWithPermissions(SystemRole::ItManager, [
            PermissionName::AssetViewAll,
            PermissionName::AssetCreate,
            PermissionName::AssetUpdate,
            PermissionName::AssetAssign,
        ]);
        $employee = User::factory()->create();

        $created = $this->actingAs($manager)->postJson('/api/v1/assets', [
            'name' => 'Engineering Laptop',
            'category' => 'Laptop',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad T14',
            'serial_number' => 'OPS-00001',
            'purchase_date' => now()->subMonth()->toDateString(),
        ])->assertCreated()->assertJsonPath('data.status', 'AVAILABLE');

        $assetId = $created->json('data.id');
        $this->assertMatchesRegularExpression('/^AST-LAP-\d{5}$/', $created->json('data.asset_code'));

        $this->actingAs($manager)
            ->postJson("/api/v1/assets/{$assetId}/assign", ['user_id' => $employee->id, 'notes' => 'New hire kit'])
            ->assertOk()
            ->assertJsonPath('data.status', 'ASSIGNED')
            ->assertJsonPath('data.assigned_user.id', $employee->id);

        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $assetId,
            'user_id' => $employee->id,
            'returned_at' => null,
        ]);

        $this->actingAs($manager)
            ->postJson("/api/v1/assets/{$assetId}/return", ['notes' => 'Returned in good condition'])
            ->assertOk()
            ->assertJsonPath('data.status', 'AVAILABLE')
            ->assertJsonPath('data.assigned_user', null);

        $this->assertDatabaseMissing('asset_assignments', ['asset_id' => $assetId, 'returned_at' => null]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ASSET_ASSIGNED', 'entity_id' => $assetId]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ASSET_RETURNED', 'entity_id' => $assetId]);
    }

    public function test_employee_asset_list_is_scoped_to_own_assets(): void
    {
        $employee = $this->userWithPermissions(SystemRole::Employee, [PermissionName::AssetViewOwn]);
        Asset::factory()->create(['assigned_user_id' => $employee->id, 'status' => 'ASSIGNED']);
        Asset::factory()->create();

        $this->actingAs($employee)
            ->getJson('/api/v1/assets')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.assigned_user.id', $employee->id);
    }

    /** @param list<PermissionName> $permissions */
    private function userWithPermissions(SystemRole $systemRole, array $permissions): User
    {
        $role = Role::factory()->create(['name' => $systemRole->value]);
        $permissionModels = collect($permissions)->map(fn (PermissionName $permission) => Permission::query()->firstOrCreate(
            ['name' => $permission->value],
            ['description' => fake()->sentence()],
        ));
        $role->permissions()->attach($permissionModels);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
