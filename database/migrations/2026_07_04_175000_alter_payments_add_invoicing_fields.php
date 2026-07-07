<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Nouveaux champs pour la facturation, sans supprimer l'existant
            $table->foreignId('client_id')->nullable()->constrained()->after('id');
            $table->string('type', 20)->nullable()->after('client_id'); // incoming, outgoing
            $table->string('payment_number', 50)->nullable()->after('type');
            $table->foreignId('partner_id')->nullable()->constrained()->after('payment_number');
            $table->foreignId('invoice_id')->nullable()->constrained()->after('partner_id');
            $table->date('payment_date')->nullable()->after('invoice_id');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000)->after('amount');
            $table->foreignId('bank_account_id')->nullable()->constrained('accounting_accounts')->after('reference');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->after('bank_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn([
                'client_id', 'type', 'payment_number', 'partner_id',
                'invoice_id', 'payment_date', 'exchange_rate',
                'bank_account_id', 'journal_entry_id',
            ]);
        });
    }
};
