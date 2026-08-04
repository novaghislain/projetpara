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
        Schema::table('client_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('client_id');
            // Assuming constraint dropping is needed for SQLite or some engines
            // We use standard change() if DBAL is present, but for safety in older Laravel:
            // $table->dropForeign(['client_id']);
        });

        // For simplicity, instead of dealing with SQLite foreign key issues which are notorious,
        // we can just add a `user_id` column to tables that need it and keep `client_id` nullable where possible.
        // But since `client_id` is foreignId constrained, making it nullable via `change()` is the standard Laravel way:
        Schema::table('client_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });

        Schema::table('client_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('client_id');
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });

        Schema::table('gel_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('cabinet_id')->nullable()->change();
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });

        Schema::table('dae_agenda_events', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            // It already has created_by which is used as user_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this is complex and usually not strictly necessary for this feature rollback.
    }
};
