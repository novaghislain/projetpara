<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // e-MECeF columns on ERP invoices
        Schema::table('erp_invoices', function (Blueprint $table) {
            $table->string('emecef_nim', 50)->nullable()->after('created_by');
            $table->string('emecef_compteur', 50)->nullable()->after('emecef_nim');
            $table->string('emecef_hash', 500)->nullable()->after('emecef_compteur');
            $table->text('emecef_qr')->nullable()->after('emecef_hash');
            $table->enum('emecef_statut', ['non_emise', 'emise', 'annulee'])->default('non_emise')->after('emecef_qr');
            $table->timestamp('emecef_datetime')->nullable()->after('emecef_statut');
        });

        // e-MECeF columns on company invoices
        Schema::table('company_invoices', function (Blueprint $table) {
            $table->string('emecef_nim', 50)->nullable();
            $table->string('emecef_compteur', 50)->nullable();
            $table->string('emecef_hash', 500)->nullable();
            $table->text('emecef_qr')->nullable();
            $table->enum('emecef_statut', ['non_emise', 'emise', 'annulee'])->default('non_emise');
            $table->timestamp('emecef_datetime')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('erp_invoices', function (Blueprint $table) {
            $table->dropColumn(['emecef_nim', 'emecef_compteur', 'emecef_hash', 'emecef_qr', 'emecef_statut', 'emecef_datetime']);
        });
        Schema::table('company_invoices', function (Blueprint $table) {
            $table->dropColumn(['emecef_nim', 'emecef_compteur', 'emecef_hash', 'emecef_qr', 'emecef_statut', 'emecef_datetime']);
        });
    }
};
