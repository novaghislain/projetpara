<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pos_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->foreignId('business_user_id')->constrained('business_users')->cascadeOnDelete();
            $t->timestamp('opened_at')->useCurrent();
            $t->timestamp('closed_at')->nullable();
            $t->decimal('opening_amount', 12, 2)->default(0);
            $t->decimal('closing_amount', 12, 2)->nullable();
            $t->decimal('expected_amount', 12, 2)->nullable();
            $t->decimal('difference', 12, 2)->nullable();
            $t->string('status', 20)->default('open');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pos_sessions'); }
};
