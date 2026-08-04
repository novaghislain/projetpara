<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle LegalVeille (Veille juridique).
 *
 * Stocke les articles et informations de veille juridique
 * (nouvelles lois, règlements, jurisprudence) classés par catégorie
 * et niveau d'impact pour l'entreprise.
 * Table associée : `legal_veille`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $titre Titre de l'article
 * @property string|null $description Description
 * @property string|null $source Source de l'information
 * @property string|null $categorie Catégorie (fiscal, social, commercial, etc.)
 * @property \Carbon\Carbon|null $date_publication Date de publication
 * @property string|null $url URL de la source
 * @property string|null $impact Impact (faible, moyen, fort)
 * @property array|null $tags Tags (JSON)
 * @property bool $est_lu Si l'article a été lu
 * @property int $created_by ID du créateur
 */
class LegalVeille extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_veille';

    protected $fillable = [
        'client_id', 'titre', 'description', 'source',
        'categorie', 'date_publication', 'url',
        'impact', 'tags', 'est_lu', 'created_by',
    ];

    protected $casts = [
        'tags' => 'json',
        'date_publication' => 'date',
        'est_lu' => 'boolean',
    ];
}
