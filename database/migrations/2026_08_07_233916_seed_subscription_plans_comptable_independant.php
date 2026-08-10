<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('subscription_plans')->insert([
            [
                'name' => 'Starter',
                'description' => 'Idéal pour démarrer. Jusqu\'à 3 clients gérés.',
                'price' => 0, // À définir plus tard
                'profile_type' => 'comptable_independant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professionnel',
                'description' => 'Pour les comptables confirmés. Jusqu\'à 10 clients gérés.',
                'price' => 0, // À définir plus tard
                'profile_type' => 'comptable_independant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cabinet Solo',
                'description' => 'La solution complète. Nombre de clients illimité.',
                'price' => 0, // À définir plus tard
                'profile_type' => 'comptable_independant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('subscription_plans')->where('profile_type', 'comptable_independant')->delete();
    }
};
