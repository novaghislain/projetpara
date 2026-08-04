<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle LegalDossier (Dossier juridique).
 *
 * Représente un dossier de suivi juridique avec ses documents associés,
 * dates importantes, priorité et statut d'avancement.
 * Table associée : `legal_dossiers`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $reference Référence du dossier
 * @property string $titre Titre du dossier
 * @property string $type Type (contentieux, conseil, fiscal, social, etc.)
 * @property string $statut Statut (ouvert, en_cours, cloturé)
 * @property string $priorite Priorité (basse, moyenne, haute, critique)
 * @property string|null $description Description du dossier
 * @property array|null $documents Documents associés (JSON)
 * @property array|null $dates Dates importantes (JSON)
 * @property int|null $assigned_to ID de l'assigné
 * @property int $created_by ID du créateur
 */
class LegalDossier extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_dossiers';

    protected $fillable = [
        'client_id', 'reference', 'titre', 'type',
        'statut', 'priorite', 'description',
        'documents', 'dates',
        'assigned_to', 'created_by',
    ];

    protected $casts = [
        'documents' => 'json',
        'dates' => 'json',
    ];
}
