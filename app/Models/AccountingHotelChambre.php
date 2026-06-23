<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingHotelChambre extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_hotel_chambres';
    protected $fillable = ['client_id','numero_chambre','type','categorie','prix_nuitee','capacite','etage','statut','equipements','notes','is_active','created_by'];
    protected $casts = ['prix_nuitee'=>'decimal:2','equipements'=>'array','is_active'=>'boolean'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
