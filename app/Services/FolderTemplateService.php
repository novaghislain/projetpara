<?php

namespace App\Services;

use App\Models\ClientFolder;
use Illuminate\Support\Str;

class FolderTemplateService
{
    /**
     * Génère la structure de base standard (EDEN STORE) pour un client.
     */
    public function generateStandardStructure($clientId, $year = null)
    {
        $year = $year ?? date('Y');

        // DOSSIER RACINE : L'ANNÉE
        $rootYear = $this->createFolder($clientId, (string) $year, null, 1, (int) $year);

        // 1. ADMINISTRATIF
        $admin = $this->createFolder($clientId, '1. ADMINISTRATIF', $rootYear->id, 2, 1);
        $this->createFolder($clientId, '01 Analyse TECS', $admin->id, 3, 1);
        $this->createFolder($clientId, '02 Facturation', $admin->id, 3, 2);
        $this->createFolder($clientId, '03 Lettre de mission (LMG)', $admin->id, 3, 3);
        $this->createFolder($clientId, '04 Offres de services', $admin->id, 3, 4);
        $this->createFolder($clientId, '05 Analyse volumétrie', $admin->id, 3, 5);
        $this->createFolder($clientId, '06 Soumissions TDL', $admin->id, 3, 6);
        $this->createFolder($clientId, '99 Divers administratifs', $admin->id, 3, 99);

        // 2. COURANT
        $courant = $this->createFolder($clientId, '2. COURANT', $rootYear->id, 2, 2);
        
        // 1 DOCUMENTS DU CLIENT
        $docsClient = $this->createFolder($clientId, '1 DOCUMENTS DU CLIENT', $courant->id, 3, 1);
        $this->createFolder($clientId, 'États financiers reçus', $docsClient->id, 4, 1);
        $this->createFolder($clientId, 'Déclarations fiscales reçues', $docsClient->id, 4, 2);
        $this->createFolder($clientId, 'Contrats', $docsClient->id, 4, 3);
        $this->createFolder($clientId, 'Documents corporatifs', $docsClient->id, 4, 4);
        $this->createFolder($clientId, 'Divers', $docsClient->id, 4, 5);

        // 2 TENUE DE LIVRES
        $tdl = $this->createFolder($clientId, '2 TENUE DE LIVRES', $courant->id, 3, 2);
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $monthFolder = $this->createFolder($clientId, "{$year}-{$month}", $tdl->id, 4, $i);
            $this->createFolder($clientId, '01 Documents du client', $monthFolder->id, 5, 1);
            $this->createFolder($clientId, '02 Banque', $monthFolder->id, 5, 2);
            $this->createFolder($clientId, '03 Carte de crédit', $monthFolder->id, 5, 3);
            $this->createFolder($clientId, '04 Revenus', $monthFolder->id, 5, 4);
            $this->createFolder($clientId, '05 Dépenses', $monthFolder->id, 5, 5);
            $this->createFolder($clientId, '06 Balance de vérification', $monthFolder->id, 5, 6);
            $this->createFolder($clientId, '07 Révision mensuelle', $monthFolder->id, 5, 7);
            $this->createFolder($clientId, '08 Écritures de régularisation', $monthFolder->id, 5, 8);
        }
        $this->createFolder($clientId, 'TPS-TVQ', $tdl->id, 4, 13);
        $this->createFolder($clientId, 'Paies', $tdl->id, 4, 14);
        $this->createFolder($clientId, 'Conciliations', $tdl->id, 4, 15);
        $this->createFolder($clientId, 'Révisions mensuelles', $tdl->id, 4, 16);

        // 3 FISCAL
        $fiscal = $this->createFolder($clientId, '3 FISCAL', $courant->id, 3, 3);
        $this->createFolder($clientId, 'Déclarations fiscales', $fiscal->id, 4, 1);
        $this->createFolder($clientId, 'Estimations', $fiscal->id, 4, 2);
        $this->createFolder($clientId, 'Correspondances fiscales', $fiscal->id, 4, 3);
        $this->createFolder($clientId, 'Autres documents fiscaux', $fiscal->id, 4, 4);

        // 4 CASEWARE
        $caseware = $this->createFolder($clientId, '4 CASEWARE', $courant->id, 3, 4);
        $this->createFolder($clientId, 'Fichiers de travail', $caseware->id, 4, 1);
        $this->createFolder($clientId, 'États financiers', $caseware->id, 4, 2);
        $this->createFolder($clientId, 'Papier de travail', $caseware->id, 4, 3);
        $this->createFolder($clientId, 'Rapports', $caseware->id, 4, 4);

        // 5 DOCUMENTS FINAUX
        $finaux = $this->createFolder($clientId, '5 DOCUMENTS FINAUX', $courant->id, 3, 5);
        $this->createFolder($clientId, '01 États financiers', $finaux->id, 4, 1);
        $this->createFolder($clientId, '02 Déclarations fiscales', $finaux->id, 4, 2);
        $this->createFolder($clientId, '03 Documents fiscaux à signer', $finaux->id, 4, 3);
        $this->createFolder($clientId, '04 Bordereaux de paie', $finaux->id, 4, 4);
        $this->createFolder($clientId, '05 Écritures de régularisation', $finaux->id, 4, 5);
        $this->createFolder($clientId, '06 Balance finale', $finaux->id, 4, 6);

        // 3. PERMANENT
        $permanent = $this->createFolder($clientId, '3. PERMANENT', $rootYear->id, 2, 3);
        $this->createFolder($clientId, '01 Constitution', $permanent->id, 3, 1);
        $this->createFolder($clientId, '02 Registre des entreprises', $permanent->id, 3, 2);
        $this->createFolder($clientId, '03 REQ', $permanent->id, 3, 3);
        $this->createFolder($clientId, '04 Numéros fiscaux', $permanent->id, 3, 4);
        $this->createFolder($clientId, '05 Connaissance du client', $permanent->id, 3, 5);
        $this->createFolder($clientId, '06 Lettres de mission', $permanent->id, 3, 6);
        $this->createFolder($clientId, '07 Assurances', $permanent->id, 3, 7);
        $this->createFolder($clientId, '99 Divers', $permanent->id, 3, 99);

        // 4. SPÉCIAL
        $special = $this->createFolder($clientId, '4. SPÉCIAL', $rootYear->id, 2, 4);
        $this->createFolder($clientId, 'Changement adresse', $special->id, 3, 1);
    }

    private function createFolder($clientId, $name, $parentId, $level, $sortOrder)
    {
        // On évite les doublons
        $existing = ClientFolder::where('client_id', $clientId)
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->first();

        if ($existing) {
            return $existing;
        }

        $path = $name;
        if ($parentId) {
            $parent = ClientFolder::find($parentId);
            if ($parent) {
                $path = $parent->path . ' / ' . $name;
            }
        }

        return ClientFolder::create([
            'client_id' => $clientId,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'path' => $path,
            'level' => $level,
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'is_system' => true,
        ]);
    }
}
