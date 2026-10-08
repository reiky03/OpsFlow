<?php

namespace Database\Seeders;

use App\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::query()->where('email', 'manager@opsflow.local')->firstOrFail();
        $assets = [
            ['AST-LAP-00001', 'MacBook Pro 14', 'Laptop', 'Apple', 'M3 Pro', 'OPS-MBP-001', AssetStatus::Assigned, 'employee@opsflow.local'],
            ['AST-LAP-00002', 'ThinkPad T14', 'Laptop', 'Lenovo', 'Gen 5', 'OPS-LEN-002', AssetStatus::Assigned, 'dian.engineer@opsflow.local'],
            ['AST-LAP-00003', 'EliteBook 840', 'Laptop', 'HP', 'G11', 'OPS-HP-003', AssetStatus::Assigned, 'budi.finance@opsflow.local'],
            ['AST-MON-00004', 'UltraSharp 27', 'Monitor', 'Dell', 'U2723QE', 'OPS-DEL-004', AssetStatus::Assigned, 'employee@opsflow.local'],
            ['AST-PHN-00005', 'iPhone 15', 'Mobile Phone', 'Apple', '15', 'OPS-PHN-005', AssetStatus::Assigned, 'rudi.operations@opsflow.local'],
            ['AST-LAP-00006', 'Latitude 7450', 'Laptop', 'Dell', '7450', 'OPS-DEL-006', AssetStatus::Available, null],
            ['AST-MON-00007', 'ThinkVision 24', 'Monitor', 'Lenovo', 'T24i', 'OPS-MON-007', AssetStatus::Available, null],
            ['AST-PRN-00008', 'LaserJet Pro', 'Printer', 'HP', 'M404dn', 'OPS-PRN-008', AssetStatus::Maintenance, null],
            ['AST-LAP-00009', 'ThinkPad X1 Carbon', 'Laptop', 'Lenovo', 'Gen 12', 'OPS-LEN-009', AssetStatus::Available, null],
            ['AST-LAP-00010', 'MacBook Air 13', 'Laptop', 'Apple', 'M3', 'OPS-MBA-010', AssetStatus::Available, null],
            ['AST-MON-00011', 'ProDisplay 24', 'Monitor', 'LG', '24QP500', 'OPS-LG-011', AssetStatus::Available, null],
            ['AST-PHN-00012', 'Galaxy S24', 'Mobile Phone', 'Samsung', 'S24', 'OPS-PHN-012', AssetStatus::Available, null],
            ['AST-TAB-00013', 'iPad Air', 'Tablet', 'Apple', 'M2', 'OPS-TAB-013', AssetStatus::Available, null],
            ['AST-NET-00014', 'UniFi Access Point', 'Network Device', 'Ubiquiti', 'U6 Pro', 'OPS-NET-014', AssetStatus::Available, null],
            ['AST-PRN-00015', 'EcoTank Office', 'Printer', 'Epson', 'L6490', 'OPS-PRN-015', AssetStatus::Available, null],
            ['AST-LAP-00016', 'ProBook 440', 'Laptop', 'HP', 'G11', 'OPS-HP-016', AssetStatus::Maintenance, null],
            ['AST-MON-00017', 'Business Monitor 27', 'Monitor', 'Samsung', 'S27A600', 'OPS-MON-017', AssetStatus::Retired, null],
            ['AST-ACC-00018', 'USB-C Dock', 'Accessory', 'Dell', 'WD22TB4', 'OPS-ACC-018', AssetStatus::Available, null],
            ['AST-ACC-00019', 'Conference Headset', 'Accessory', 'Jabra', 'Evolve2 65', 'OPS-ACC-019', AssetStatus::Available, null],
            ['AST-NET-00020', 'Branch Router', 'Network Device', 'MikroTik', 'RB5009', 'OPS-NET-020', AssetStatus::Available, null],
        ];

        foreach ($assets as [$code, $name, $category, $brand, $model, $serial, $status, $email]) {
            $user = $email ? User::query()->where('email', $email)->firstOrFail() : null;
            $asset = Asset::query()->updateOrCreate(['asset_code' => $code], [
                'name' => $name,
                'category' => $category,
                'brand' => $brand,
                'model' => $model,
                'serial_number' => $serial,
                'status' => $status,
                'assigned_user_id' => $user?->id,
                'purchase_date' => now()->subMonths(10)->toDateString(),
                'warranty_end_date' => now()->addYears(2)->toDateString(),
                'notes' => 'Portfolio demonstration inventory record.',
            ]);

            if ($user) {
                AssetAssignment::query()->updateOrCreate(
                    ['asset_id' => $asset->id, 'user_id' => $user->id, 'returned_at' => null],
                    ['assigned_at' => now()->subMonths(3), 'assigned_by' => $manager->id, 'notes' => 'Standard equipment allocation.'],
                );
            }
        }
    }
}
