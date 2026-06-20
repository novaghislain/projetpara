<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
