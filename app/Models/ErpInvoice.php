<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

/**
 * Modèle représentant une facture dans le module ERP.
 *
 * Table associée : `erp_invoices` (via convention Laravel)
 *
 * Supporte la facturation électronique (e-MECeF) avec les champs
 * NIM, compteur, hash et QR code.
 *
 * Relations :
 * - Une facture appartient à un client (Client)
 * - Une facture peut avoir plusieurs lignes (ErpInvoiceItem)
 * - Une facture est créée par un utilisateur (User)
 */
class ErpInvoice extends Model {
    use SoftDeletes, Auditable;
    protected $fillable = [
        'invoice_number','type','client_id','client_name',
        'invoice_date','due_date','total_ht','tax_amount','total_ttc',
        'status','created_by',
        'emecef_nim','emecef_compteur','emecef_hash','emecef_qr',
        'emecef_statut','emecef_datetime',
    ];
    protected $casts = [
        'invoice_date'=>'date','due_date'=>'date',
        'total_ht'=>'decimal:2','tax_amount'=>'decimal:2','total_ttc'=>'decimal:2',
        'emecef_compteur'=>'integer','emecef_datetime'=>'datetime',
    ];
    public function client()   { return $this->belongsTo(Client::class); }
    public function lineItems(){ return $this->hasMany(ErpInvoiceItem::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
