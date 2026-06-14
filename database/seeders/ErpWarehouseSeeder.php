<?php

namespace Database\Seeders;

use App\Models\ErpWarehouse;
use Illuminate\Database\Seeder;

class ErpWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            ['name' => 'Magasin Principal', 'location' => 'Siège, Rez-de-chaussée'],
            ['name' => 'Archive Documents', 'location' => 'Sous-sol, Zone A'],
            ['name' => 'Fournitures Bureau', 'location' => '1er étage, Salle 103'],
        ];

        foreach ($warehouses as $w) {
            ErpWarehouse::updateOrCreate(['name' => $w['name']], $w);
        }
    }
}
