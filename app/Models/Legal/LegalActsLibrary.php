<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle LegalActsLibrary (Bibliothèque d'actes juridiques).
 *
 * Stocke les modèles d'actes juridiques (statuts, contrats, PV, etc.)
 * avec leurs variables dynamiques, classés par catégorie et type de société.
 * Les actes peuvent être publics ou validés par un expert.
 * Table associée : `legal_acts_library`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $titre Titre de l'acte
 * @property string $categorie Catégorie (statuts, contrat, PV, etc.)
 * @property string|null $type_societe Type de société concernée (SA, SARL, SAS, etc.)
 * @property string $contenu Contenu du modèle d'acte
 * @property array|null $variables Variables dynamiques (JSON)
 * @property string|null $droit_applicable Droit applicable (OHADA, etc.)
 * @property string $version Version du document
 * @property bool $is_public Acte public ou privé
 * @property bool $is_validated Acte validé par un expert
 * @property int|null $validated_by ID du validateur
 * @property array|null $tags Tags (JSON)
 * @property int $created_by ID du créateur
 */
class LegalActsLibrary extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_acts_library';

    protected $fillable = [
        'client_id', 'titre', 'categorie', 'type_societe',
        'contenu', 'variables', 'droit_applicable',
        'version', 'is_public', 'is_validated',
        'validated_by', 'tags', 'created_by',
    ];

    protected $casts = [
        'variables' => 'json',
        'tags' => 'json',
        'is_public' => 'boolean',
        'is_validated' => 'boolean',
    ];
}
