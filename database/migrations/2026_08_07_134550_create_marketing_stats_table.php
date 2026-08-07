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
        Schema::create('marketing_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->onDelete('cascade');
            $table->string('platform'); // facebook, instagram, linkedin, google_ads
            $table->integer('followers_gained')->default(0);
            $table->integer('reach')->default(0);
            $table->integer('engagement')->default(0);
            $table->decimal('spend', 15, 2)->default(0);
            $table->date('reported_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_stats');
    }
};
