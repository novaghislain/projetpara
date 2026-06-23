<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingTontine extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_tontines';
    protected $fillable = ['client_id','nom_groupe','description','nb_membres','montant_cotisation','frequence','montant_caisse','date_creation','statut','regles','created_by'];
    protected $casts = ['nb_membres'=>'integer','montant_cotisation'=>'decimal:2','montant_caisse'=>'decimal:2','date_creation'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
