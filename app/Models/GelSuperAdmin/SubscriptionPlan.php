<?php

namespace App\Models\GelSuperAdmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle SubscriptionPlan — Forfaits tarifaires GEL SABINET.
 *
 * Le champ `profile_type` est OBLIGATOIRE pour distinguer les grilles tarifaires :
 * - 'entreprise'              : plan pour Entreprise Cliente (Modèle 1 ou 2)
 * - 'secretaire_independant'  : plan pour Secrétaire Autonome (Modèle 3A)
 * - 'comptable_independant'   : plan pour Comptable Autonome (Modèle 3B)
 * - 'gel_pool'                : plan interne pour personnel GEL SABINET
 *
 * RÈGLE DE SÉCURITÉ : ne jamais afficher un plan 'secretaire_independant'
 * dans le formulaire d'inscription d'un comptable, et vice-versa.
 *
 * Les montants des plans 3A et 3B doivent être validés par l'administrateur
 * avant d'être fixés — ils ne sont pas définis par le développeur.
 */
class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'max_users',
        'ia_quota',
        'is_active',
        'profile_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
    ];

    // ─── Scopes ─────────────────────────────────────────────────────────

    /** Plans destinés aux entreprises clientes. */
    public function scopeForEntreprises($q) { return $q->where('profile_type', 'entreprise'); }

    /** Plans destinés aux secrétaires indépendants (Modèle 3A). */
    public function scopeForSecretairesIndependants($q) { return $q->where('profile_type', 'secretaire_independant'); }

    /** Plans destinés aux comptables indépendants (Modèle 3B). */
    public function scopeForComptablesIndependants($q) { return $q->where('profile_type', 'comptable_independant'); }

    /** Plans actifs uniquement. */
    public function scopeActifs($q) { return $q->where('is_active', true); }
}
