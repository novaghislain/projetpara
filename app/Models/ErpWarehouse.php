<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un entrepôt/dépôt dans le module ERP.
 *
 * Table associée : `erp_warehouses` (via convention Laravel)
 *
 * Relations :
 * - Un entrepôt peut avoir plusieurs mouvements de stock (ErpStockMovement)
 */
class ErpWarehouse extends Model {
    protected $fillable = ['name','location','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function stockMovements() { return $this->hasMany(ErpStockMovement::class); }
}
