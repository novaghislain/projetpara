<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingTransitDossier extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_transit_dossiers';
    protected $fillable = [
        'client_id','reference_dossier','type_transit','fournisseur_nom','client_nom',
        'marchandise','valeur_marchandise','fret_ht','droits_douane','tva_douane',
        'frais_accessoires','total_facture','montant_paye','solde','statut',
        'date_ouverture','date_cloture','douane_bureau','numero_declaration','notes','created_by',
    ];
    protected $casts = [
        'date_ouverture' => 'date', 'date_cloture' => 'date',
        'valeur_marchandise' => 'decimal:2', 'fret_ht' => 'decimal:2',
        'droits_douane' => 'decimal:2', 'tva_douane' => 'decimal:2',
        'frais_accessoires' => 'decimal:2', 'total_facture' => 'decimal:2',
        'montant_paye' => 'decimal:2', 'solde' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
