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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('emecef_nim')->nullable();
            $table->unsignedInteger('emecef_compteur')->nullable();
            $table->string('emecef_hash')->nullable();
            $table->text('emecef_qr')->nullable();
            $table->string('emecef_statut')->nullable();
            $table->dateTime('emecef_datetime')->nullable();
            $table->string('emecef_uid')->nullable();
            $table->json('emecef_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'emecef_nim', 'emecef_compteur', 'emecef_hash', 
                'emecef_qr', 'emecef_statut', 'emecef_datetime',
                'emecef_uid', 'emecef_response'
            ]);
        });
    }
};
