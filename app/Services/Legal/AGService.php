<?php

namespace App\Services\Legal;

use App\Models\Legal\LegalAssembly;
use App\Models\Legal\LegalRegistre;
use App\Services\Legal\ActeGeneratorService;

class AGService
{
    protected ActeGeneratorService $acteGenerator;

    public function __construct(ActeGeneratorService $acteGenerator)
    {
        $this->acteGenerator = $acteGenerator;
    }

    /**
     * Prépare une AG : crée + génère convocation.
     */
    public function preparerAG(array $data): LegalAssembly
    {
        $ag = LegalAssembly::create($data);

        // Générer la convocation
        $convocation = $this->acteGenerator->genererConvocationAG($ag);

        // Marquer la convocation comme envoyée
        $ag->update(['convocation_envoyee' => true]);

        return $ag;
    }

    /**
     * Génère le PV d'une AG.
     */
    public function genererPV(LegalAssembly $ag, array $resolutions): string
    {
        $ag->update([
            'resolutions' => $resolutions,
            'pv_path' => 'legal/' . $ag->client_id . '/pv/ag_' . $ag->id . '.pdf',
        ]);

        return $ag->pv_path;
    }

    /**
     * Enregistre l'AG au registre des assemblées.
     */
    public function enregistrerAuRegistre(LegalAssembly $ag): void
    {
        $registre = LegalRegistre::firstOrCreate(
            [
                'client_id' => $ag->client_id,
                'type' => 'registre_assemblee',
                'annee' => $ag->annee,
            ],
            [
                'entrees' => [],
            ]
        );

        $entrees = $registre->entrees ?? [];
        $entrees[] = [
            'date' => $ag->date_tenue?->format('Y-m-d'),
            'type' => $ag->type,
            'description' => 'Assemblée Générale ' . $ag->type . ' du ' . $ag->date_tenue?->format('d/m/Y'),
            'assembly_id' => $ag->id,
        ];

        $registre->update(['entrees' => $entrees]);
    }
}
