<?php

namespace App\Services\IA;

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

/**
 * Orchestrateur des 6 agents IA — §4.23 du cahier des charges.
 *
 * Point d'entrée unique pour lancer les agents GEL Intelligence :
 * ohada, fiscal, rapprochement, relance, ocr, tresorerie.
 * Chaque agent est une commande artisan dédiée ; l'orchestrateur les
 * exécute pour un client précis (--client-id) ou pour tous les clients actifs.
 *
 * Les commandes des agents Rapprochement, OCR et Trésorerie sont créées
 * au fil des corrections (§2.1) — l'orchestrateur signale proprement
 * une commande non encore enregistrée au lieu d'échouer.
 */
class GelIntelligenceService
{
    /**
     * Index des commandes artisan enregistrées (signature => instance).
     */
    private array $commands = [];

    public function __construct(ConsoleKernel $kernel)
    {
        $this->commands = $kernel->all();
    }

    /**
     * Vérifie qu'une commande artisan est enregistrée.
     */
    private function hasCommand(string $signature): bool
    {
        return isset($this->commands[$signature]);
    }

    /**
     * Les 6 agents IA (§4.23.1 → 4.23.6) et leur commande artisan.
     */
    public const AGENTS = [
        'ohada'         => 'ai:agent-ohada',
        'fiscal'        => 'ai:agent-fiscal',
        'rapprochement' => 'ai:agent-rapprochement',
        'relance'       => 'ai:agent-relance',
        'ocr'           => 'ai:agent-ocr',
        'tresorerie'    => 'ai:agent-tresorerie',
    ];

    /**
     * Liste des noms d'agents disponibles (les 6 du CDC).
     */
    public function agents(): array
    {
        return array_keys(self::AGENTS);
    }

    /**
     * Exécute un agent pour un client précis (ou tous les clients actifs).
     *
     * @param  string      $agent    Nom de l'agent ('ohada', 'fiscal', ...)
     * @param  int|null    $clientId Client cible, ou null pour tous les clients actifs
     * @return array       ['agent', 'success', 'output'|'error']
     */
    public function runAgent(string $agent, ?int $clientId = null): array
    {
        $command = self::AGENTS[$agent] ?? null;

        if (!$command) {
            return ['agent' => $agent, 'success' => false, 'error' => "Agent inconnu. Attendus : " . implode(', ', $this->agents()) . '.'];
        }

        if (!$this->hasCommand($command)) {
            return ['agent' => $agent, 'success' => false, 'error' => "Commande {$command} non enregistrée (agent à livrer)."];
        }

        $params = $clientId ? ['--client-id' => $clientId] : [];
        $exitCode = Artisan::call($command, $params);

        return [
            'agent'   => $agent,
            'success' => $exitCode === 0,
            'output'  => trim(Artisan::output()),
        ];
    }

    /**
     * Exécute les 6 agents pour un client précis (ou tous les clients actifs).
     *
     * @param  int|null $clientId Client cible, ou null pour tous les clients actifs
     * @return array    Liste des résultats par agent
     */
    public function runAll(?int $clientId = null): array
    {
        $results = [];
        foreach (array_keys(self::AGENTS) as $agent) {
            $results[] = $this->runAgent($agent, $clientId);
        }
        return $results;
    }
}
