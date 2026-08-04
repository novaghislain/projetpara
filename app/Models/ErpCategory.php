<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une catégorie d'articles ERP.
 *
 * Table associée : `erp_categories` (via convention Laravel)
 *
 * Relations :
 * - Une catégorie peut avoir plusieurs articles (ErpItem)
 */
class ErpCategory extends Model {
    use SoftDeletes;
    protected $fillable = ['name','type','description'];
    public function items() { return $this->hasMany(ErpItem::class); }
}
