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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->nullable()->constrained('gel_cabinets');
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->string('actor_email');
            $table->string('actor_role');
            $table->foreignId('client_id')->nullable()->constrained('gel_clients');
            $table->string('action'); // 'ecriture.create', 'ecriture.valider', 'exercice.cloturer', etc.
            $table->string('entity_type'); // 'App\Models\Gel\Comptabilite\EcritureComptable'
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('before')->nullable(); // État avant modification
            $table->json('after')->nullable();  // État après modification
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            $table->index(['cabinet_id', 'client_id', 'created_at']);
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
