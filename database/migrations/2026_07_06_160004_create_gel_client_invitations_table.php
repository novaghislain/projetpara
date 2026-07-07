<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gel_client_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->nullable()->constrained('gel_cabinets')->nullOnDelete();
            $table->string('email', 255);
            $table->string('nom', 200)->nullable();
            $table->string('token', 100)->unique();
            $table->enum('statut', ['en_attente', 'acceptee', 'expiree'])->default('en_attente');
            $table->text('message')->nullable();
            $table->timestamp('acceptee_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_client_invitations');
    }
};
