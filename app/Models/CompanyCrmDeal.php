<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCrmDeal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'contact_id',
        'title',
        'description',
        'amount',
        'stage',
        'status',
        'probability',
        'expected_close_date',
        'closed_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Contact associé à l'affaire.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(CompanyCrmContact::class, 'contact_id');
    }

    /**
     * Interactions liées à cette affaire.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(CompanyCrmInteraction::class, 'deal_id');
    }

    /**
     * Utilisateur ayant créé l'affaire.
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
