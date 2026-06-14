<?php

namespace Database\Seeders;

use App\Models\ErpEmployee;
use Illuminate\Database\Seeder;

class ErpEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            ['matricule' => 'EMP-001', 'first_name' => 'Jean', 'last_name' => 'Kouassi', 'position' => 'Comptable Senior', 'phone' => '+229 01 23 45 67', 'base_salary' => 350000, 'hire_date' => '2022-03-15', 'status' => 'active', 'cnss_number' => 'CNSS-001', 'ifu_number' => 'IFU-001'],
            ['matricule' => 'EMP-002', 'first_name' => 'Marie', 'last_name' => 'Hounsou', 'position' => 'Juriste d\'Affaires', 'phone' => '+229 01 23 45 68', 'base_salary' => 400000, 'hire_date' => '2023-01-10', 'status' => 'active'],
            ['matricule' => 'EMP-003', 'first_name' => 'David', 'last_name' => 'Agossou', 'position' => 'Assistant Comptable', 'phone' => '+229 01 23 45 69', 'base_salary' => 200000, 'hire_date' => '2024-06-01', 'status' => 'active', 'cnss_number' => 'CNSS-003'],
            ['matricule' => 'EMP-004', 'first_name' => 'Sarah', 'last_name' => 'Dossou', 'position' => 'Responsable RH', 'phone' => '+229 01 23 45 70', 'base_salary' => 300000, 'hire_date' => '2023-09-20', 'status' => 'active'],
            ['matricule' => 'EMP-005', 'first_name' => 'Paul', 'last_name' => 'Soglo', 'position' => 'Fiscaliste', 'phone' => '+229 01 23 45 71', 'base_salary' => 450000, 'hire_date' => '2022-11-01', 'status' => 'active', 'ifu_number' => 'IFU-005'],
        ];

        foreach ($employees as $emp) {
            ErpEmployee::updateOrCreate(['matricule' => $emp['matricule']], $emp);
        }
    }
}
