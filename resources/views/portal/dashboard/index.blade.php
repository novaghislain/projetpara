@extends('layouts.portal')
@section('title', 'Tableau de bord - Espace Client')

@section('content')
<div class="portal-page-header portal-mb-6">
    <h1 class="portal-title">Bonjour, {{ auth('portal')->user()->first_name }} 👋</h1>
    <p class="portal-subtitle">Bienvenue sur votre espace client {{ \App\Models\Client::where('portal_slug', $slug)->value('company_name') }}.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;">
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 14px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Factures en attente</h3>
        <div style="font-size: 32px; font-weight: 700;">0</div>
    </div>
    
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 14px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Devis à valider</h3>
        <div style="font-size: 32px; font-weight: 700;">0</div>
    </div>
    
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 14px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Nouveaux Messages</h3>
        <div style="font-size: 32px; font-weight: 700;">0</div>
    </div>
</div>

<div class="portal-card">
    <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Activité récente</h2>
    <div style="text-align: center; padding: 40px 0; color: var(--portal-text-muted);">
        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
        <p>Aucune activité récente à afficher pour le moment.</p>
    </div>
</div>
@endsection
