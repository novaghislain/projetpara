<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->nullable();
            $t->foreignId('parent_id')->nullable()->constrained('product_categories')->cascadeOnDelete();
            $t->string('color', 20)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('product_categories'); }
};
