<?php

namespace App\Policies;

use App\Models\User;

class AiSuggestionPolicy
{
    /**
     * Voir les suggestions IA.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ia.consulter');
    }

    /**
     * Utiliser une suggestion IA.
     */
    public function apply(User $user): bool
    {
        return $user->hasPermissionTo('ia.suggestion');
    }

    /**
     * Utiliser l'analyse prédictive.
     */
    public function analyze(User $user): bool
    {
        return $user->hasPermissionTo('ia.analyse');
    }

    /**
     * Utiliser le rapprochement bancaire IA.
     */
    public function reconcile(User $user): bool
    {
        return $user->hasPermissionTo('ia.rapprochement');
    }

    /**
     * Utiliser l'OCR.
     */
    public function ocr(User $user): bool
    {
        return $user->hasPermissionTo('ia.ocr');
    }

    /**
     * Entraîner les modèles IA.
     */
    public function train(User $user): bool
    {
        return $user->hasPermissionTo('ia.entrainer');
    }
}
