<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_account_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Insert default types
        DB::table('gel_account_types')->insert([
            ['code' => 'cabinet', 'libelle' => 'Cabinet comptable', 'description' => 'Comptable qui gère des clients', 'actif' => true],
            ['code' => 'entreprise', 'libelle' => 'Entreprise', 'description' => 'Client du cabinet', 'actif' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_account_types');
    }
};
