<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une interaction CRM (appel, email, rendez-vous, etc.).
 *
 * Table associée : `company_crm_interactions` (via convention Laravel)
 *
 * Relations :
 * - Une interaction appartient à un contact (CompanyCrmContact)
 * - Une interaction peut être liée à une affaire (CompanyCrmDeal)
 * - Une interaction est créée par un utilisateur (User)
 */
class CompanyCrmInteraction extends Model
{
    protected $fillable = [
        'client_id',
        'contact_id',
        'deal_id',
        'type',
        'subject',
        'description',
        'scheduled_at',
        'outcome',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Contact associé à l'interaction.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(CompanyCrmContact::class, 'contact_id');
    }

    /**
     * Affaire associée à l'interaction.
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(CompanyCrmDeal::class, 'deal_id');
    }

    /**
     * Utilisateur ayant créé l'interaction.
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
