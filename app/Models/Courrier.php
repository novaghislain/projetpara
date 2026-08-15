<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Courrier extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'numero_enregistrement',
        'type',
        'objet',
        'expediteur_destinataire',
        'categorie',
        'priorite',
        'statut',
        'assigne_a',
        'cree_par',
        'date_reception_envoi',
    ];

    protected $casts = [
        'date_reception_envoi' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($courrier) {
            if (empty($courrier->numero_enregistrement)) {
                $courrier->numero_enregistrement = self::generateSequentialNumber($courrier->client_id);
            }
        });
    }

    /**
     * Génère un numéro séquentiel [Entité]-[Année]-[Séquence]
     */
    public static function generateSequentialNumber($clientId)
    {
        $year = date('Y');
        
        // On récupère le nom court ou un préfixe de l'entreprise
        $entreprise = Entreprise::find($clientId);
        $prefix = 'GEL';
        
        if ($entreprise && $entreprise->nom) {
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $entreprise->nom), 0, 3));
            if (empty($prefix)) {
                $prefix = 'GEL';
            }
        }

        $lastCourrier = self::where('client_id', $clientId)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastCourrier && preg_match('/-(\d+)$/', $lastCourrier->numero_enregistrement, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    // Relations
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'client_id');
    }

    public function assigneA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigne_a');
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'courrier_documents', 'courrier_id', 'document_id')
                    ->withTimestamps();
    }
}
