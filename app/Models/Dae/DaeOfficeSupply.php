<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeOfficeSupply extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_office_supplies';

    protected $fillable = [
        'client_id', 'nom', 'description', 'reference', 'categorie',
        'quantite_stock', 'seuil_alerte', 'unite', 'prix_unitaire',
        'fournisseur', 'emplacement', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'decimal:2',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'fournitures';
    }

    // ─── Relations ────────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requests()
    {
        return $this->hasMany(DaeOfficeSupplyRequest::class, 'supply_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeEnAlerte($query)
    {
        return $query->whereColumn('quantite_stock', '<=', 'seuil_alerte');
    }

    public function scopeParCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    // ─── Accesseurs ────────────────────────────────────────

    public function getStockSuffisantAttribute(): bool
    {
        return $this->quantite_stock > $this->seuil_alerte;
    }
}
