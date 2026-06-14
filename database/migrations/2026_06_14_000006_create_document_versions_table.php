<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Colonnes supplementaires pour l'arborescence des dossiers
        Schema::table('client_folders', function (Blueprint $table) {
            $table->string('path')->nullable()->after('slug');
            $table->integer('level')->default(1)->after('path');
        });

        // 2. Ajouter des colonnes supplémentaires à documents
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_hash', 64)->nullable()->after('file_path');
            $table->json('tags')->nullable()->after('description');
            $table->string('original_name')->nullable()->after('name');
        });

        // 3. Table de versioning des documents
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('file_path');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_hash', 64)->nullable();
            $table->string('mime_type')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_notes')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'version_number']);
        });

        // 4. Table d'audit des documents
        Schema::create('document_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // view, upload, update, delete, restore, download
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_audit_log');
        Schema::dropIfExists('document_versions');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['file_hash', 'tags', 'original_name']);
        });
    }
};
