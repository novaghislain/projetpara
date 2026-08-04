<?php

namespace App\Observers\Gel;

use App\Models\Gel\EcritureComptable;
use App\Services\AuditService;

class EcritureObserver
{
    /**
     * Handle the EcritureComptable "created" event.
     */
    public function created(EcritureComptable $ecriture): void
    {
        $newValues = $ecriture->getAttributes();
        unset($newValues['created_at'], $newValues['updated_at']);

        AuditService::log('created', $ecriture, null, $newValues, "Création de l'écriture comptable {$ecriture->numero} : {$ecriture->libelle}");
    }

    /**
     * Handle the EcritureComptable "updated" event.
     */
    public function updated(EcritureComptable $ecriture): void
    {
        // On évite de logger si seulement valide, valide_at, valide_par changent (géré par 'validated')
        $changes = $ecriture->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        // Si c'est juste la validation qui change, on laisse l'action manuelle du controller s'en charger
        if (count($changes) === 1 && isset($changes['valide'])) {
            return;
        }
        if (isset($changes['valide']) && isset($changes['valide_at'])) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changes as $key => $newValue) {
            $oldValues[$key] = $ecriture->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        AuditService::log('updated', $ecriture, $oldValues, $newValues, "Modification de l'écriture comptable {$ecriture->numero}");
    }

    /**
     * Handle the EcritureComptable "deleted" event.
     */
    public function deleted(EcritureComptable $ecriture): void
    {
        $oldValues = $ecriture->getAttributes();
        unset($oldValues['created_at'], $oldValues['updated_at']);

        AuditService::log('deleted', $ecriture, $oldValues, null, "Suppression de l'écriture comptable {$ecriture->numero}");
    }
}
