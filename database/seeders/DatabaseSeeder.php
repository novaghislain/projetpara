<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Le seeder de rôles et permissions DOIT être appelé avant AdminSeeder
        // car les utilisateurs Admin et Company Admin ont besoin des rôles.
        $this->call([
            RoleAndPermissionSeeder::class, // ← ajouté en premier
            PoleSeeder::class,
            AdminSeeder::class,
            ServiceSeeder::class,
            AccountingAccountSeeder::class,
            ErpCategorySeeder::class,
            ErpItemSeeder::class,
            ErpWarehouseSeeder::class,
            ErpEmployeeSeeder::class,
            ErpBankAccountSeeder::class,
            DemoCompanySeeder::class,
            EdenStoreFolderSeeder::class,
            CrescendoDemoSeeder::class,
        ]);
    }
}
