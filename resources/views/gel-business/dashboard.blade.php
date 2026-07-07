@php $currentSection = 'dashboard'; @endphp
@extends('layouts.gel-business')

@section('title', 'Tableau de bord - Mon Entreprise')

@section('content')
{{-- Welcome Banner --}}
<div class="gel-business-welcome">
    <h1><i class="fas fa-building"></i> Bonjour {{ $stats['entreprise'] ?? 'Mon Entreprise' }}</h1>
    <p>Comptable : {{ $stats['comptable_nom'] ?? 'Non assigné' }}</p>
</div>

{{-- LIGNE 1 — KPIs --}}
<div class="gel-kpi-grid">
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Chiffre d'affaires</div>
        <div class="gel-kpi-value">{{ number_format($stats['ca_mensuel'] ?? 0, 0, ',', ' ') }} CFA</div>
        <div class="gel-kpi-change up">Année en cours</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Factures impayées</div>
        <div class="gel-kpi-value" style="color:var(--gel-danger);">0</div>
        <div class="gel-kpi-change down">CFA 0</div>
    </div>
    <div class="gel-kpi-card">
        <div class="gel-kpi-label">Trésorerie</div>
        <div class="gel-kpi-value">CFA 0</div>
        <div class="gel-kpi-change">Disponible</div>
    </div>
</div>

{{-- LIGNE 2 — Dernières opérations --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
    <div class="gel-card">
        <div class="gel-card-header">
            <span style="font-weight:700;">Dernières opérations</span>
            <a href="{{ route('gel-business.comptabilite.grand-livre') }}" class="gel-btn gel-btn-secondary gel-btn-sm">Voir tout →</a>
        </div>
        <div class="gel-card-body" style="padding:0;">
            @if(!empty($recentEcritures) && count($recentEcritures) > 0)
            <table class="gel-table">
                <tr><th>Date</th><th>Libellé</th><th>Montant</th></tr>
                @foreach($recentEcritures as $e)
                <tr>
                    <td>{{ $e->date_ecriture->format('d/m/Y') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->libelle, 30) }}</td>
                    <td class="gel-text-right">{{ number_format($e->total_debit ?? 0, 0, ',', ' ') }} CFA</td>
                </tr>
                @endforeach
            </table>
            @else
            <div class="gel-empty" style="padding:30px;">
                <i class="fas fa-exchange-alt"></i>
                <h3>Aucune opération</h3>
                <p>Votre comptable publiera les opérations ici.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Documents récents --}}
    <div class="gel-card">
        <div class="gel-card-header">
            <span style="font-weight:700;">Documents récents</span>
            <a href="{{ route('gel-business.documents') }}" class="gel-btn gel-btn-secondary gel-btn-sm">Voir tout →</a>
        </div>
        <div class="gel-card-body" style="padding:0;">
            <div class="gel-echeances" style="margin:0;">
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-file-invoice" style="color:var(--gel-primary);"></i> Facture FA-2026-001</span>
                    <span style="font-size:12px;">150 000 CFA</span>
                </div>
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-file-alt" style="color:var(--gel-success);"></i> Relevé bancaire</span>
                    <span style="font-size:12px;">Juin 2026</span>
                </div>
                <div class="gel-echeance-item">
                    <span class="gel-echeance-label"><i class="fas fa-file-invoice" style="color:var(--gel-warning);"></i> Déclaration TVA</span>
                    <span style="font-size:12px;">Mai 2026</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 3 — Prochaines échéances --}}
<div class="gel-card" style="margin-bottom:24px;">
    <div class="gel-card-header"><span style="font-weight:700;">📅 Prochaines échéances</span></div>
    <div class="gel-card-body" style="padding:0;">
        <div class="gel-echeances" style="margin:0;">
            <div class="gel-echeance-item">
                <span class="gel-echeance-label">
                    <i class="fas fa-file-invoice" style="color:var(--gel-warning);"></i>
                    <span>Déclaration TVA</span>
                </span>
                <span style="font-size:13px;font-weight:600;">15/07/2026 - 45 000 CFA</span>
            </div>
            <div class="gel-echeance-item">
                <span class="gel-echeance-label">
                    <i class="fas fa-building" style="color:var(--gel-info);"></i>
                    <span>CNSS trimestrielle</span>
                </span>
                <span style="font-size:13px;font-weight:600;">31/07/2026 - 120 000 CFA</span>
            </div>
            <div class="gel-echeance-item">
                <span class="gel-echeance-label">
                    <i class="fas fa-users" style="color:var(--gel-text-muted);"></i>
                    <span>IRPP</span>
                </span>
                <span style="font-size:13px;font-weight:600;">15/08/2026 - 35 000 CFA</span>
            </div>
        </div>
    </div>
</div>

{{-- LIGNE 4 — Contacter mon comptable --}}
<div class="gel-card" style="margin-bottom:24px;">
    <div class="gel-card-header"><span style="font-weight:700;">📞 Contacter mon comptable</span></div>
    <div class="gel-card-body">
        <div class="gel-contact-card">
            <div class="gel-avatar gel-avatar-lg" style="background:var(--gel-primary);">
                {{ strtoupper(substr($stats['comptable_nom'] ?? 'C', 0, 2)) }}
            </div>
            <div style="flex:1;">
                <div style="font-weight:600;font-size:15px;">{{ $stats['comptable_nom'] ?? 'Comptable' }}</div>
                <div style="font-size:13px;color:var(--gel-text-secondary);">{{ $stats['comptable_email'] ?? 'comptable@cabinet.bj' }}</div>
                <div style="font-size:13px;color:var(--gel-text-secondary);">{{ $stats['comptable_telephone'] ?? '+229 01 23 45 67' }}</div>
            </div>
            <div style="display:flex;gap:8px;">
                <button class="gel-btn gel-btn-primary gel-btn-sm" onclick="showToast('Message envoyé','success')">
                    <i class="fas fa-envelope"></i> Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bank Accounts --}}
<div class="gel-bank-accounts">
    <div class="gel-chart-header">
        <div class="gel-chart-title">Comptes bancaires</div>
    </div>
    <div class="gel-bank-account">
        <div class="gel-bank-account-info">
            <div class="gel-bank-account-icon" style="background:#005BAC;"><i class="fas fa-university"></i></div>
            <div>
                <div class="gel-bank-account-name">Compte courant</div>
                <div class="gel-bank-account-desc">XAF</div>
            </div>
        </div>
        <div class="gel-bank-account-balance">CFA 0</div>
    </div>
    <div class="gel-bank-connect"><i class="fas fa-plus-circle"></i> Connecter une banque</div>
</div>
@endsection
