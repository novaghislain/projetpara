<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // S4/S5/S9 : catégorie de classement (permanent/courant/administration) + date du document
            if (!Schema::hasColumn('documents', 'category')) {
                $table->string('category')->nullable()->after('folder_id');
            }
            if (!Schema::hasColumn('documents', 'document_date')) {
                $table->date('document_date')->nullable()->after('category');
            }

            // S10 : workflow de circulation (entreprise → secrétariat → comptable → validation)
            if (!Schema::hasColumn('documents', 'workflow_step')) {
                $table->string('workflow_step')->default('recu')->after('document_date'); // recu | classe | transmis_comptable | valide
            }
            if (!Schema::hasColumn('documents', 'workflow_notes')) {
                $table->text('workflow_notes')->nullable()->after('workflow_step');
            }
            if (!Schema::hasColumn('documents', 'priority')) {
                $table->string('priority')->nullable()->after('workflow_notes'); // normale | urgente
            }
            if (!Schema::hasColumn('documents', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('documents', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('processed_at');
                $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('documents', 'transmitted_at')) {
                $table->timestamp('transmitted_at')->nullable()->after('processed_by');
            }
            if (!Schema::hasColumn('documents', 'transmitted_by')) {
                $table->unsignedBigInteger('transmitted_by')->nullable()->after('transmitted_at');
                $table->foreign('transmitted_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('documents', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('transmitted_by');
            }
            if (!Schema::hasColumn('documents', 'validated_by')) {
                $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
                $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $columns = ['validated_by', 'transmitted_by', 'processed_by', 'validated_at', 'transmitted_at', 'processed_at', 'priority', 'workflow_notes', 'workflow_step', 'document_date', 'category'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('documents', $col)) {
                    $table->dropForeign(['processed_by']);
                    $table->dropForeign(['transmitted_by']);
                    $table->dropForeign(['validated_by']);
                    $table->dropColumn($col);
                }
            }
        });
    }
};
