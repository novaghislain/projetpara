<?php

namespace Database\Seeders;

use App\Models\ErpItem;
use Illuminate\Database\Seeder;

class ErpItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['erp_category_id' => 1, 'reference' => 'PF-001', 'designation' => 'Rapport Annuel', 'unit' => 'unite', 'purchase_price' => 2500, 'selling_price' => 5000, 'stock_alert' => 10],
            ['erp_category_id' => 1, 'reference' => 'PF-002', 'designation' => 'Liasse Fiscale', 'unit' => 'unite', 'purchase_price' => 1500, 'selling_price' => 3500, 'stock_alert' => 20],
            ['erp_category_id' => 2, 'reference' => 'MP-001', 'designation' => 'Papier A4 (ramette)', 'unit' => 'carton', 'purchase_price' => 8000, 'selling_price' => 12000, 'stock_alert' => 5],
            ['erp_category_id' => 2, 'reference' => 'MP-002', 'designation' => 'Toner Imprimante', 'unit' => 'unite', 'purchase_price' => 15000, 'selling_price' => 25000, 'stock_alert' => 3],
            ['erp_category_id' => 3, 'reference' => 'FB-001', 'designation' => 'Classeur', 'unit' => 'unite', 'purchase_price' => 500, 'selling_price' => 1200, 'stock_alert' => 30],
            ['erp_category_id' => 3, 'reference' => 'FB-002', 'designation' => 'Stylo (boîte 12)', 'unit' => 'carton', 'purchase_price' => 2000, 'selling_price' => 4500, 'stock_alert' => 10],
            ['erp_category_id' => 4, 'reference' => 'SV-001', 'designation' => 'Heure de Conseil', 'unit' => 'service', 'purchase_price' => 0, 'selling_price' => 50000, 'stock_alert' => 0],
            ['erp_category_id' => 4, 'reference' => 'SV-002', 'designation' => 'Forfait Comptabilité Mensuel', 'unit' => 'service', 'purchase_price' => 0, 'selling_price' => 150000, 'stock_alert' => 0],
            ['erp_category_id' => 4, 'reference' => 'SV-003', 'designation' => 'Forfait Fiscal Annuel', 'unit' => 'service', 'purchase_price' => 0, 'selling_price' => 300000, 'stock_alert' => 0],
        ];

        foreach ($items as $item) {
            ErpItem::updateOrCreate(['reference' => $item['reference']], $item);
        }
    }
}
