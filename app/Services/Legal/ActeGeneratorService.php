<?php

namespace App\Services\Legal;

use App\Models\Legal\LegalActsLibrary;
use App\Models\Legal\LegalAssembly;

class ActeGeneratorService
{
    /**
     * Génère un acte depuis un modèle de la bibliothèque.
     */
    public function generer(int $acteLibraryId, array $variables, int $clientId): string
    {
        $modele = LegalActsLibrary::findOrFail($acteLibraryId);

        $contenu = $modele->contenu;

        // Remplacer les variables {{...}}
        foreach ($variables as $key => $value) {
            $contenu = str_replace('{{' . $key . '}}', e($value), $contenu);
        }

        // Retirer les variables non remplacées
        $contenu = preg_replace('/\{\{[^}]+\}\}/', '', $contenu);

        // Ici, on pourrait générer un PDF via DomPDF
        // Pour l'instant, on retourne le HTML

        return $contenu;
    }

    /**
     * Retourne la liste des variables requises par un modèle.
     */
    public function getVariablesRequises(int $acteLibraryId): array
    {
        $modele = LegalActsLibrary::findOrFail($acteLibraryId);
        $variables = $modele->variables ?? [];

        // Extraire les variables {{...}} du contenu
        preg_match_all('/\{\{(\w+)\}\}/', $modele->contenu, $matches);

        $trouvees = array_unique($matches[1]);

        // Fusionner avec les descriptions existantes
        $result = [];
        foreach ($trouvees as $var) {
            $desc = '';
            foreach ($variables as $v) {
                if (isset($v['nom']) && $v['nom'] === $var) {
                    $desc = $v['description'] ?? '';
                    break;
                }
            }
            $result[] = [
                'nom' => $var,
                'description' => $desc,
                'requis' => true,
            ];
        }

        return $result;
    }

    /**
     * Génère automatiquement une convocation d'AG.
     */
    public function genererConvocationAG(LegalAssembly $ag): string
    {
        $societe = $ag->client?->company_name ?? 'la société';

        $variables = [
            'raison_sociale' => $societe,
            'type_ag' => $ag->type,
            'date' => $ag->date_tenue?->format('d/m/Y') ?? '',
            'lieu' => $ag->lieu,
            'heure' => '10h00',
            'ordre_du_jour' => implode("\n", array_map(function ($point, $i) {
                return ($i + 1) . '. ' . (is_string($point) ? $point : ($point['titre'] ?? $point['intitule'] ?? ''));
            }, $ag->ordre_du_jour ?? [], array_keys($ag->ordre_du_jour ?? []))),
        ];

        $contenu = view('legal.templates.convocation_ag', $variables)->render();

        return $contenu;
    }
}
