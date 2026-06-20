<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('business_users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('role_id')->nullable()->constrained('business_roles')->nullOnDelete();
            $t->string('business_role', 50)->default('cashier');
            $t->boolean('is_active')->default(true);
            $t->json('permissions')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['client_id', 'user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('business_users'); }
};
