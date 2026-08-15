<?php

namespace App\Services\Gel;

use App\Models\Gel\Client;
use App\Models\Gel\Conformite;

class ConformiteScoreService
{
    /**
     * Calcule et enregistre le Score de Conformité GEL® pour un client.
     * Score sur 100 = moyenne pondérée des 10 obligations.
     */
    public static function calculer(Client $client): int
    {
        $items = Conformite::where('client_id', $client->id)->get();

        if ($items->isEmpty()) {
            // Initialiser les obligations par défaut si elles n'existent pas
            self::initialiserObligations($client);
            $items = Conformite::where('client_id', $client->id)->get();
        }

        $total  = $items->count() * 10; // max possible (10 pts par item)
        $obtenu = $items->sum('score_value');

        $score = $total > 0 ? (int) round(($obtenu / $total) * 100) : 0;

        $client->update([
            'score_conformite'  => $score,
            'score_calcule_at'  => now(),
        ]);

        return $score;
    }

    /**
     * Crée les obligations de conformité standard pour un nouveau client.
     */
    public static function initialiserObligations(Client $client): void
    {
        foreach (Conformite::$CODES as $code => $info) {
            Conformite::firstOrCreate(
                ['client_id' => $client->id, 'code' => $code],
                [
                    'categorie'   => $info['categorie'],
                    'titre'       => $info['titre'],
                    'statut'      => 'ko',
                ]
            );
        }
    }

    /**
     * Retourne la couleur du badge selon le score.
     */
    public static function badgeColor(int $score): string
    {
        if ($score >= 80) return '#10b981'; // vert
        if ($score >= 50) return '#f59e0b'; // orange
        return '#ef4444';                   // rouge
    }

    /**
     * Retourne le libellé du niveau selon le score.
     */
    public static function badgeLabel(int $score): string
    {
        if ($score >= 80) return 'Conforme';
        if ($score >= 50) return 'Partiel';
        return 'Non conforme';
    }

    /**
     * Retourne les items expirés ou expirant bientôt (<=30j).
     */
    public static function alertesExpiration(Client $client): \Illuminate\Support\Collection
    {
        return Conformite::where('client_id', $client->id)
            ->whereNotNull('date_expiration')
            ->where('date_expiration', '<=', now()->addDays(30))
            ->orderBy('date_expiration')
            ->get();
    }
}
