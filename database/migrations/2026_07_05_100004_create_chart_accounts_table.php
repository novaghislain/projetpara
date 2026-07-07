<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 255);
            $table->enum('type', ['active', 'passive', 'charge', 'produit', 'autre'])->default('autre');
            $table->string('class', 1)->nullable()->comment('Classe SYSCOHADA 1-9');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_syscohada')->default(true);
            $table->string('parent_code', 20)->nullable();
            $table->decimal('tva_rate', 5, 2)->nullable();
            $table->boolean('has_tva')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'class']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_accounts');
    }
};
