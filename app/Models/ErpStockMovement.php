<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ErpStockMovement extends Model {
    protected $fillable = ['erp_item_id','erp_warehouse_id','type','quantity','reference_doc','movement_date','motif','created_by'];
    protected $casts = ['movement_date'=>'date'];
    public function item()      { return $this->belongsTo(ErpItem::class, 'erp_item_id'); }
    public function warehouse() { return $this->belongsTo(ErpWarehouse::class, 'erp_warehouse_id'); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
}
