<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingEmballage extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_emballages';

    protected $fillable = [
        'client_id', 'type', 'tiers_nom', 'tiers_type',
        'produit', 'quantite', 'montant_consigne',
        'date_emission', 'date_retour', 'statut', 'notes',
        'created_by',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'montant_consigne' => 'decimal:2',
        'date_emission' => 'date',
        'date_retour' => 'date',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
    public function scopeActif($q) { return $q->where('statut', 'en_cours'); }
}
