<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ErpWarehouse extends Model {
    protected $fillable = ['name','location','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function stockMovements() { return $this->hasMany(ErpStockMovement::class); }
}
