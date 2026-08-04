<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une demande de contact / devis d'un prospect.
 *
 * Table associée : `company_requests` (via convention Laravel)
 *
 * Ce modèle stocke les demandes entrantes de prospects souhaitant
 * être contactés ou obtenir un devis pour les services de l'application.
 */
class CompanyRequest extends Model
{
    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'message',
        'requested_services',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_services' => 'array',
        ];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
