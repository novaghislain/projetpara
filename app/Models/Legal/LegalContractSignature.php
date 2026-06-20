<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalContractSignature extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_contract_signatures';

    protected $fillable = [
        'contract_id',
        'signataire_nom', 'signataire_email', 'signataire_role',
        'statut', 'date_signature', 'signature_path',
    ];

    protected $casts = [
        'date_signature' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(LegalContract::class, 'contract_id');
    }
}
