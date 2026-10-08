<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\TicketActivityType;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::query()->where('email', 'manager@opsflow.local')->firstOrFail();
        $technician = User::query()->where('email', 'technician@opsflow.local')->firstOrFail();
        $sinta = User::query()->where('email', 'sinta.technician@opsflow.local')->firstOrFail();
        $definitions = [
            ['000101', 'employee@opsflow.local', 'VPN disconnects every few minutes', 'VPN', TicketPriority::High, TicketStatus::InProgress, $technician, 'AST-LAP-00001'],
            ['000102', 'budi.finance@opsflow.local', 'Cannot export monthly finance report', 'Application', TicketPriority::Critical, TicketStatus::Assigned, $technician, 'AST-LAP-00003'],
            ['000103', 'maya.hr@opsflow.local', 'New employee email account request', 'Email', TicketPriority::Medium, TicketStatus::Open, null, null],
            ['000104', 'dian.engineer@opsflow.local', 'External monitor flickers intermittently', 'Hardware', TicketPriority::Medium, TicketStatus::WaitingUser, $sinta, 'AST-LAP-00002'],
            ['000105', 'rudi.operations@opsflow.local', 'Warehouse printer is offline', 'Printer', TicketPriority::High, TicketStatus::Resolved, $sinta, 'AST-PRN-00008'],
            ['000106', 'nadia.employee@opsflow.local', 'Design application license expired', 'Software', TicketPriority::High, TicketStatus::Closed, $technician, null],
            ['000107', 'employee@opsflow.local', 'Request access to staging environment', 'Account Access', TicketPriority::Low, TicketStatus::Open, null, null],
            ['000108', 'budi.finance@opsflow.local', 'Laptop battery drains too quickly', 'Hardware', TicketPriority::Medium, TicketStatus::Open, null, 'AST-LAP-00003'],
            ['000109', 'maya.hr@opsflow.local', 'Email signature is not updating', 'Email', TicketPriority::Low, TicketStatus::Assigned, $technician, null],
            ['000110', 'dian.engineer@opsflow.local', 'Cannot reach internal package registry', 'Network', TicketPriority::High, TicketStatus::InProgress, $sinta, 'AST-LAP-00002'],
            ['000111', 'rudi.operations@opsflow.local', 'Mobile device enrollment failed', 'Application', TicketPriority::Medium, TicketStatus::WaitingUser, $technician, 'AST-PHN-00005'],
            ['000112', 'nadia.employee@opsflow.local', 'Design font package installation request', 'Software', TicketPriority::Low, TicketStatus::Resolved, $sinta, null],
            ['000113', 'employee@opsflow.local', 'Second monitor is not detected', 'Hardware', TicketPriority::Medium, TicketStatus::Assigned, $technician, 'AST-MON-00004'],
            ['000114', 'budi.finance@opsflow.local', 'Finance shared drive access request', 'Account Access', TicketPriority::High, TicketStatus::Closed, $technician, null],
            ['000115', 'maya.hr@opsflow.local', 'Onboarding laptop preparation', 'Hardware', TicketPriority::Medium, TicketStatus::Open, null, null],
            ['000116', 'dian.engineer@opsflow.local', 'CI dashboard returns permission denied', 'Account Access', TicketPriority::High, TicketStatus::InProgress, $sinta, null],
            ['000117', 'rudi.operations@opsflow.local', 'Barcode printer produces blank labels', 'Printer', TicketPriority::Critical, TicketStatus::Assigned, $technician, 'AST-PRN-00008'],
            ['000118', 'nadia.employee@opsflow.local', 'Video conference audio is distorted', 'Hardware', TicketPriority::Medium, TicketStatus::WaitingUser, $sinta, null],
            ['000119', 'employee@opsflow.local', 'VPN profile renewal request', 'VPN', TicketPriority::Medium, TicketStatus::Resolved, $technician, 'AST-LAP-00001'],
            ['000120', 'budi.finance@opsflow.local', 'Spreadsheet application crashes on startup', 'Application', TicketPriority::High, TicketStatus::Open, null, 'AST-LAP-00003'],
            ['000121', 'maya.hr@opsflow.local', 'Distribution list membership update', 'Email', TicketPriority::Low, TicketStatus::Closed, $sinta, null],
            ['000122', 'dian.engineer@opsflow.local', 'Local development certificate expired', 'Software', TicketPriority::Medium, TicketStatus::Assigned, $technician, 'AST-LAP-00002'],
            ['000123', 'rudi.operations@opsflow.local', 'Warehouse Wi-Fi signal is unstable', 'Network', TicketPriority::High, TicketStatus::InProgress, $sinta, null],
            ['000124', 'nadia.employee@opsflow.local', 'Cannot send large email attachment', 'Email', TicketPriority::Low, TicketStatus::Resolved, $technician, null],
            ['000125', 'employee@opsflow.local', 'Password reset for analytics portal', 'Account Access', TicketPriority::Medium, TicketStatus::Closed, $technician, null],
            ['000126', 'budi.finance@opsflow.local', 'PDF printer is missing', 'Printer', TicketPriority::Low, TicketStatus::Open, null, null],
            ['000127', 'maya.hr@opsflow.local', 'HR application page loads slowly', 'Application', TicketPriority::Medium, TicketStatus::WaitingUser, $sinta, null],
            ['000128', 'dian.engineer@opsflow.local', 'Docker registry authentication failed', 'Software', TicketPriority::High, TicketStatus::Assigned, $technician, 'AST-LAP-00002'],
            ['000129', 'rudi.operations@opsflow.local', 'Office router requires configuration review', 'Network', TicketPriority::Medium, TicketStatus::Resolved, $sinta, 'AST-NET-00020'],
            ['000130', 'nadia.employee@opsflow.local', 'Request ergonomic mouse replacement', 'Other', TicketPriority::Low, TicketStatus::Open, null, null],
        ];

        foreach ($definitions as [$sequence, $reporterEmail, $title, $categoryName, $priority, $status, $assignee, $assetCode]) {
            $reporter = User::query()->where('email', $reporterEmail)->firstOrFail();
            $category = TicketCategory::query()->where('name', $categoryName)->firstOrFail();
            $asset = $assetCode ? Asset::query()->where('asset_code', $assetCode)->first() : null;
            $ticket = Ticket::query()->updateOrCreate(
                ['ticket_number' => 'TCK-'.now()->format('Y').'-'.$sequence],
                [
                    'title' => $title,
                    'description' => "{$title}. This seeded request contains realistic operational context for the OpsFlow portfolio demonstration.",
                    'category_id' => $category->id,
                    'priority' => $priority,
                    'status' => $status,
                    'reporter_id' => $reporter->id,
                    'assigned_technician_id' => $assignee?->id,
                    'asset_id' => $asset?->id,
                    'assigned_by' => $assignee ? $manager->id : null,
                    'assigned_at' => $assignee ? now()->subDays(2) : null,
                    'resolved_at' => in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true) ? now()->subDay() : null,
                    'closed_at' => $status === TicketStatus::Closed ? now()->subHours(8) : null,
                ],
            );
            $ticket->activities()->updateOrCreate(
                ['type' => TicketActivityType::Created, 'description' => "Ticket {$ticket->ticket_number} created by {$reporter->name}."],
                ['actor_id' => $reporter->id, 'new_values' => ['status' => TicketStatus::Open->value, 'priority' => $priority->value]],
            );
            if ($assignee) {
                $ticket->activities()->updateOrCreate(
                    ['type' => TicketActivityType::Assigned, 'description' => "Assigned to {$assignee->name} by {$manager->name}."],
                    ['actor_id' => $manager->id, 'new_values' => ['assigned_technician_id' => $assignee->id]],
                );
            }
        }
    }
}
