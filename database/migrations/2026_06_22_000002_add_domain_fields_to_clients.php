<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('domain_code', 50)->nullable()->after('created_by');
            $table->foreignId('domain_id')->nullable()->constrained('business_domains')->nullOnDelete()->after('domain_code');
            $table->boolean('domain_confirmed')->default(false)->after('domain_id');
            $table->timestamp('domain_confirmed_at')->nullable()->after('domain_confirmed');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['domain_id']);
            $table->dropColumn(['domain_code', 'domain_id', 'domain_confirmed', 'domain_confirmed_at']);
        });
    }
};
