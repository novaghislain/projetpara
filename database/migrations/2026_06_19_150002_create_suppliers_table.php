<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->string('name');
            $t->string('contact_name')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->integer('delivery_delay')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('suppliers'); }
};
