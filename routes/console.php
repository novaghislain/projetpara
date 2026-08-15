<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────────────────────────────────────
// Planification CDC §13.4 — nouvelles commandes artisan
// (horaires conformes au tableau §13.4 du cahier des charges)
// ─────────────────────────────────────────────────────────────────────────────

// ProcessRelances — quotidienne à 09:00 (alias relance:send)
Artisan::command('relances:process', function () {
    $this->call('relance:send');
})->purpose('ProcessRelances — Relances automatiques clients impayés (CDC 13.4)');

// ProcessRecurringTransactions — quotidienne à 06:00
// écritures programmées + rappels (commande : recurring:process)
Schedule::command('recurring:process')->dailyAt('06:00');

// ProcessRelances — quotidienne à 09:00
Schedule::command('relance:send')->dailyAt('09:00');

// ProcessAiAgentOhada — horaire (analyse et suggestions Agent OHADA)
Schedule::command('ai:agent-ohada')->hourly();

// ProcessAiAgentFiscal — quotidienne à 07:00 (alertes échéances + pré-remplissage TVA)
Schedule::command('ai:agent-fiscal')->dailyAt('07:00');

// ProcessAiAgentRelance — quotidienne à 08:00 (analyse des impayés + suggestions)
Schedule::command('ai:agent-relance')->dailyAt('08:00');

// ProcessAiAgentRapprochement — quotidienne à 08:30 (écarts + lettrage 401/411)
Schedule::command('ai:agent-rapprochement')->dailyAt('08:30');

// ProcessAiAgentOcr — quotidienne à 08:45 (documents à numériser)
Schedule::command('ai:agent-ocr')->dailyAt('08:45');

// ProcessAiAgentTresorerie — quotidienne à 09:30 (prévisions de trésorerie)
Schedule::command('ai:agent-tresorerie')->dailyAt('09:30');

// CheckItAssetAlerts — hebdomadaire (lundi à 08:00) garanties + licences expirant
Schedule::command('it:asset-alerts')->weeklyOn(1, '08:00');

// ── Planification existante (hors §13.4) ──
Schedule::job(new \App\Jobs\DailyDigestJob)->dailyAt('07:00');
Schedule::job(new \App\Jobs\TaskEscalationJob)->hourly();
Schedule::job(new \App\Jobs\ContactBirthdayJob)->dailyAt('08:00');
Schedule::command('documents:check-expirations')->dailyAt('09:00');
Schedule::command('agenda:send-reminders')->dailyAt('08:30');
