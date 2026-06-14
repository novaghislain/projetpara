<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCrmContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'position',
        'category',
        'notes',
        'tags',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'json',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Affaires liées à ce contact.
     */
    public function deals(): HasMany
    {
        return $this->hasMany(CompanyCrmDeal::class, 'contact_id');
    }

    /**
     * Interactions liées à ce contact.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(CompanyCrmInteraction::class, 'contact_id');
    }

    /**
     * Utilisateur ayant créé le contact.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
