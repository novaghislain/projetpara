<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'client_id', // L'entreprise utilisatrice de l'ERP
        'company_name',
        'contact_name',
        'email',
        'phone',
        'source', // web, referral, cold_call
        'status', // new, contacted, qualified, lost
        'notes'
    ];

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
