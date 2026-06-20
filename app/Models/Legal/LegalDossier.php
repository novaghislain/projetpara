<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalDossier extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_dossiers';

    protected $fillable = [
        'client_id', 'reference', 'titre', 'type',
        'statut', 'priorite', 'description',
        'documents', 'dates',
        'assigned_to', 'created_by',
    ];

    protected $casts = [
        'documents' => 'json',
        'dates' => 'json',
    ];
}
