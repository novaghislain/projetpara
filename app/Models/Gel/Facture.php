<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CompanyCrmContact;
use App\Models\Gel\EcritureComptable;

class Facture extends Model
{
    use SoftDeletes;

    protected $table = 'gel_factures';

    protected $fillable = [
        // Champs hérités du Design A — encore utilisés par le portail Gel Business
        // (VentesController / DepensesController). À retirer quand le double
        // portail sera consolidé (§2.3 du rapport de conformité).
        'type',
        'client_nom',
        'montant',
        // Champs du Design B (modèle actif / chaîne comptable §2.2)
        'client_id',
        'contact_id',
        'numero',
        'date_facture',
        'date_echeance',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut',
        'notes',
        'ecriture_id',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CompanyCrmContact::class, 'contact_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(FactureLigne::class, 'facture_id');
    }

    /**
     * Écriture comptable générée à la validation de la facture.
     * Rétablie (était commentée) — §2.2 du rapport de conformité : la
     * facture GEL crée bien une écriture côté contrôleur, le modèle doit
     * pouvoir y remonter pour éviter doublon ou écriture orpheline.
     */
    public function ecriture(): BelongsTo
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_id');
    }
}
