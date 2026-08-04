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
        Schema::create('gel_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabinet_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_type'); // 'accountant' or 'business'
            $table->text('message');
            $table->string('piece_jointe')->nullable();
            $table->boolean('est_lu')->default(false);
            $table->timestamps();

            $table->foreign('cabinet_id')->references('id')->on('gel_cabinets')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gel_messages');
    }
};
