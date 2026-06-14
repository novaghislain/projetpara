<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpInvoice extends Model {
    use SoftDeletes;
    protected $fillable = ['invoice_number','type','client_id','client_name','invoice_date','due_date','total_ht','tax_amount','total_ttc','status','created_by'];
    protected $casts = ['invoice_date'=>'date','due_date'=>'date','total_ht'=>'decimal:2','tax_amount'=>'decimal:2','total_ttc'=>'decimal:2'];
    public function client()   { return $this->belongsTo(Client::class); }
    public function lineItems(){ return $this->hasMany(ErpInvoiceItem::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}
