<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle ItKnowledgeBase (Base de connaissances IT).
 *
 * Stocke les articles de la base de connaissances pour le support IT.
 * Les articles peuvent être classés par catégorie, tagués, et rendus
 * publics ou internes. Comptabilise le nombre de vues.
 * Table associée : `it_knowledge_base`.
 *
 * @property int $id
 * @property string $title Titre de l'article
 * @property string $slug Slug unique pour l'URL
 * @property string $category Catégorie
 * @property string $content Contenu de l'article (HTML/Markdown)
 * @property array|null $tags Tags (JSON)
 * @property bool $is_public Si l'article est public
 * @property int $views Nombre de vues
 * @property int $created_by ID de l'auteur
 *
 * @property-read \App\Models\User|null $author Auteur de l'article
 */
class ItKnowledgeBase extends Model
{
    protected $table = 'it_knowledge_base';

    protected $fillable = [
        'title', 'slug', 'category', 'content', 'tags',
        'is_public', 'views', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'json',
            'is_public' => 'boolean',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
