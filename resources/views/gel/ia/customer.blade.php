@extends('layouts.gel')

@section('title', 'Customer AI - GEL Cabinet')

@section('styles')
<style>
    .client-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        padding: 1.25rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    .client-card:hover { border-color: var(--gel-accent-2); box-shadow: 0 4px 12px rgba(99,91,255,0.1); }
    .client-card .client-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        color: white;
        flex-shrink: 0;
    }
    .segment-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .insight-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        padding: 1.25rem;
        text-align: center;
    }
    .insight-card .insight-value { font-size: 1.75rem; font-weight: 800; color: var(--gel-primary); }
    .insight-card .insight-label { font-size: 0.8rem; color: var(--gel-text-muted); margin-top: 0.25rem; }
    .risk-meter {
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
        overflow: hidden;
        margin: 0.5rem 0;
    }
    .risk-meter .fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .segment-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .segment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .segment-title { font-weight: 700; font-size: 1rem; }
    .segment-count { font-size: 0.8rem; color: var(--gel-text-muted); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Customer AI</h1>
        <p class="page-subtitle">Analyse intelligente de votre portefeuille clients</p>
    </div>
</div>

{{-- Insights globaux --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="insight-card">
            <div class="insight-value">{{ $insights['total_clients'] ?? 0 }}</div>
            <div class="insight-label">Total clients</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="insight-card">
            <div class="insight-value" style="color:#10B981;">{{ $insights['clients_actifs'] ?? 0 }}</div>
            <div class="insight-label">Clients actifs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="insight-card">
            <div class="insight-value" style="color:#F59E0B;">{{ $insights['clients_en_suspens'] ?? 0 }}</div>
            <div class="insight-label">En suspens</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="insight-card">
            <div class="insight-value" style="color:#3B82F6;">{{ $insights['nouveaux_ce_mois'] ?? 0 }}</div>
            <div class="insight-label">Nouveaux ce mois</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Liste des clients --}}
    <div class="col-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="font-weight:700;margin:0;">Portefeuille clients</h5>
            <span class="badge bg-light text-dark">{{ count($clients) }} clients</span>
        </div>
        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchClient" placeholder="Rechercher un client...">
        </div>
        <div style="max-height:600px;overflow-y:auto;" id="clientsList">
            @foreach($clients as $client)
            <div class="client-card mb-2" data-id="{{ $client->id }}" data-name="{{ strtolower($client->nom) }}" onclick="analyserClient({{ $client->id }})">
                <div class="d-flex align-items-center gap-3">
                    <div class="client-avatar" style="background: linear-gradient(135deg, {{ ['#635bff','#10B981','#F59E0B','#EF4444','#3B82F6','#8B5CF6'][$loop->index % 6] }}, {{ ['#4f46e5','#059669','#D97706','#DC2626','#2563EB','#7C3AED'][$loop->index % 6] }});">
                        {{ strtoupper(substr($client->nom, 0, 2)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:0.85rem;">{{ $client->nom }}</div>
                        <div style="font-size:0.75rem;color:var(--gel-text-muted);">{{ $client->email }}</div>
                        <div style="font-size:0.75rem;color:var(--gel-text-muted);">{{ $client->telephone ?? '—' }}</div>
                    </div>
                    <span class="badge bg-{{ $client->statut === 'actif' ? 'success' : 'warning' }}" style="font-size:0.65rem;">
                        {{ $client->statut }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Analyse détaillée --}}
    <div class="col-lg-8">
        <div id="analysisPlaceholder" class="text-center py-5" style="background:white;border-radius:12px;border:1px solid var(--gel-border);">
            <i class="bi bi-person-lines-fill d-block mb-3" style="font-size:3rem;color:var(--gel-text-muted);opacity:0.3;"></i>
            <h5 style="color:var(--gel-primary);font-weight:700;">Sélectionnez un client</h5>
            <p style="color:var(--gel-text-muted);font-size:0.9rem;max-width:400px;margin:0 auto;">
                Cliquez sur un client dans la liste pour voir son analyse complète
            </p>
        </div>
        <div id="clientAnalysis" style="display:none;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ─── Filtre clients ───
    document.getElementById('searchClient')?.addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.client-card').forEach(card => {
            const name = card.dataset.name || '';
            card.style.display = name.includes(search) ? 'block' : 'none';
        });
    });

    // ─── Analyser un client ───
    function analyserClient(clientId) {
        const placeholder = document.getElementById('analysisPlaceholder');
        const analysis = document.getElementById('clientAnalysis');

        placeholder.style.display = 'none';
        analysis.style.display = 'block';
        analysis.innerHTML = '<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Analyse en cours...</div>';

        // Mettre en évidence le client sélectionné
        document.querySelectorAll('.client-card').forEach(c => c.style.borderColor = 'var(--gel-border)');
        document.querySelector(`.client-card[data-id="${clientId}"]`).style.borderColor = 'var(--gel-accent-2)';

        fetch(`/gel/ia/customer/analyze/${clientId}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const a = data.analysis;
                const sc = a.sante_client;
                analysis.innerHTML = `
                    <div style="background:white;border-radius:12px;border:1px solid var(--gel-border);overflow:hidden;">
                        {{-- En-tête client --}}
                        <div style="padding:1.5rem;background:linear-gradient(135deg, #f8f9fa, #fff);border-bottom:1px solid var(--gel-border);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="client-avatar" style="width:56px;height:56px;font-size:1.3rem;background:linear-gradient(135deg, ${sc.couleur}, ${sc.couleur}88);">
                                    ${a.client.nom.substring(0,2).toUpperCase()}
                                </div>
                                <div>
                                    <h4 style="font-weight:700;margin:0;">${a.client.nom}</h4>
                                    <div style="font-size:0.85rem;color:var(--gel-text-muted);">${a.client.email} | ${a.client.telephone || '—'}</div>
                                    <div style="font-size:0.8rem;color:var(--gel-text-muted);">Client depuis ${a.client.date_creation}</div>
                                </div>
                                <div class="ms-auto text-end">
                                    <div style="font-size:1.25rem;font-weight:800;color:${sc.couleur};">${sc.score}/100</div>
                                    <div style="font-size:0.75rem;color:var(--gel-text-muted);">Score santé</div>
                                    <span class="badge" style="background:${sc.couleur};">${sc.niveau}</span>
                                </div>
                            </div>
                        </div>

                        <div style="padding:1.5rem;">
                            <div class="row g-4">
                                {{-- Analyse financière --}}
                                <div class="col-md-6">
                                    <h6 style="font-weight:700;margin-bottom:1rem;"><i class="bi bi-cash-stack me-1"></i>Analyse financière</h6>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span style="font-size:0.85rem;color:var(--gel-text-muted);">Total facturé</span>
                                        <span style="font-weight:600;font-size:0.9rem;">${a.analyse_financiere.total_facture_formatted}</span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span style="font-size:0.85rem;color:var(--gel-text-muted);">Impayés</span>
                                        <span style="font-weight:600;font-size:0.9rem;color:${a.analyse_financiere.total_impaye > 0 ? '#EF4444' : '#10B981'};">${a.analyse_financiere.total_impaye_formatted}</span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span style="font-size:0.85rem;color:var(--gel-text-muted);">Délai paiement moyen</span>
                                        <span style="font-weight:600;font-size:0.9rem;">${a.analyse_financiere.delai_paiement_moyen}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-size:0.85rem;color:var(--gel-text-muted);">Taux d'impayé</span>
                                        <span style="font-weight:600;font-size:0.9rem;">${a.analyse_financiere.taux_impaye}</span>
                                    </div>
                                </div>

                                {{-- Facteurs santé --}}
                                <div class="col-md-6">
                                    <h6 style="font-weight:700;margin-bottom:1rem;"><i class="bi bi-heart-pulse me-1"></i>Facteurs de santé</h6>
                                    ${sc.facteurs.map(f => `<div style="font-size:0.85rem;padding:0.25rem 0;">${f}</div>`).join('')}
                                    <div class="risk-meter mt-2">
                                        <div class="fill" style="width:${sc.score}%;background:${sc.couleur};"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Recommandations --}}
                            <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gel-border);">
                                <h6 style="font-weight:700;margin-bottom:1rem;"><i class="bi bi-lightbulb me-1"></i>Recommandations</h6>
                                ${a.recommandations.map(r => `
                                    <div style="padding:0.75rem;border-radius:8px;background:${r.priorite === 'haute' ? '#fef2f2' : (r.priorite === 'moyenne' ? '#fffbeb' : '#f0fdf4')};margin-bottom:0.5rem;border-left:3px solid ${r.priorite === 'haute' ? '#EF4444' : (r.priorite === 'moyenne' ? '#F59E0B' : '#10B981')};">
                                        <div style="font-weight:600;font-size:0.85rem;">${r.message}</div>
                                        <div style="font-size:0.75rem;color:var(--gel-text-muted);text-transform:capitalize;">${r.type} &bull; Priorité ${r.priorite}</div>
                                    </div>
                                `).join('')}
                            </div>

                            {{-- Notes --}}
                            ${a.notes && a.notes.length > 0 ? `
                                <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gel-border);">
                                    <h6 style="font-weight:700;margin-bottom:1rem;"><i class="bi bi-sticky me-1"></i>Notes récentes</h6>
                                    ${a.notes.map(n => `
                                        <div style="padding:0.5rem 0;border-bottom:1px solid #f0f0f0;font-size:0.85rem;">
                                            <div>${n.contenu}</div>
                                            <div style="font-size:0.7rem;color:var(--gel-text-muted);">${n.created_at}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            } else {
                analysis.innerHTML = `<div class="text-danger text-center py-4">${data.error || 'Erreur'}</div>`;
            }
        })
        .catch(() => {
            analysis.innerHTML = '<div class="text-danger text-center py-4">Erreur de connexion</div>';
        });
    }

    // ─── Prédiction churn (bouton optionnel) ───
    function predireChurn() {
        fetch('/gel/ia/customer/churn-prediction', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log(data.predictions);
            }
        });
    }
</script>
@endsection
