<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
