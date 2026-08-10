@extends('layouts.gel-direction')

@section('title', 'Vue 360° - ' . $client->nom_entreprise)

@section('page_title', 'Vue 360° Client')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <a href="{{ route('gel-direction.clients.index') }}" style="color:var(--dir-text-muted); font-size:13px; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux clients</a>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">{{ $client->nom_entreprise }}</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Analyse de la relation client, des revenus générés et des ressources affectées.</p>
    </div>
    <div>
        <button class="btn btn-primary" style="background:var(--dir-primary); border:none; padding:8px 16px; font-weight:600; border-radius:8px;">
            <i class="fas fa-file-pdf"></i> Exporter le Dossier
        </button>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
    <!-- Infos Générales -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px; border-bottom:1px solid var(--dir-border); padding-bottom:10px;">Informations Générales</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Secteur / Industrie</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">Non renseigné</div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Date d'entrée</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">{{ $client->created_at->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Email Principal</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">{{ $client->email_contact ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Téléphone</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">{{ $client->telephone ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Santé Financière -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px; border-bottom:1px solid var(--dir-border); padding-bottom:10px;">Santé Financière (Estimée)</h3>
        <div style="display:flex; justify-content:space-around; align-items:center;">
            <div style="text-align:center;">
                <div style="font-size:32px; font-weight:700; color:#10B981;">{{ number_format($caGenere, 0, ',', ' ') }} €</div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600; margin-top:4px;">CA Généré YTD</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:32px; font-weight:700; color:var(--dir-primary);">0 €</div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600; margin-top:4px;">Factures Impayées</div>
            </div>
        </div>
    </div>

    <!-- Équipe Assignée -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); grid-column:1 / -1;">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px; border-bottom:1px solid var(--dir-border); padding-bottom:10px;">Équipe Assignée (Intervenants)</h3>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#F8FAFC; text-align:left;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Collaborateur</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Rôle</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Dernière Activité</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->users as $member)
                <tr style="border-bottom:1px solid #F1F5F9;">
                    <td style="padding:12px 16px; font-size:14px; font-weight:500;">
                        <i class="fas fa-user-circle text-muted me-2"></i> {{ $member->name }}
                    </td>
                    <td style="padding:12px 16px; font-size:13px; color:var(--dir-text-muted);">
                        @if($member->is_accountant) Comptable @elseif($member->is_secretary) Secrétaire @else Client Admin @endif
                    </td>
                    <td style="padding:12px 16px; font-size:13px; color:var(--dir-text-muted);">
                        Il y a 2 jours
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="padding:16px; text-align:center; color:var(--dir-text-muted); font-size:13px;">Aucun intervenant interne assigné.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
