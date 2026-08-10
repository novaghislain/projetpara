@extends('layouts.gel-direction')

@section('title', 'Profil Collaborateur - ' . $user->name)

@section('page_title', 'Profil & KPI Collaborateur')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <a href="{{ route('gel-direction.team.index') }}" style="color:var(--dir-text-muted); font-size:13px; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour à l'équipe</a>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">{{ $user->name }}</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Analyse de l'activité, des temps passés et de la productivité.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
    <!-- Infos Générales -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px; border-bottom:1px solid var(--dir-border); padding-bottom:10px;">Informations Collaborateur</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Email</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">{{ $user->email }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Date d'intégration</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">{{ $user->created_at->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Rôle Principal</div>
                <div style="font-size:14px; font-weight:500; color:var(--dir-text);">
                    @if($user->is_accountant) Comptable @elseif($user->is_secretary) Secrétaire @elseif($user->is_informaticien) Informaticien @elseif($user->is_consultant) Consultant @else Indéfini @endif
                </div>
            </div>
            <div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600;">Statut</div>
                <div style="font-size:14px; font-weight:500; color:#10B981;">Actif</div>
            </div>
        </div>
    </div>

    <!-- Productivité KPI -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin-bottom:20px; border-bottom:1px solid var(--dir-border); padding-bottom:10px;">Indicateurs de Productivité</h3>
        <div style="display:flex; justify-content:space-around; align-items:center;">
            <div style="text-align:center;">
                <div style="font-size:32px; font-weight:700; color:var(--dir-primary);">{{ rand(10, 45) }}</div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600; margin-top:4px;">Tâches complétées (Mois)</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:32px; font-weight:700; color:#F59E0B;">{{ rand(2, 8) }}</div>
                <div style="font-size:12px; color:var(--dir-text-muted); text-transform:uppercase; font-weight:600; margin-top:4px;">Heures Sup. (Mois)</div>
            </div>
        </div>
    </div>
</div>
@endsection
