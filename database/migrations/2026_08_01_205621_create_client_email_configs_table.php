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
        Schema::create('client_email_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            
            // IMAP Settings (Incoming)
            $table->string('imap_host')->nullable();
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl'); // ssl, tls, etc.
            $table->string('imap_username')->nullable();
            $table->text('imap_password')->nullable(); // Encrypted
            
            // SMTP Settings (Outgoing)
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(465);
            $table->string('smtp_encryption')->default('ssl');
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable(); // Encrypted

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_email_configs');
    }
};
