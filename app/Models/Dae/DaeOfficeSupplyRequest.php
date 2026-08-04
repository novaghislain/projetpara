<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant une demande de fourniture de bureau (DAE).
 *
 * Table associée : `dae_office_supply_requests`
 *
 * Permet de gérer les demandes internes de fournitures avec suivi
 * du statut (en attente, approuvée, refusée) et validation par un approuveur.
 */
class DaeOfficeSupplyRequest extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_office_supply_requests';

    protected $fillable = [
        'client_id', 'supply_id', 'quantite_demandee', 'quantite_approuvee',
        'motif', 'statut', 'demande_par', 'approuve_par',
        'approuve_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'approuve_at' => 'datetime',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'demandes_fournitures';
    }

    // ─── Relations ────────────────────────────────────────

    public function supply()
    {
        return $this->belongsTo(DaeOfficeSupply::class, 'supply_id');
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demande_par');
    }

    public function approuveur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouvees($query)
    {
        return $query->where('statut', 'approuvee');
    }
}
