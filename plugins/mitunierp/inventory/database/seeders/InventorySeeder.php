<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->createWarehouses();
        $this->createOperationTypes();
    }

    private function createWarehouses(): void
    {
        $warehouseId = DB::table('inventory_warehouses')->insertGetId([
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'address' => 'Main Street 1',
            'city' => 'Jakarta',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locations = [
            ['name' => 'Stock', 'warehouse_id' => $warehouseId, 'is_active' => true],
            ['name' => 'Input', 'warehouse_id' => $warehouseId, 'is_active' => true],
            ['name' => 'Output', 'warehouse_id' => $warehouseId, 'is_active' => true],
            ['name' => 'Scrap', 'warehouse_id' => $warehouseId, 'is_active' => true],
            ['name' => 'Quarantine', 'warehouse_id' => $warehouseId, 'is_active' => true],
        ];

        foreach ($locations as $loc) {
            $locationId = DB::table('inventory_locations')->insertGetId($loc + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inventory_locations')
                ->where('id', $locationId)
                ->update(['parent_path' => (string) $locationId]);
        }
    }

    private function createOperationTypes(): void
    {
        $stockLocation = DB::table('inventory_locations')
            ->where('name', 'Stock')
            ->value('id');

        $inputLocation = DB::table('inventory_locations')
            ->where('name', 'Input')
            ->value('id');

        $outputLocation = DB::table('inventory_locations')
            ->where('name', 'Output')
            ->value('id');

        $types = [
            [
                'name' => 'Receipts',
                'code' => 'IN',
                'type' => 'receipt',
                'source_location_id' => $inputLocation,
                'destination_location_id' => $stockLocation,
            ],
            [
                'name' => 'Deliveries',
                'code' => 'OUT',
                'type' => 'delivery',
                'source_location_id' => $stockLocation,
                'destination_location_id' => $outputLocation,
            ],
            [
                'name' => 'Internal Transfers',
                'code' => 'INT',
                'type' => 'internal_transfer',
                'source_location_id' => null,
                'destination_location_id' => null,
            ],
        ];

        foreach ($types as $type) {
            DB::table('inventory_operation_types')->insert($type + [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
