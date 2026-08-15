<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalContractSignature extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_contract_signatures';

    protected $fillable = [
        'contract_id',
        'signataire_nom',
        'signataire_email',
        'signed_at',
        'statut'
    ];

    public function contract()
    {
        return $this->belongsTo(LegalContract::class, 'contract_id', 'id');
    }

}
