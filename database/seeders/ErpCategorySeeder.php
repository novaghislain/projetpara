<?php

namespace Database\Seeders;

use App\Models\ErpCategory;
use Illuminate\Database\Seeder;

class ErpCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Produits Finis', 'type' => 'product', 'description' => 'Articles destinés à la vente'],
            ['name' => 'Matières Premières', 'type' => 'raw_material', 'description' => 'Intrants et fournitures'],
            ['name' => 'Fournitures de Bureau', 'type' => 'product', 'description' => 'Consommables de bureau'],
            ['name' => 'Prestations de Service', 'type' => 'service', 'description' => 'Services facturables'],
        ];

        foreach ($categories as $cat) {
            ErpCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
