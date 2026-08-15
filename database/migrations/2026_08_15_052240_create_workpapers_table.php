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
        Schema::create('workpapers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->unsignedBigInteger('fiscal_year_id');
            $table->string('period', 20);
            $table->unsignedBigInteger('account_id');
            
            $table->string('status', 20)->default('pending'); // pending, reviewed, error
            $table->foreignUuid('reviewer_id')->nullable()->constrained('utilisateurs')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('adjustments')->nullable();
            $table->json('attachments')->nullable();
            
            $table->timestamps();
            
            // Un seul workpaper par compte pour une période/année fiscale donnée
            $table->unique(['client_id', 'fiscal_year_id', 'period', 'account_id'], 'workpaper_unique_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workpapers');
    }
};
