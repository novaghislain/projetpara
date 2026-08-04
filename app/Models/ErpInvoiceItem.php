<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une ligne de facture dans le module ERP.
 *
 * Table associée : `erp_invoice_items` (via convention Laravel)
 *
 * Relations :
 * - Une ligne appartient à une facture (ErpInvoice)
 * - Une ligne est liée à un article (ErpItem)
 */
class ErpInvoiceItem extends Model {
    protected $fillable = ['erp_invoice_id','erp_item_id','designation','quantity','unit_price','total_price'];
    protected $casts = ['unit_price'=>'decimal:2','total_price'=>'decimal:2'];
    public function invoice() { return $this->belongsTo(ErpInvoice::class, 'erp_invoice_id'); }
    public function item()    { return $this->belongsTo(ErpItem::class, 'erp_item_id'); }
}
