import os

models_dir = 'app/Models'

models = {
    'ErpCategory.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class ErpCategory extends Model {
    use SoftDeletes;
    protected $fillable = ['name','type','description'];
    public function items() { return $this->hasMany(ErpItem::class); }
}
""",
    'ErpItem.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class ErpItem extends Model {
    use SoftDeletes;
    protected $fillable = ['erp_category_id','reference','designation','purchase_price','selling_price','stock_alert','unit'];
    protected $casts = ['purchase_price'=>'decimal:2','selling_price'=>'decimal:2'];
    public function category() { return $this->belongsTo(ErpCategory::class, 'erp_category_id'); }
    public function stockMovements() { return $this->hasMany(ErpStockMovement::class); }
    public function getCurrentStockAttribute() {
        $in  = $this->stockMovements()->where('type','entry')->sum('quantity');
        $out = $this->stockMovements()->where('type','exit')->sum('quantity');
        return $in - $out;
    }
}
""",
    'ErpWarehouse.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;

class ErpWarehouse extends Model {
    protected $fillable = ['name','location','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function stockMovements() { return $this->hasMany(ErpStockMovement::class); }
}
""",
    'ErpStockMovement.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;

class ErpStockMovement extends Model {
    protected $fillable = ['erp_item_id','erp_warehouse_id','type','quantity','reference_doc','movement_date','motif','created_by'];
    protected $casts = ['movement_date'=>'date'];
    public function item()      { return $this->belongsTo(ErpItem::class, 'erp_item_id'); }
    public function warehouse() { return $this->belongsTo(ErpWarehouse::class, 'erp_warehouse_id'); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
}
""",
    'ErpInvoice.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class ErpInvoice extends Model {
    use SoftDeletes;
    protected $fillable = ['invoice_number','type','client_id','client_name','invoice_date','due_date','total_ht','tax_amount','total_ttc','status','created_by'];
    protected $casts = ['invoice_date'=>'date','due_date'=>'date','total_ht'=>'decimal:2','tax_amount'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function client()   { return $this->belongsTo(Client::class); }
    public function lineItems(){ return $this->hasMany(ErpInvoiceItem::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
""",
    'ErpInvoiceItem.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;

class ErpInvoiceItem extends Model {
    protected $fillable = ['erp_invoice_id','erp_item_id','designation','quantity','unit_price','total_price'];
    protected $casts = ['unit_price'=>'decimal:2','total_price'=>'decimal:2'];
    public function invoice() { return $this->belongsTo(ErpInvoice::class, 'erp_invoice_id'); }
    public function item()    { return $this->belongsTo(ErpItem::class, 'erp_item_id'); }
}
""",
    'ErpEmployee.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class ErpEmployee extends Model {
    use SoftDeletes;
    protected $fillable = ['matricule','first_name','last_name','position','phone','base_salary','cnss_number','ifu_number','hire_date','status'];
    protected $casts = ['base_salary'=>'decimal:2','hire_date'=>'date'];
    public function payrolls() { return $this->hasMany(ErpPayroll::class); }
}
""",
    'ErpPayroll.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;

class ErpPayroll extends Model {
    protected $fillable = ['erp_employee_id','period','base_salary','bonuses','deductions','advances','net_salary','status'];
    protected $casts = ['base_salary'=>'decimal:2','bonuses'=>'decimal:2','deductions'=>'decimal:2','advances'=>'decimal:2','net_salary'=>'decimal:2'];
    public function employee() { return $this->belongsTo(ErpEmployee::class, 'erp_employee_id'); }
}
""",
    'ErpBankAccount.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;

class ErpBankAccount extends Model {
    protected $fillable = ['name','type','account_number','initial_balance','is_active'];
    protected $casts = ['initial_balance'=>'decimal:2','is_active'=>'boolean'];
    public function transactions() { return $this->hasMany(ErpTransaction::class); }
}
""",
    'ErpTransaction.php': """<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;

class ErpTransaction extends Model {
    protected $fillable = ['erp_bank_account_id','transaction_date','type','amount','reference','description','created_by'];
    protected $casts = ['transaction_date'=>'date','amount'=>'decimal:2'];
    public function account()  { return $this->belongsTo(ErpBankAccount::class, 'erp_bank_account_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
""",
}

for filename, content in models.items():
    filepath = os.path.join(models_dir, filename)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f'Updated {filename}')
