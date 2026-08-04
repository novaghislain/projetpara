<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Subscription (Abonnement).
 *
 * Gère les abonnements des cabinets à la plateforme.
 * Définit la formule souscrite, le statut, la période de validité,
 * le nombre maximal d'utilisateurs/clients autorisés, et les fonctionnalités
 * activées (features). Supporte l'intégration Stripe.
 * Table associée : `gel_subscriptions`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property string $formule Formule d'abonnement (basic, pro, enterprise)
 * @property string $statut Statut (actif, expire, suspendu)
 * @property \Carbon\Carbon $date_debut Date de début d'abonnement
 * @property \Carbon\Carbon $date_fin Date de fin d'abonnement
 * @property string|null $stripe_id ID de la souscription Stripe
 * @property float $montant Montant de l'abonnement
 * @property string $devise Devise (XOF, EUR, USD)
 * @property int $max_users Nombre max d'utilisateurs
 * @property int $max_clients Nombre max de clients
 * @property array $features Fonctionnalités activées (JSON)
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 */
class Subscription extends Model
{
    protected $table = 'gel_subscriptions';

    protected $fillable = [
        'cabinet_id', 'formule', 'statut', 'date_debut', 'date_fin',
        'stripe_id', 'montant', 'devise', 'max_users', 'max_clients', 'features',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant' => 'decimal:2',
        'features' => 'array',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }

    // ─── Scopes ───
    public function scopeActif($q) { return $q->where('statut', 'actif'); }
    public function scopeByFormule($q, $f) { return $q->where('formule', $f); }

    // ─── Helpers ───
    public function estActif(): bool { return $this->statut === 'actif'; }
    public function estExpire(): bool { return $this->date_fin && $this->date_fin->isPast(); }
    public function joursRestants(): int { return $this->date_fin ? now()->diffInDays($this->date_fin, false) : 0; }
}
