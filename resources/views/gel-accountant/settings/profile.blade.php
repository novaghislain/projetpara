@php $currentSection = 'profile'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Mon Profil - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Mon Profil</h1>
        <p class="gel-page-subtitle">Gérez vos informations personnelles</p>
    </div>
    <a href="{{ route('gel-accountant.settings') }}" class="gel-btn gel-btn-secondary gel-btn-sm">
        <i class="fas fa-cog"></i> Paramètres
    </a>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;">
    {{-- Carte profil --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <div class="gel-card-body p-4 mb-4" style="text-align:center;">
                <div class="avatar" style="width:72px;height:72px;font-size:24px;background:var(--gel-primary);color:white;margin:0 auto 16px;">
                    {{ strtoupper(substr(auth()->user()?->name ?? auth()->user()?->email ?? 'U', 0, 2)) }}
                </div>
                <div style="font-weight:700;font-size:16px;margin-bottom:4px;">{{ auth()->user()?->name ?? 'Utilisateur' }}</div>
                <div style="color:var(--gel-text-muted);font-size:13px;margin-bottom:12px;">{{ auth()->user()?->email }}</div>
                <span class="gel-badge gel-badge-info">Comptable</span>
                <hr style="margin:20px 0;border-color:var(--gel-border);">
                <div style="text-align:left;display:flex;flex-direction:column;gap:10px;">
                    <div style="font-size:13px;color:var(--gel-text-secondary);">
                        <i class="fas fa-calendar-alt" style="width:16px;color:var(--gel-text-muted);"></i>
                        Membre depuis {{ auth()->user()?->created_at?->format('M Y') ?? 'N/A' }}
                    </div>
                    <div style="font-size:13px;color:var(--gel-text-secondary);">
                        <i class="fas fa-check-circle" style="width:16px;color:var(--gel-success);"></i>
                        Compte vérifié
                    </div>
                </div>
            </div>
        </div>

        {{-- Liens rapides --}}
        <div class="gel-card p-4 mb-4" style="margin-top:16px;">
            <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;font-size:13px;">Accès rapides</span></div>
            <div class="gel-card-body p-4 mb-4">
                @foreach([
                    ['gel-accountant.dashboard','fa-tachometer-alt','Tableau de bord'],
                    ['gel-accountant.clients','fa-users','Mes clients'],
                    ['gel-accountant.tasks.index','fa-tasks','Mes Tâches'],
                    ['gel-accountant.settings','fa-cog','Paramètres'],
                ] as $link)
                <a href="{{ route($link[0]) }}" class="dd-item" style="display:flex;align-items:center;gap:10px;padding:10px 16px;color:var(--gel-text-primary);">
                    <i class="fas {{ $link[1] }}" style="width:16px;color:var(--gel-primary);"></i>
                    {{ $link[2] }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Activité récente --}}
    <div>
        <div class="gel-card p-4 mb-4">
            <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Activité récente</span></div>
            <div class="gel-card-body p-4 mb-4">
                <div class="gel-empty" style="padding:40px 20px;">
                    <i class="fas fa-history" style="font-size:36px;color:var(--gel-text-muted);margin-bottom:12px;display:block;"></i>
                    <h3>Aucune Activité récente</h3>
                    <p>Votre historique d'actions apparaîtra ici.</p>
                </div>
            </div>
        </div>

        <div class="gel-card p-4 mb-4" style="margin-top:16px;">
            <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Statistiques</span></div>
            <div class="gel-card-body p-4 mb-4">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                    <div style="text-align:center;padding:16px;background:var(--gel-sidebar-bg);border-radius:8px;">
                        <div style="font-size:24px;font-weight:700;color:var(--gel-primary);">0</div>
                        <div style="font-size:12px;color:var(--gel-text-muted);margin-top:4px;">Clients gérés</div>
                    </div>
                    <div style="text-align:center;padding:16px;background:var(--gel-sidebar-bg);border-radius:8px;">
                        <div style="font-size:24px;font-weight:700;color:var(--gel-success);">0</div>
                        <div style="font-size:12px;color:var(--gel-text-muted);margin-top:4px;">Tâches complètes</div>
                    </div>
                    <div style="text-align:center;padding:16px;background:var(--gel-sidebar-bg);border-radius:8px;">
                        <div style="font-size:24px;font-weight:700;color:var(--gel-warning);">0</div>
                        <div style="font-size:12px;color:var(--gel-text-muted);margin-top:4px;">Rapports générés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

