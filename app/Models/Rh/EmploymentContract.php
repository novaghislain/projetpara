<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Model;

class EmploymentContract extends Model
{
    protected $fillable = [
        'client_id',
        'employee_name',
        'position', // Poste
        'contract_type', // CDI, CDD, Stage
        'start_date',
        'end_date',
        'gross_salary', // Salaire brut
        'content_html', // Contenu généré du contrat
        'status', // draft, signed, terminated
        'signed_at',
        'signature_path' // Chemin vers l'image de la signature (base64 ou fichier)
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
