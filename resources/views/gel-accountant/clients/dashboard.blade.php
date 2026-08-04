@extends('layouts.gel-accountant')

@section('title', $client->nom_entreprise . ' — Tableau de bord')

@section('content')
{{-- En-tête du client --}}
<div class="gel-page-header" style="align-items:flex-start;">
    <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;">
            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--gel-primary),var(--gel-primary-dark));display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:700;">
                {{ strtoupper(substr($client->nom_entreprise, 0, 2)) }}
            </div>
            <div>
                <h1 class="gel-page-title" style="margin-bottom:2px;">{{ $client->nom_entreprise }}</h1>
                <p class="gel-page-subtitle" style="margin:0;">
                    @if($client->ifu)<code style="font-size:11px;background:var(--gel-bg-light);padding:2px 8px;border-radius:4px;margin-right:8px;">IFU: {{ $client->ifu }}</code>@endif
                    @if($client->rc)<span style="font-size:12px;color:var(--gel-text-muted);">RCCM: {{ $client->rc }}</span>@endif
                </p>
            </div>
        </div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('gel-accountant.comptabilite.ecritures', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-pen"></i> Saisir une écriture
        </a>
        <a href="{{ route('gel-accountant.client.deselect') }}" class="gel-btn gel-btn-secondary" title="Retour au cabinet">
            <i class="fas fa-arrow-left"></i> Retour au cabinet
        </a>
    </div>
</div>

{{-- Exercice en cours --}}
@if($exerciceActif)
<div class="gel-card p-3 mb-4" style="background:linear-gradient(135deg,rgba(var(--gel-primary-rgb,59,130,246),.08),rgba(var(--gel-primary-rgb,59,130,246),.02));border-left:4px solid var(--gel-primary);">
    <div style="display:flex;align-items:center;gap:12px;">
        <i class="fas fa-calendar-alt" style="font-size:20px;color:var(--gel-primary);"></i>
        <div>
            <strong>Exercice en cours :</strong> {{ $exerciceActif->libelle }}
            <span style="color:var(--gel-text-muted);margin-left:8px;">({{ \Carbon\Carbon::parse($exerciceActif->date_debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($exerciceActif->date_fin)->format('d/m/Y') }})</span>
        </div>
    </div>
</div>
@else
<div class="gel-card p-3 mb-4" style="background:rgba(var(--gel-warning-rgb,234,179,8),.08);border-left:4px solid var(--gel-warning);">
    <div style="display:flex;align-items:center;gap:12px;">
        <i class="fas fa-exclamation-triangle" style="font-size:20px;color:var(--gel-warning);"></i>
        <div>
            <strong>Aucun exercice ouvert.</strong>
            <span style="color:var(--gel-text-muted);margin-left:8px;">Créez un exercice comptable pour commencer la saisie.</span>
        </div>
    </div>
</div>
@endif

{{-- KPIs --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    {{-- Écritures du mois --}}
    <div class="gel-card p-4" style="text-align:center;">
        <div style="font-size:32px;font-weight:700;color:var(--gel-primary);">{{ $stats['ecritures_mois'] }}</div>
        <div style="font-size:13px;color:var(--gel-text-muted);margin-top:4px;">Écritures ce mois</div>
    </div>

    {{-- Écritures en attente --}}
    <div class="gel-card p-4" style="text-align:center;">
        <div style="font-size:32px;font-weight:700;color:{{ $stats['en_attente'] > 0 ? 'var(--gel-warning)' : 'var(--gel-success)' }};">{{ $stats['en_attente'] }}</div>
        <div style="font-size:13px;color:var(--gel-text-muted);margin-top:4px;">En attente de validation</div>
    </div>

    {{-- Balance --}}
    <div class="gel-card p-4" style="text-align:center;">
        @if($stats['balance_equilibree'])
            <div style="font-size:32px;"><i class="fas fa-check-circle" style="color:var(--gel-success);"></i></div>
            <div style="font-size:13px;color:var(--gel-success);margin-top:4px;">Balance équilibrée</div>
        @else
            <div style="font-size:32px;"><i class="fas fa-exclamation-circle" style="color:var(--gel-danger);"></i></div>
            <div style="font-size:13px;color:var(--gel-danger);margin-top:4px;">Balance déséquilibrée</div>
        @endif
    </div>

    {{-- Comptes actifs --}}
    <div class="gel-card p-4" style="text-align:center;">
        <div style="font-size:32px;font-weight:700;color:var(--gel-text);">{{ $stats['comptes_actifs'] }}</div>
        <div style="font-size:13px;color:var(--gel-text-muted);margin-top:4px;">Comptes actifs</div>
    </div>

    {{-- Journaux --}}
    <div class="gel-card p-4" style="text-align:center;">
        <div style="font-size:32px;font-weight:700;color:var(--gel-text);">{{ $stats['journaux_count'] }}</div>
        <div style="font-size:13px;color:var(--gel-text-muted);margin-top:4px;">Journaux comptables</div>
    </div>

    {{-- Total écritures --}}
    <div class="gel-card p-4" style="text-align:center;">
        <div style="font-size:32px;font-weight:700;color:var(--gel-text);">{{ $stats['ecritures_total'] }}</div>
        <div style="font-size:13px;color:var(--gel-text-muted);margin-top:4px;">Écritures au total</div>
    </div>
</div>

{{-- Accès rapides --}}
<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;"><i class="fas fa-bolt" style="color:var(--gel-primary);margin-right:8px;"></i>Accès rapides</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
        <a href="{{ route('gel-accountant.comptabilite.plan-comptable', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-secondary" style="text-align:center;">
            <i class="fas fa-sitemap"></i> Plan comptable
        </a>
        <a href="{{ route('gel-accountant.comptabilite.journaux', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-secondary" style="text-align:center;">
            <i class="fas fa-book"></i> Journaux
        </a>
        <a href="{{ route('gel-accountant.comptabilite.ecritures', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-secondary" style="text-align:center;">
            <i class="fas fa-pen"></i> Écritures
        </a>
        <a href="{{ route('gel-accountant.comptabilite.grand-livre', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-secondary" style="text-align:center;">
            <i class="fas fa-book-open"></i> Grand Livre
        </a>
        <a href="{{ route('gel-accountant.comptabilite.balance', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-secondary" style="text-align:center;">
            <i class="fas fa-balance-scale"></i> Balance
        </a>
        <a href="{{ route('gel-accountant.comptabilite.etats-financiers', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-secondary" style="text-align:center;">
            <i class="fas fa-chart-pie"></i> États financiers
        </a>
    </div>
</div>

{{-- Dernières écritures --}}
<div class="gel-card p-4">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">
        <i class="fas fa-history" style="color:var(--gel-primary);margin-right:8px;"></i>Dernières écritures
    </h3>

    @if($dernieresEcritures->count() > 0)
        <table class="gel-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Journal</th>
                    <th>Libellé</th>
                    <th style="text-align:right;">Débit</th>
                    <th style="text-align:right;">Crédit</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dernieresEcritures as $ecriture)
                    <tr>
                        <td>
                            <a href="{{ route('gel-accountant.comptabilite.ecritures.show', $ecriture->id) }}" style="color:var(--gel-primary);font-weight:600;">
                                {{ $ecriture->numero ?? '#' . $ecriture->id }}
                            </a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') }}</td>
                        <td><span class="gel-badge">{{ $ecriture->journal->code ?? '—' }}</span></td>
                        <td>{{ Str::limit($ecriture->libelle, 40) }}</td>
                        <td style="text-align:right;font-family:monospace;">{{ number_format($ecriture->total_debit, 0, ',', ' ') }}</td>
                        <td style="text-align:right;font-family:monospace;">{{ number_format($ecriture->total_credit, 0, ',', ' ') }}</td>
                        <td>
                            @if($ecriture->valide)
                                <span class="gel-badge gel-badge-success">Validée</span>
                            @else
                                <span class="gel-badge gel-badge-warning">En attente</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="text-align:center;margin-top:12px;">
            <a href="{{ route('gel-accountant.comptabilite.ecritures', ['client_id' => $client->id]) }}" style="color:var(--gel-primary);font-size:13px;">
                Voir toutes les écritures →
            </a>
        </div>
    @else
        <div class="gel-empty" style="padding:32px;">
            <i class="fas fa-inbox" style="font-size:32px;color:var(--gel-text-muted);"></i>
            <h3 style="font-size:15px;margin-top:12px;">Aucune écriture</h3>
            <p style="font-size:13px;color:var(--gel-text-muted);">Aucune écriture comptable n'a été saisie pour ce client.</p>
            <a href="{{ route('gel-accountant.comptabilite.ecritures.create', ['client_id' => $client->id]) }}" class="gel-btn gel-btn-primary" style="margin-top:12px;">
                <i class="fas fa-plus"></i> Créer la première écriture
            </a>
        </div>
    @endif
</div>
@endsection
