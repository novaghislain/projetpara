<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FactureHonoraire extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'gel_factures_honoraires';

    protected $fillable = [
        'user_id',
        'client_id',
        'numero_facture',
        'date_facture',
        'date_echeance',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut',
        'notes',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Gel\Client::class, 'client_id');
    }
}
