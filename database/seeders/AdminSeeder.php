<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@gel.cabinet', 'password' => Hash::make('admin123'), 'role' => 'super_admin', 'is_admin' => true, 'is_active' => true],
            ['name' => 'Directeur Cabinet', 'email' => 'directeur@gel.cabinet', 'password' => Hash::make('admin123'), 'role' => 'director', 'is_admin' => true, 'is_active' => true],
            ['name' => 'Alice Comptabilité', 'email' => 'alice@gel.cabinet', 'password' => Hash::make('admin123'), 'role' => 'pole_responsible', 'is_admin' => false, 'is_active' => true],
            ['name' => 'Bob Juridique', 'email' => 'bob@gel.cabinet', 'password' => Hash::make('admin123'), 'role' => 'collaborator', 'is_admin' => false, 'is_active' => true],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
