<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle LegalContractSignature (Signature de contrat).
 *
 * Enregistre les signatures électroniques apposées sur un contrat.
 * Chaque signature est associée à un signataire (nom, email, rôle)
 * avec son statut et la date de signature.
 * Table associée : `legal_contract_signatures`.
 *
 * @property int $id
 * @property int $contract_id ID du contrat
 * @property string $signataire_nom Nom du signataire
 * @property string $signataire_email Email du signataire
 * @property string|null $signataire_role Rôle du signataire
 * @property string $statut Statut (en_attente, signé, refusé)
 * @property \Carbon\Carbon|null $date_signature Date de signature
 * @property string|null $signature_path Chemin du fichier de signature
 *
 * @property-read \App\Models\Legal\LegalContract $contract Contrat associé
 */
class LegalContractSignature extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_contract_signatures';

    protected $fillable = [
        'contract_id',
        'signataire_nom', 'signataire_email', 'signataire_role',
        'statut', 'date_signature', 'signature_path',
    ];

    protected $casts = [
        'date_signature' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(LegalContract::class, 'contract_id');
    }
}
