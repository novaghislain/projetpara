import os
import glob

migrations_dir = 'database/migrations'

# Mapping of file suffixes to their new schema content
schemas = {
    'create_erp_categories_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('product'); // product, raw_material, service
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_categories'); }
};
""",
    'create_erp_items_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('designation');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->integer('stock_alert')->default(0);
            $table->string('unit')->default('unite');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_items'); }
};
""",
    'create_erp_warehouses_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_warehouses'); }
};
""",
    'create_erp_stock_movements_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('erp_warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // entry, exit, transfer
            $table->integer('quantity');
            $table->string('reference_doc')->nullable(); // Bon d'arrivage, Facture, etc.
            $table->date('movement_date');
            $table->text('motif')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_stock_movements'); }
};
""",
    'create_erp_invoices_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('type')->default('sale'); // sale, purchase, return
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('client_name')->nullable(); // For unregistered clients
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_ht', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, validated, paid, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_invoices'); }
};
""",
    'create_erp_invoice_items_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('erp_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_invoice_items'); }
};
""",
    'create_erp_employees_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_employees', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->string('cnss_number')->nullable();
            $table->string('ifu_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_employees'); }
};
""",
    'create_erp_payrolls_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_employee_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // e.g., 2026-04
            $table->decimal('base_salary', 15, 2);
            $table->decimal('bonuses', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('advances', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->string('status')->default('draft'); // draft, validated, paid
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_payrolls'); }
};
""",
    'create_erp_bank_accounts_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Caisse Principale, Compte BOA, Momo...
            $table->string('type')->default('cash'); // cash, bank, mobile_money
            $table->string('account_number')->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_bank_accounts'); }
};
""",
    'create_erp_transactions_table.php': """<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('type'); // income, expense, transfer
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->string('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('erp_transactions'); }
};
"""
}

for filename in os.listdir(migrations_dir):
    for suffix, content in schemas.items():
        if filename.endswith(suffix):
            filepath = os.path.join(migrations_dir, filename)
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {filename}")
