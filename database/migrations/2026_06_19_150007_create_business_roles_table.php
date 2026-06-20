<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('business_roles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->string('role', 50);
            $t->json('permissions')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('business_roles'); }
};
