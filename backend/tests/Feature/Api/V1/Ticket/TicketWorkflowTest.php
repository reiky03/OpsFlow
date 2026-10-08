<?php

namespace Tests\Feature\Api\V1\Ticket;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use App\TicketStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_employee_can_create_ticket_and_receive_generated_number(): void
    {
        Notification::fake();
        $employee = $this->userWithPermissions(SystemRole::Employee, [
            PermissionName::TicketCreate,
            PermissionName::TicketViewOwn,
            PermissionName::TicketComment,
        ]);
        $category = TicketCategory::factory()->create();

        $response = $this->actingAs($employee)->postJson('/api/v1/tickets', [
            'title' => 'Cannot connect to company VPN',
            'description' => 'The VPN client reports a connection timeout.',
            'category_id' => $category->id,
            'priority' => 'HIGH',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reporter.id', $employee->id)
            ->assertJsonPath('data.status', 'OPEN')
            ->assertJsonPath('data.priority', 'HIGH');

        $ticketNumber = $response->json('data.ticket_number');
        $this->assertMatchesRegularExpression('/^TCK-\d{4}-\d{6}$/', $ticketNumber);
        $this->assertDatabaseHas('ticket_activities', ['type' => 'CREATED']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'TICKET_CREATED']);
    }

    public function test_manager_can_assign_ticket_and_technician_can_follow_valid_workflow(): void
    {
        Notification::fake();
        $reporter = User::factory()->create();
        $manager = $this->userWithPermissions(SystemRole::ItManager, [
            PermissionName::TicketViewAll,
            PermissionName::TicketAssign,
        ]);
        $technician = $this->userWithPermissions(SystemRole::Technician, [
            PermissionName::TicketViewAssigned,
            PermissionName::TicketUpdateStatus,
        ]);
        $ticket = Ticket::factory()->for($reporter, 'reporter')->create();

        $this->actingAs($manager)
            ->postJson("/api/v1/tickets/{$ticket->id}/assign", ['technician_id' => $technician->id])
            ->assertOk()
            ->assertJsonPath('data.status', 'ASSIGNED')
            ->assertJsonPath('data.assigned_technician.id', $technician->id);

        $this->actingAs($technician)
            ->postJson("/api/v1/tickets/{$ticket->id}/status", ['status' => 'IN_PROGRESS'])
            ->assertOk()
            ->assertJsonPath('data.status', 'IN_PROGRESS');

        $this->actingAs($technician)
            ->postJson("/api/v1/tickets/{$ticket->id}/status", ['status' => 'CLOSED'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.status.0', 'Status cannot change from IN_PROGRESS to CLOSED.');

        $this->assertSame(TicketStatus::InProgress, $ticket->fresh()->status);
    }

    public function test_internal_notes_are_hidden_from_employee(): void
    {
        Notification::fake();
        $employee = $this->userWithPermissions(SystemRole::Employee, [
            PermissionName::TicketViewOwn,
            PermissionName::TicketComment,
        ]);
        $technician = $this->userWithPermissions(SystemRole::Technician, [
            PermissionName::TicketViewAssigned,
            PermissionName::TicketComment,
            PermissionName::TicketInternalNote,
        ]);
        $ticket = Ticket::factory()->for($employee, 'reporter')->create([
            'assigned_technician_id' => $technician->id,
            'status' => TicketStatus::Assigned,
        ]);

        $this->actingAs($technician)
            ->postJson("/api/v1/tickets/{$ticket->id}/comments", [
                'message' => 'Possible account lockout; verify logs first.',
                'is_internal' => true,
            ])
            ->assertCreated();

        $this->actingAs($employee)
            ->getJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data.comments');

        $this->actingAs($employee)
            ->postJson("/api/v1/tickets/{$ticket->id}/comments", [
                'message' => 'I should not create this note.',
                'is_internal' => true,
            ])
            ->assertForbidden();
    }

    public function test_ticket_attachment_uses_private_random_storage_name(): void
    {
        Storage::fake('local');
        $employee = $this->userWithPermissions(SystemRole::Employee, [
            PermissionName::TicketViewOwn,
            PermissionName::TicketComment,
        ]);
        $ticket = Ticket::factory()->for($employee, 'reporter')->create();

        $response = $this->actingAs($employee)->postJson("/api/v1/tickets/{$ticket->id}/attachments", [
            'file' => UploadedFile::fake()->create('diagnostic.log', 128, 'text/plain'),
        ]);

        $response->assertCreated()->assertJsonPath('data.original_name', 'diagnostic.log');
        $attachment = $ticket->attachments()->firstOrFail();
        $this->assertNotSame('diagnostic.log', $attachment->stored_name);
        Storage::disk('local')->assertExists($attachment->path);

        $this->actingAs($employee)
            ->get("/api/v1/tickets/{$ticket->id}/attachments/{$attachment->id}")
            ->assertOk();
    }

    /** @param list<PermissionName> $permissions */
    private function userWithPermissions(SystemRole $systemRole, array $permissions): User
    {
        $role = Role::factory()->create(['name' => $systemRole->value]);
        $permissionModels = collect($permissions)->map(
            fn (PermissionName $permission) => Permission::query()->firstOrCreate(
                ['name' => $permission->value],
                ['description' => fake()->sentence()],
            ),
        );
        $role->permissions()->attach($permissionModels);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
