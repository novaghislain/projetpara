<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientFolder extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'path',
        'level',
        'parent_id',
        'sort_order',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'sort_order' => 'integer',
            'level' => 'integer',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent()
    {
        return $this->belongsTo(ClientFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ClientFolder::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    public function allDescendants()
    {
        return $this->children()->with('allDescendants');
    }

    // Scope pour les dossiers racine (sans parent)
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope par niveau
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Scope par client
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Obtenir le chemin complet affichable
    public function getDisplayPathAttribute(): string
    {
        return $this->path ?? $this->name;
    }

    // Vérifier si le dossier a des sous-dossiers
    public function getHasChildrenAttribute(): bool
    {
        return $this->relationLoaded('children') ? $this->children->isNotEmpty() : false;
    }
}
