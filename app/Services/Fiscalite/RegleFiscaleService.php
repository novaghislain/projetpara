<?php

namespace App\Services\Fiscalite;

use App\Models\RegleFiscale;
use Illuminate\Support\Carbon;

class RegleFiscaleService
{
    /**
     * Résout la règle fiscale en vigueur pour un pays, un type d'impôt et une date donnée.
     * Prend optionnellement en compte des conditions spécifiques (ex: type de centre d'impôts pour l'AIB).
     */
    public function resoudre(string $codePays, string $typeImpot, array $conditionsClient = [], ?Carbon $date = null): ?RegleFiscale
    {
        $date = $date ?? now();

        $query = RegleFiscale::where('code_pays', strtoupper($codePays))
            ->where('type_impot', strtoupper($typeImpot))
            ->where('statut', 'active')
            ->where('date_debut_validite', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('date_fin_validite')
                  ->orWhere('date_fin_validite', '>=', $date->toDateString());
            })
            ->orderBy('version', 'desc');

        $regles = $query->get();

        if ($regles->isEmpty()) {
            return null;
        }

        // Si on a des conditions (ex: centre_impots_rattachement = DGE)
        // On cherche d'abord une règle conditionnelle qui matche exactement
        if (!empty($conditionsClient)) {
            foreach ($regles as $regle) {
                if ($regle->conditions && is_array($regle->conditions)) {
                    $match = true;
                    foreach ($regle->conditions as $key => $value) {
                        if (!isset($conditionsClient[$key]) || $conditionsClient[$key] !== $value) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        return $regle;
                    }
                }
            }
        }

        // Sinon, on retourne la règle générale (celle sans conditions) la plus récente
        return $regles->firstWhere('conditions', null);
    }
}
