<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalContract extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_contracts';

    protected $fillable = [
        'client_id',
        'intitule',
        'type',
        'date_signature',
        'date_expiration',
        'statut',
        'fichier_path'
    ];

    public function legalContractSignatures()
    {
        return $this->hasMany(LegalContractSignature::class, 'contract_id', 'id');
    }

}
