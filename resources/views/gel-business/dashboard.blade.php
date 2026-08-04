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

{{-- LIGNE — Accès collaborateurs --}}
<div class="gel-card" style="margin-bottom:24px;">
    <div class="gel-card-header">
        <span style="font-weight:700;">👥 Accès collaborateurs</span>
        <a href="{{ route('gel-business.inviter-comptable') }}" class="gel-btn gel-btn-primary gel-btn-sm">
            <i class="fas fa-user-plus"></i> Inviter
        </a>
    </div>
    <div class="gel-card-body" style="padding:0;">
        @php
            $collaborators = $collaborators ?? collect([]);
            $invitations = $invitations ?? collect([]);
        @endphp

        @if($collaborators->isNotEmpty() || $invitations->isNotEmpty())
        <table class="gel-table">
            <tr><th>Collaborateur</th><th>Rôle</th><th>Statut</th><th>Données manipulables</th></tr>
            @foreach($collaborators as $uc)
            @php $u = $uc->user; @endphp
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="gel-avatar" style="background:var(--gel-primary);">{{ strtoupper(substr($u->name ?? $u->email, 0, 2)) }}</div>
                        <div>
                            <div style="font-weight:600;font-size:13px;">{{ $u->name ?? $u->email }}</div>
                            <div style="font-size:12px;color:var(--gel-text-secondary);">{{ $u->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if(($uc->role ?? '') === 'secretaire')
                        <span class="gel-badge gel-badge-info">Secrétaire</span>
                    @elseif(($uc->role ?? '') === 'company_admin')
                        <span class="gel-badge gel-badge-success">Administrateur</span>
                    @else
                        <span class="gel-badge" style="background:var(--gel-primary-light);color:var(--gel-primary);">Comptable</span>
                    @endif
                </td>
                <td><span class="gel-badge gel-badge-success">Accepté</span></td>
                <td style="font-size:12px;color:var(--gel-text-secondary);">
                    @if(($uc->role ?? '') === 'secretaire')
                        Agenda, relances, documents (GED), tâches
                    @elseif(($uc->role ?? '') === 'company_admin')
                        Tous les espaces (gestion complète)
                    @else
                        Comptabilité, écritures, états financiers
                    @endif
                </td>
            </tr>
            @endforeach
            @foreach($invitations->where('statut', 'en_attente') as $inv)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="gel-avatar" style="background:var(--gel-text-muted);color:#fff;">?</div>
                        <div>
                            <div style="font-weight:600;font-size:13px;">{{ $inv->nom ?: $inv->email }}</div>
                            <div style="font-size:12px;color:var(--gel-text-secondary);">Invité le {{ optional($inv->created_at)->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if(($inv->role_invite ?? '') === 'secretaire')
                        <span class="gel-badge gel-badge-info">Secrétaire</span>
                    @else
                        <span class="gel-badge" style="background:var(--gel-primary-light);color:var(--gel-primary);">Comptable</span>
                    @endif
                </td>
                <td><span class="gel-badge gel-badge-warning">En attente</span></td>
                <td style="font-size:12px;color:var(--gel-text-secondary);">En attente d'acceptation de l'invitation</td>
            </tr>
            @endforeach
        </table>
        @else
        <div class="gel-empty" style="padding:30px;">
            <i class="fas fa-user-plus"></i>
            <h3>Aucun collaborateur</h3>
            <p>Invitez votre comptable ou votre secrétaire : ils devront accepter l'invitation avant de manipuler vos données.</p>
            <a href="{{ route('gel-business.inviter-comptable') }}" class="gel-btn gel-btn-primary gel-btn-sm">Inviter un collaborateur</a>
        </div>
        @endif
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
                <a href="{{ route('gel-business.messagerie') }}" class="gel-btn gel-btn-primary gel-btn-sm">
                    <i class="fas fa-comments"></i> Discuter / Envoyer
                </a>
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
    <div class="gel-bank-connect" onclick="openConnectBankPanel()" style="cursor: pointer;"><i class="fas fa-plus-circle"></i> Connecter une banque</div>
</div>

{{-- Templates for slide panels --}}
<template id="contactAccountantTemplate">
    <div class="gel-form-group">
        <label>Sujet</label>
        <input type="text" class="gel-form-control" placeholder="Objet de votre message">
    </div>
    <div class="gel-form-group">
        <label>Message</label>
        <textarea class="gel-form-control" rows="5" placeholder="Votre message détaillé pour l'expert-comptable..."></textarea>
    </div>
    <div class="gel-form-group">
        <label>Pièce jointe (optionnel)</label>
        <input type="file" class="gel-form-control">
    </div>
</template>

<template id="contactAccountantFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="showToast('Votre message a été envoyé à l\'expert-comptable', 'success'); closePanel();">Envoyer le message</button>
</template>

<template id="connectBankTemplate">
    <div style="text-align:center; padding: 20px;">
        <i class="fas fa-university" style="font-size: 40px; color: var(--gel-primary); margin-bottom: 16px;"></i>
        <h3 style="margin-bottom: 8px;">Sélectionnez votre banque</h3>
        <p style="color: var(--gel-text-secondary); margin-bottom: 24px;">Connectez votre compte bancaire en toute sécurité via notre partenaire pour synchroniser automatiquement vos relevés.</p>
        
        <div class="gel-form-group" style="text-align: left;">
            <input type="text" class="gel-form-control" placeholder="Rechercher une banque...">
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
            <div class="gel-card" style="padding: 12px; cursor: pointer; border: 1px solid var(--gel-primary-light);">
                <strong>Ecobank</strong>
            </div>
            <div class="gel-card" style="padding: 12px; cursor: pointer;">
                <strong>BOA</strong>
            </div>
            <div class="gel-card" style="padding: 12px; cursor: pointer;">
                <strong>UBA</strong>
            </div>
            <div class="gel-card" style="padding: 12px; cursor: pointer;">
                <strong>Orabank</strong>
            </div>
        </div>
    </div>
</template>

<template id="connectBankFooter">
    <button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button>
    <button class="gel-btn gel-btn-primary" onclick="showToast('Redirection vers la passerelle bancaire...', 'info'); closePanel();">Continuer vers la banque</button>
</template>

<script>
function openContactAccountantPanel() {
    var bodyHtml = document.getElementById('contactAccountantTemplate').innerHTML;
    var footerHtml = document.getElementById('contactAccountantFooter').innerHTML;
    openPanel('Contacter mon comptable', bodyHtml, footerHtml);
}

function openConnectBankPanel() {
    var bodyHtml = document.getElementById('connectBankTemplate').innerHTML;
    var footerHtml = document.getElementById('connectBankFooter').innerHTML;
    openPanel('Connexion bancaire sécurisée', bodyHtml, footerHtml);
}
</script>
@endsection
