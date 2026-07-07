@extends('layouts.gel')

@section('title', 'Finance Agent - GEL Cabinet')

@section('styles')
<style>
    .metric-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        padding: 1.25rem;
        transition: all 0.2s;
    }
    .metric-card:hover { border-color: var(--gel-accent-2); box-shadow: 0 2px 8px rgba(99,91,255,0.08); }
    .metric-card .metric-value { font-size: 1.6rem; font-weight: 800; color: var(--gel-primary); }
    .metric-card .metric-label { font-size: 0.8rem; color: var(--gel-text-muted); margin-top: 0.15rem; }
    .metric-card .metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 0.75rem;
    }
    .forecast-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .forecast-card .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gel-border);
        background: #fafbfc;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .forecast-card .card-body { padding: 1.5rem; }
    .forecast-month {
        padding: 1rem;
        border-radius: 10px;
        background: #f8f9fa;
        margin-bottom: 0.75rem;
        border-left: 4px solid var(--gel-accent-2);
    }
    .forecast-month:last-child { margin-bottom: 0; }
    .alert-item {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-left: 3px solid;
    }
    .alert-item.haute { background: #fef2f2; border-left-color: #EF4444; }
    .alert-item.moyenne { background: #fffbeb; border-left-color: #F59E0B; }
    .alert-item.info { background: #eff6ff; border-left-color: #3B82F6; }
    .rec-card {
        padding: 0.75rem;
        border-radius: 8px;
        background: #f8f9fa;
        border-left: 3px solid var(--gel-accent-2);
        margin-bottom: 0.5rem;
    }
    .tab-btn {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--gel-text-muted);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }
    .tab-btn.active { color: var(--gel-accent-2); border-bottom-color: var(--gel-accent-2); }
    .tab-btn:hover { color: var(--gel-primary); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Finance Agent</h1>
        <p class="page-subtitle">Agent intelligent pour l'analyse financière et la trésorerie</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" onclick="rafraichirDonnees()">
            <i class="bi bi-arrow-repeat me-1"></i>Rafraîchir
        </button>
    </div>
</div>

{{-- Métriques clés --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(16,185,129,0.1);color:#10B981;"><i class="bi bi-cash-stack"></i></div>
            <div class="metric-value" id="tresorerieValue">{{ $stats['tresorerie']['solde_formatted'] ?? '0 FCFA' }}</div>
            <div class="metric-label">Trésorerie actuelle</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(99,91,255,0.1);color:var(--gel-accent-2);"><i class="bi bi-graph-up"></i></div>
            <div class="metric-value">{{ $stats['chiffre_affaires']['montant_formatted'] ?? '0 FCFA' }}</div>
            <div class="metric-label">Chiffre d'affaires</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(16,185,129,0.1);color:#10B981;"><i class="bi bi-piggy-bank"></i></div>
            <div class="metric-value" style="color:{{ ($stats['rentabilite']['resultat'] ?? 0) >= 0 ? '#10B981' : '#EF4444' }};">
                {{ $stats['rentabilite']['resultat_formatted'] ?? '0 FCFA' }}
            </div>
            <div class="metric-label">Résultat net</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(245,158,11,0.1);color:#F59E0B;"><i class="bi bi-people"></i></div>
            <div class="metric-value">{{ $stats['clients']['total_clients'] ?? 0 }}</div>
            <div class="metric-label">Clients ({{ $stats['clients']['impayes_formatted'] ?? '0 FCFA' }} impayés)</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Prévisions trésorerie --}}
    <div class="col-lg-7">
        <div class="forecast-card">
            <div class="card-header">
                <span><i class="bi bi-graph-up-arrow me-1"></i>Prévisions de trésorerie</span>
                <button class="btn btn-sm btn-outline-primary" onclick="chargerPrevisions()">
                    <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                </button>
            </div>
            <div class="card-body" id="previsionsContainer">
                <div class="text-center text-muted py-3">
                    <span class="spinner-border spinner-border-sm me-2"></span>Chargement des prévisions...
                </div>
            </div>
        </div>

        <div class="forecast-card">
            <div class="card-header">
                <span><i class="bi bi-shield-exclamation me-1"></i>Alertes en temps réel</span>
            </div>
            <div class="card-body" id="alertsContainer">
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle d-block mb-2" style="font-size:1.5rem;"></i>
                    Aucune alerte pour le moment
                </div>
            </div>
        </div>
    </div>

    {{-- Analyse et recommandations --}}
    <div class="col-lg-5">
        <div class="forecast-card">
            <div class="card-header">
                <span><i class="bi bi-bar-chart me-1"></i>Analyse de rentabilité</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:0.85rem;color:var(--gel-text-muted);">Marge nette</span>
                        <span style="font-weight:700;">{{ $stats['rentabilite']['marge'] ?? 'N/A' }}</span>
                    </div>
                    <div class="risk-meter">
                        <div class="fill" style="width:{{ $stats['rentabilite']['resultat'] ?? 0 > 0 ? '60' : '30' }}%;background:{{ ($stats['rentabilite']['resultat'] ?? 0) >= 0 ? '#10B981' : '#EF4444' }};"></div>
                    </div>
                </div>

                <div style="font-weight:600;font-size:0.85rem;margin-bottom:0.5rem;">Top 5 charges</div>
                <div id="topCharges">
                    <div class="text-muted" style="font-size:0.85rem;">Données non disponibles</div>
                </div>
            </div>
        </div>

        <div class="forecast-card">
            <div class="card-header">
                <span><i class="bi bi-lightbulb me-1"></i>Recommandations</span>
            </div>
            <div class="card-body" id="recommendationsContainer">
                <div class="text-center text-muted py-3">
                    <i class="bi bi-arrow-repeat d-block mb-2" style="font-size:1.5rem;"></i>
                    Analyse en cours...
                </div>
            </div>
        </div>

        <div class="forecast-card">
            <div class="card-header">
                <span><i class="bi bi-file-earmark-bar-graph me-1"></i>Générer un rapport</span>
            </div>
            <div class="card-body">
                <form id="reportForm">
                    @csrf
                    <div class="mb-2">
                        <select class="form-select form-select-sm" id="reportType">
                            <option value="mensuel">Mensuel</option>
                            <option value="trimestriel">Trimestriel</option>
                            <option value="annuel">Annuel</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="date" class="form-control form-control-sm" id="reportDebut" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <input type="date" class="form-control form-control-sm" id="reportFin" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Générer le rapport
                    </button>
                </form>
                <div id="reportResult" style="display:none;margin-top:0.75rem;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        chargerPrevisions();
        chargerRecommandations();
        chargerAlertes();
    });

    // ─── Charger les prévisions ───
    function chargerPrevisions() {
        const container = document.getElementById('previsionsContainer');
        fetch('/gel/ia/finance/cashflow-forecast', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.forecast.previsions?.length > 0) {
                container.innerHTML = `
                    <div style="font-size:0.85rem;color:var(--gel-text-muted);margin-bottom:1rem;">
                        Trésorerie actuelle : <strong>${data.forecast.tresorerie_actuelle_formatted}</strong>
                        ${data.forecast.a_encaisser > 0 ? ` | &Agrave; encaisser : <strong>${data.forecast.a_encaisser.toLocaleString('fr-FR')} FCFA</strong>` : ''}
                    </div>
                    ${data.forecast.previsions.map(p => `
                        <div class="forecast-month">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong style="font-size:0.9rem;">${p.mois}</strong>
                                <span class="badge bg-${p.statut === 'positif' ? 'success' : 'danger'}">${p.statut === 'positif' ? 'Positif' : 'N&eacute;gatif'}</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-4 text-center">
                                    <div style="font-size:0.75rem;color:var(--gel-text-muted);">Entr&eacute;es</div>
                                    <div style="font-weight:700;color:#10B981;">${p.entrees_formatted}</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div style="font-size:0.75rem;color:var(--gel-text-muted);">Sorties</div>
                                    <div style="font-weight:700;color:#EF4444;">${p.sorties_formatted}</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div style="font-size:0.75rem;color:var(--gel-text-muted);">Solde</div>
                                    <div style="font-weight:700;">${p.solde_formatted}</div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                    ${data.forecast.recommendations?.length > 0 ? `
                        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--gel-border);">
                            ${data.forecast.recommendations.map(r => `
                                <div class="rec-card">
                                    <div style="font-weight:600;font-size:0.85rem;">${r.message}</div>
                                    <div style="font-size:0.8rem;color:var(--gel-text-muted);margin-top:0.25rem;">${r.action}</div>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                `;
            } else {
                container.innerHTML = '<div class="text-muted text-center py-3">Aucune pr&eacute;vision disponible</div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="text-danger text-center py-3">Erreur de chargement</div>';
        });
    }

    // ─── Charger les recommandations ───
    function chargerRecommandations() {
        const container = document.getElementById('recommendationsContainer');
        const marge = '{{ $stats["rentabilite"]["marge"] ?? "N/A" }}';
        container.innerHTML = `
            <div class="rec-card">
                <div style="font-weight:600;font-size:0.85rem;">Marge nette : ${marge}</div>
                <div style="font-size:0.8rem;color:var(--gel-text-muted);">Analyse de rentabilit&eacute; en cours</div>
            </div>
            <div class="rec-card">
                <div style="font-weight:600;font-size:0.85rem;">Optimisation fiscale</div>
                <div style="font-size:0.8rem;color:var(--gel-text-muted);">V&eacute;rifiez les amortissements et provisions</div>
            </div>
        `;
    }

    // ─── Charger les alertes ───
    function chargerAlertes() {
        const container = document.getElementById('alertsContainer');
        fetch('/gel/ia/finance/realtime-alerts', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.alerts.length > 0) {
                container.innerHTML = data.alerts.map(a => `
                    <div class="alert-item ${a.priorite}">
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">${a.message}</div>
                            <div style="font-size:0.75rem;color:var(--gel-text-muted);">${a.time || ''}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-check-circle d-block mb-2" style="font-size:1.5rem;"></i>Aucune alerte</div>';
            }
        });
    }

    // ─── Détection de fraude ───
    function detecterFraude() {
        fetch('/gel/ia/finance/fraud-detection', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.alerts.length > 0) {
                alert(data.count + ' alerte(s) de fraude d&eacute;tect&eacute;e(s)');
            } else {
                alert('Aucune activit&eacute; suspecte d&eacute;tect&eacute;e');
            }
        });
    }

    // ─── Génération de rapport ───
    document.getElementById('reportForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>G&eacute;n&eacute;ration...';

        fetch('/gel/ia/finance/generate-report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                type: document.getElementById('reportType').value,
                periode_debut: document.getElementById('reportDebut').value,
                periode_fin: document.getElementById('reportFin').value,
            }),
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('reportResult');
            div.style.display = 'block';
            if (data.success) {
                div.innerHTML = `<div class="alert alert-success mb-0 py-2" style="font-size:0.85rem;">
                    <i class="bi bi-check-circle me-1"></i>Rapport ${data.report.type} g&eacute;n&eacute;r&eacute; (${data.report.periode.debut} &rarr; ${data.report.periode.fin})<br>
                    <strong>${data.report.resume.total_ecritures}</strong> &eacute;critures &bull; Solde : <strong>${data.report.resume.solde.toLocaleString('fr-FR')} FCFA</strong>
                </div>`;
            } else {
                div.innerHTML = `<div class="alert alert-danger mb-0 py-2" style="font-size:0.85rem;">${data.error}</div>`;
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-pdf me-1"></i>G&eacute;n&eacute;rer le rapport';
        });
    });

    // ─── Rafraîchir les données ───
    function rafraichirDonnees() {
        chargerPrevisions();
        chargerAlertes();
        const btn = event.currentTarget;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Rafra&icirc;chissement...';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Rafra&icirc;chir';
        }, 2000);
    }
</script>
@endsection
