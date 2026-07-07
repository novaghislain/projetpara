@extends('layouts.gel')

@section('title', 'IA Comptable - GEL Cabinet')

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
    .metric-card .metric-label { font-size: 0.8rem; color: var(--gel-text-muted); }
    .metric-card .metric-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 0.75rem;
    }
    .action-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .action-card .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gel-border);
        background: #fafbfc;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .action-card .card-body { padding: 1.5rem; }
    .result-box {
        padding: 1rem;
        border-radius: 8px;
        background: var(--gel-bg);
        margin-top: 1rem;
        font-size: 0.85rem;
        display: none;
    }
    .anomaly-item {
        padding: 0.75rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border-left: 3px solid;
        font-size: 0.85rem;
    }
    .anomaly-item.critical { background: #fef2f2; border-left-color: #EF4444; }
    .anomaly-item.warning { background: #fffbeb; border-left-color: #F59E0B; }
    .anomaly-item.info { background: #eff6ff; border-left-color: #3B82F6; }
    .account-suggestion {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        background: white;
        border: 1px solid var(--gel-border);
        margin-bottom: 0.35rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
    }
    .account-suggestion .compte { font-weight: 700; color: var(--gel-accent-2); }
    .account-suggestion .confiance { font-size: 0.75rem; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">IA Comptable</h1>
        <p class="page-subtitle">Assistant intelligent pour la comptabilité SYSCOHADA</p>
    </div>
</div>

{{-- Métriques --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(99,91,255,0.1);color:var(--gel-accent-2);"><i class="bi bi-lightbulb"></i></div>
            <div class="metric-value">{{ $stats['total_suggestions'] ?? 0 }}</div>
            <div class="metric-label">Total suggestions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(245,158,11,0.1);color:#F59E0B;"><i class="bi bi-hourglass"></i></div>
            <div class="metric-value">{{ $stats['pending_suggestions'] ?? 0 }}</div>
            <div class="metric-label">En attente</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(16,185,129,0.1);color:#10B981;"><i class="bi bi-check-circle"></i></div>
            <div class="metric-value">{{ $stats['approved_suggestions'] ?? 0 }}</div>
            <div class="metric-label">Approuvées</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(239,68,68,0.1);color:#EF4444;"><i class="bi bi-shield-exclamation"></i></div>
            <div class="metric-value">{{ $stats['anomalies_count'] ?? 0 }}</div>
            <div class="metric-label">Anomalies détectées</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        {{-- Analyse d'écriture --}}
        <div class="action-card">
            <div class="card-header">
                <span><i class="bi bi-search me-1"></i>Analyser une transaction</span>
            </div>
            <div class="card-body">
                <form id="analyzeForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw500">Libellé de la transaction</label>
                        <input type="text" class="form-control" id="analyzeLibelle" placeholder="Ex: Achat de fournitures de bureau" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw500">Montant</label>
                            <input type="number" class="form-control" id="analyzeMontant" placeholder="0" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw500">Type</label>
                            <select class="form-select" id="analyzeType">
                                <option value="charge">Charge</option>
                                <option value="produit">Produit</option>
                                <option value="tresorerie">Trésorerie</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="background:var(--gel-accent-2);border:none;">
                        <i class="bi bi-cpu me-1"></i>Analyser
                    </button>
                </form>
                <div class="result-box" id="analyzeResult"></div>
            </div>
        </div>

        {{-- Suggestion de comptes --}}
        <div class="action-card">
            <div class="card-header">
                <span><i class="bi bi-list-columns me-1"></i>Suggestion de comptes SYSCOHADA</span>
            </div>
            <div class="card-body">
                <form id="suggestForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw500">Libellé</label>
                        <input type="text" class="form-control" id="suggestLibelle" placeholder="Ex: Salaire du personnel" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-list-check me-1"></i>Suggérer des comptes
                    </button>
                </form>
                <div class="result-box" id="suggestResult"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        {{-- Détection d'anomalies --}}
        <div class="action-card">
            <div class="card-header">
                <span><i class="bi bi-shield-exclamation me-1"></i>Détection d'anomalies</span>
                <button class="btn btn-sm btn-outline-danger" onclick="detecterAnomalies()">
                    <i class="bi bi-search me-1"></i>Lancer
                </button>
            </div>
            <div class="card-body" id="anomaliesContainer">
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle d-block mb-2" style="font-size:1.5rem;"></i>
                    Cliquez sur "Lancer" pour détecter les anomalies
                </div>
            </div>
        </div>

        {{-- Génération d'écriture --}}
        <div class="action-card">
            <div class="card-header">
                <span><i class="bi bi-journal-plus me-1"></i>Générer une écriture comptable</span>
            </div>
            <div class="card-body">
                <form id="generateForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw500">Libellé</label>
                        <input type="text" class="form-control" id="genLibelle" placeholder="Ex: Achat de matériel" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw500">Montant</label>
                            <input type="number" class="form-control" id="genMontant" placeholder="0" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw500">Type</label>
                            <select class="form-select" id="genType">
                                <option value="charge">Charge</option>
                                <option value="produit">Produit</option>
                                <option value="tresorerie">Trésorerie</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-magic me-1"></i>Générer
                    </button>
                </form>
                <div class="result-box" id="generateResult"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ─── Analyser transaction ───
    document.getElementById('analyzeForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Analyse...';

        fetch('/api/ai/accounting/analyze', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                libelle: document.getElementById('analyzeLibelle').value,
                montant: parseFloat(document.getElementById('analyzeMontant').value),
                type: document.getElementById('analyzeType').value,
                client_id: {{ Auth::user()->active_client_id ?? 0 }},
            }),
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('analyzeResult');
            div.style.display = 'block';
            if (data.success && data.suggestions?.length > 0) {
                div.innerHTML = '<div style="font-weight:600;margin-bottom:0.5rem;">Comptes suggérés :</div>' +
                    data.suggestions.map(s => `
                        <div class="account-suggestion">
                            <span><span class="compte">${s.compte || s.account || s.code || 'N/A'}</span> — ${s.libelle || s.label || s.name || ''}</span>
                            <span class="confiance badge bg-${(s.confidence || 0) > 70 ? 'success' : ((s.confidence || 0) > 40 ? 'warning' : 'danger')}">
                                ${s.confidence || 0}%
                            </span>
                        </div>
                    `).join('');
            } else {
                div.innerHTML = '<div class="text-muted">Aucune suggestion trouvée</div>';
            }
        })
        .catch(() => {
            const div = document.getElementById('analyzeResult');
            div.style.display = 'block';
            div.innerHTML = '<div class="text-danger">Erreur lors de l\'analyse</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cpu me-1"></i>Analyser';
        });
    });

    // ─── Suggérer des comptes ───
    document.getElementById('suggestForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Recherche...';

        fetch('/api/ai/accounting/suggest-accounts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                libelle: document.getElementById('suggestLibelle').value,
                client_id: {{ Auth::user()->active_client_id ?? 0 }},
            }),
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('suggestResult');
            div.style.display = 'block';
            if (data.success && data.accounts?.length > 0) {
                div.innerHTML = '<div style="font-weight:600;margin-bottom:0.5rem;">Comptes SYSCOHADA correspondants :</div>' +
                    data.accounts.map(a => `
                        <div class="account-suggestion">
                            <span><span class="compte">${a.compte || a.account || a.code || 'N/A'}</span> — ${a.libelle || a.label || a.name || ''}</span>
                            <span class="confiance badge bg-${(a.confidence || 0) > 70 ? 'success' : ((a.confidence || 0) > 40 ? 'warning' : 'danger')}">
                                ${a.confidence || 0}%
                            </span>
                        </div>
                    `).join('');
            } else {
                div.innerHTML = '<div class="text-muted">Aucun compte trouvé</div>';
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-list-check me-1"></i>Suggérer des comptes';
        });
    });

    // ─── Détecter les anomalies ───
    function detecterAnomalies() {
        const container = document.getElementById('anomaliesContainer');
        container.innerHTML = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Analyse en cours...</div>';

        fetch('/api/ai/accounting/anomalies', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.anomalies?.length > 0) {
                container.innerHTML = data.anomalies.map(a => `
                    <div class="anomaly-item ${a.severite || a.priorite || 'info'}">
                        <div style="font-weight:600;">${a.titre || a.title || 'Anomalie'}</div>
                        <div>${a.description || a.message || ''}</div>
                        <div style="font-size:0.75rem;color:var(--gel-text-muted);margin-top:0.25rem;">
                            ${a.compte ? `Compte: ${a.compte}` : ''} ${a.montant ? `| Montant: ${parseFloat(a.montant).toLocaleString('fr-FR')} FCFA` : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-check-circle d-block mb-2" style="font-size:1.5rem;"></i>Aucune anomalie détectée</div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="text-danger text-center py-3">Erreur de détection</div>';
        });
    }

    // ─── Générer écriture ───
    document.getElementById('generateForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Génération...';

        fetch('/api/ai/accounting/generate-entry', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                libelle: document.getElementById('genLibelle').value,
                montant: parseFloat(document.getElementById('genMontant').value),
                type: document.getElementById('genType').value,
                client_id: {{ Auth::user()->active_client_id ?? 0 }},
            }),
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('generateResult');
            div.style.display = 'block';
            if (data.success) {
                div.innerHTML = '<div class="alert alert-success mb-0 py-2" style="font-size:0.85rem;">' +
                    '<i class="bi bi-check-circle me-1"></i>Écriture suggérée avec succès</div>';
            } else {
                div.innerHTML = '<div class="alert alert-danger mb-0 py-2" style="font-size:0.85rem;">Erreur</div>';
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic me-1"></i>Générer';
        });
    });
</script>
@endsection
