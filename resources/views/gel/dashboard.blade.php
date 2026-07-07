@extends('layouts.gel')

@section('title', 'Tableau de bord — GEL Cabinet')

@section('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--gel-border);
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 1rem;
    }
    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--gel-primary);
        line-height: 1.2;
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--gel-text-muted);
        font-weight: 500;
    }
    .stat-change {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
    }
    .stat-change.up { color: #0ca678; background: #e6fcf5; }
    .stat-change.down { color: #e03131; background: #ffe0e0; }

    .card-dashboard {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
    }
    .card-dashboard .card-header {
        background: transparent;
        border-bottom: 1px solid var(--gel-border);
        padding: 1.25rem 1.5rem;
        font-weight: 700;
        font-size: 1rem;
    }
    .card-dashboard .card-body {
        padding: 1.5rem;
    }

    .activity-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }
    .activity-text {
        font-size: 0.85rem;
        color: #1a1f36;
        flex: 1;
    }
    .activity-time {
        font-size: 0.75rem;
        color: var(--gel-text-muted);
        white-space: nowrap;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--gel-border);
        text-decoration: none;
        color: var(--gel-primary);
        transition: all 0.2s ease;
        background: white;
    }
    .quick-action:hover {
        border-color: var(--gel-accent-2);
        background: #f8f9ff;
        color: var(--gel-accent-2);
    }
    .quick-action i {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        background: #f0f2f5;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-subtitle">Bienvenue, {{ Auth::user()->name }}. Voici un aperçu de votre cabinet.</p>
    </div>
    <div>
        <a href="{{ route('gel.ia.chat') }}" class="btn btn-primary" style="background:linear-gradient(135deg, var(--gel-accent-2), var(--gel-accent));border:none;">
            <i class="bi bi-cpu me-1"></i> Assistant IA
        </a>
    </div>
</div>

{{-- Statistiques --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e6fcf5;color:#0ca678;">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-value">{{ $stats['clients'] ?? 0 }}</div>
            <div class="stat-label">Clients actifs</div>
            <div class="mt-2"><span class="stat-change up">+{{ $stats['new_clients'] ?? 0 }} ce mois</span></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3e0;color:#e67700;">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['invoices'] ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-label">Factures ce mois</div>
            <div class="mt-2"><span class="stat-value" style="font-size:1rem;">{{ number_format($stats['revenue'] ?? 0, 0, ',', ' ') }} XAF</span></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4ff;color:#1c7ed6;">
                <i class="bi bi-journal-text"></i>
            </div>
            <div class="stat-value">{{ $stats['pending_entries'] ?? 0 }}</div>
            <div class="stat-label">Écritures en attente</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3e8ff;color:#7048e8;">
                <i class="bi bi-bell"></i>
            </div>
            <div class="stat-value">{{ $stats['alerts'] ?? 0 }}</div>
            <div class="stat-label">Alertes & relances</div>
        </div>
    </div>
</div>

{{-- Activité récente --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-dashboard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Activité récente</span>
                <a href="{{ route('gel.activity') }}" class="text-decoration-none" style="font-size:0.8rem;font-weight:500;">Voir tout</a>
            </div>
            <div class="card-body">
                @forelse($activities ?? [] as $activity)
                <div class="activity-item">
                    <div class="activity-dot" style="background:{{ $activity['color'] ?? '#635bff' }};"></div>
                    <div class="activity-text">
                        <strong>{{ $activity['user'] ?? 'Système' }}</strong>
                        {{ $activity['action'] ?? '' }}
                        @if(isset($activity['target']))
                            <span style="color:var(--gel-accent-2);">{{ $activity['target'] }}</span>
                        @endif
                    </div>
                    <div class="activity-time">{{ $activity['time'] ?? '' }}</div>
                </div>
                @empty
                <div class="text-center py-4" style="color:var(--gel-text-muted);">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <p class="mt-2">Aucune activité récente</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-dashboard">
            <div class="card-header">Actions rapides</div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('gel.clients') }}" class="quick-action">
                    <i class="bi bi-person-plus"></i>
                    <span>Nouveau client</span>
                </a>
                <a href="{{ route('gel.journal') }}" class="quick-action">
                    <i class="bi bi-journal-plus"></i>
                    <span>Nouvelle écriture</span>
                </a>
                <a href="{{ route('gel.factures') }}" class="quick-action">
                    <i class="bi bi-receipt"></i>
                    <span>Nouvelle facture</span>
                </a>
                <a href="{{ route('gel.ia.suggestions') }}" class="quick-action">
                    <i class="bi bi-lightbulb"></i>
                    <span>Suggestions IA</span>
                </a>
                <a href="{{ route('gel.admin.cabinet') }}" class="quick-action">
                    <i class="bi bi-gear"></i>
                    <span>Paramètres du cabinet</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Actualisation automatique des statistiques
    function refreshStats() {
        fetch('/api/stats', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(data => {
            // Mise à jour légère sans rechargement complet
        })
        .catch(() => {});
    }
</script>
@endsection
