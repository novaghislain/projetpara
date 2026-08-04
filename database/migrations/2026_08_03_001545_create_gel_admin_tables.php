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
        // Table for Cabinet Legal Documents
        // Schema::create('cabinet_documents', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('cabinet_id')->constrained('cabinets')->onDelete('cascade');
        //     $table->string('type', 100); // e.g. statuts, rccm, dfé, etc.
        //     $table->string('titre', 255);
        //     $table->string('chemin', 255);
        //     $table->timestamps();
        // });

        Schema::create('cabinet_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('cabinets')->onDelete('cascade');
            $table->string('email');
            $table->foreignId('role_id')->nullable()->constrained('gel_roles')->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // Table for billing history (GEL SABINET -> Cabinet)
        Schema::create('cabinet_subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained('cabinets')->onDelete('cascade');
            $table->string('invoice_number', 100)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('status', 50)->default('paid');
            $table->string('plan_name', 100);
            $table->timestamp('paid_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // Table for Session Tracking (if we want to list active devices per user)
        Schema::create('gel_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('login_at')->useCurrent();
            $table->boolean('is_success')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gel_login_logs');
        Schema::dropIfExists('cabinet_subscription_invoices');
        Schema::dropIfExists('cabinet_invitations');
        Schema::dropIfExists('cabinet_documents');
    }
};
