<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->string('status', 20)->default('draft');
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('validated_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_sessions'); }
};
