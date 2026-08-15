<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Magic Links
        if (!Schema::hasTable('magic_link_requests')) {
            Schema::create('magic_link_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('requested_documents')->nullable(); // Array of document types
                $table->json('uploaded_files')->nullable(); // Array of file paths
                $table->timestamp('expires_at');
                $table->string('status')->default('pending'); // pending, partially_completed, completed, expired
                $table->timestamps();
            });
        }

        // 2. Dunning Campaigns & Logs (Relance IA)
        if (!Schema::hasTable('dunning_campaigns')) {
            Schema::create('dunning_campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('invoice_number');
                $table->decimal('amount_due', 15, 2);
                $table->integer('escalation_level')->default(1); // 1 = Email, 2 = SMS, 3 = Call
                $table->string('status')->default('active'); // active, paid, disputed, uncollectible
                $table->timestamp('next_action_at')->nullable();
                $table->string('ai_predicted_payment_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dunning_logs')) {
            Schema::create('dunning_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('dunning_campaigns')->onDelete('cascade');
                $table->string('action_type'); // email_sent, sms_sent, ai_prediction
                $table->text('content')->nullable();
                $table->timestamps();
            });
        }

        // 3. Recurring Transactions
        if (!Schema::hasTable('recurring_transactions')) {
            Schema::create('recurring_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('type'); // scheduled, reminder, template
                $table->string('frequency'); // daily, weekly, monthly, yearly
                $table->date('next_run_date');
                $table->json('template_data'); // JournalEntry skeleton
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. User Bookmarks (Signets)
        if (!Schema::hasTable('user_bookmarks')) {
            Schema::create('user_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('user_id')->constrained('utilisateurs')->onDelete('cascade');
                $table->string('title');
                $table->string('url');
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 5. Dashboard Config
        if (!Schema::hasColumn('utilisateurs', 'dashboard_config')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->json('dashboard_config')->nullable()->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_link_requests');
        Schema::dropIfExists('dunning_logs');
        Schema::dropIfExists('dunning_campaigns');
        Schema::dropIfExists('recurring_transactions');
        Schema::dropIfExists('user_bookmarks');
        
        if (Schema::hasColumn('utilisateurs', 'dashboard_config')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropColumn('dashboard_config');
            });
        }
    }
};
