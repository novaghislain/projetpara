<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GelProduit extends Model
{
    use SoftDeletes;

    protected $table = 'gel_produits';

    protected $fillable = [
        'client_id',
        'nom',
        'description',
        'prix_unitaire',
        'est_service',
        'compte_comptable_id',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'est_service' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function compteComptable(): BelongsTo
    {
        return $this->belongsTo(CompteComptable::class, 'compte_comptable_id');
    }
}
