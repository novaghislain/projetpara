<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Modèle représentant un article de blog.
 *
 * Gère les articles publiés sur le site vitrine de l'application.
 * Chaque article a un titre, un contenu et peut être categorisé.
 * Inclut la gestion du slug, des tags et de la publication.
 *
 * @property int $id
 * @property string $title Titre de l'article
 * @property string $slug Slug pour l'URL
 * @property string $content Contenu de l'article
 * @property string|null $excerpt Extrait ou résumé
 * @property string|null $category Catégorie
 * @property array|null $tags Étiquettes (JSON)
 * @property string|null $author Auteur de l'article
 * @property int|null $reading_minutes Temps de lecture estimé
 * @property bool $is_published Indique si l'article est publié
 * @property string|null $published_at Date de publication
 * @property string|null $featured_image Image à la une
 * @property string|null $meta_description Description meta SEO
 *
 * @table articles
 */
class Article extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'category',
        'tags', 'author', 'reading_minutes', 'is_published',
        'published_at', 'featured_image', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'json',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($article) {
            if (!$article->slug) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true)->whereNotNull('published_at');
    }

    // ── API accessors ─────────────────────────────────────
    public function getCategoryNameAttribute(): ?string
    {
        return $this->category;
    }

    public function getAuthorNameAttribute(): ?string
    {
        return $this->author;
    }

    public function getStatusAttribute(): string
    {
        return $this->is_published ? 'published' : 'draft';
    }

    protected $appends = ['category_name', 'author_name', 'status'];
}
