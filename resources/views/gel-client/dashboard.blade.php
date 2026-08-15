@extends('layouts.gel-client')

@section('title', 'Tableau de bord — Entreprise')

@push('styles')
<style>
    /* Spécifique au dashboard client */
    .client-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .client-kpi-card {
        background: var(--gel-surface);
        border: 1px solid var(--gel-border);
        border-radius: 12px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .client-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .client-kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--gel-text-secondary);
        font-size: 14px;
        font-weight: 500;
    }

    .client-kpi-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--gel-text-primary);
    }

    .client-dashboard-section {
        background: var(--gel-surface);
        border: 1px solid var(--gel-border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--gel-text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tableau de bord de l'entreprise</h1>
            <p class="text-muted mt-1">Aperçu rapide de vos activités et de la coordination avec votre cabinet.</p>
        </div>
        <div>
            <a href="{{ route('gel-client.documents.index') }}" class="btn btn-primary">
                <i class="fas fa-upload me-2"></i> Transmettre un document
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="client-kpi-grid">
        <div class="client-kpi-card">
            <div class="client-kpi-header">
                <span>Chiffre d'affaires (Mois)</span>
                <i class="fas fa-chart-line text-success"></i>
            </div>
            <div class="client-kpi-value">{{ number_format($stats['ca_mensuel'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="client-kpi-card">
            <div class="client-kpi-header">
                <span>Dépenses (Mois)</span>
                <i class="fas fa-wallet text-danger"></i>
            </div>
            <div class="client-kpi-value">{{ number_format($stats['depenses_mensuelles'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="client-kpi-card">
            <div class="client-kpi-header">
                <span>Messages non lus</span>
                <i class="fas fa-envelope text-primary"></i>
            </div>
            <div class="client-kpi-value">{{ $stats['messages_non_lus'] }}</div>
        </div>

        <div class="client-kpi-card">
            <div class="client-kpi-header">
                <span>Tâches en attente</span>
                <i class="fas fa-tasks text-warning"></i>
            </div>
            <div class="client-kpi-value">{{ $stats['taches_en_attente'] }}</div>
        </div>
    </div>

    <div class="row">
        <!-- Documents Récents -->
        <div class="col-md-8">
            <div class="client-dashboard-section h-100">
                <div class="section-title">
                    <i class="fas fa-file-alt text-primary"></i> Documents récents
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['documents_recents'] as $doc)
                            <tr>
                                <td>{{ $doc->file_name ?? 'Document sans nom' }}</td>
                                <td>{{ \Carbon\Carbon::parse($doc->created_at)->format('d/m/Y') }}</td>
                                <td>
                                    @if($doc->status == 'pending')
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @elseif($doc->status == 'processed')
                                        <span class="badge bg-success">Traité</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $doc->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Aucun document récent.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <a href="{{ route('gel-client.documents.index') }}" class="btn btn-sm btn-outline-primary">Voir tous les documents</a>
                </div>
            </div>
        </div>

        <!-- Raccourcis Rapides -->
        <div class="col-md-4">
            <div class="client-dashboard-section h-100">
                <div class="section-title">
                    <i class="fas fa-bolt text-warning"></i> Actions rapides
                </div>
                <div class="d-grid gap-3">
                    <a href="{{ route('gel-client.ventes.factures.create') }}" class="btn btn-outline-secondary text-start py-3">
                        <i class="fas fa-file-invoice text-primary me-3"></i> Créer une facture
                    </a>
                    <a href="{{ route('gel-client.achats.depenses.create') }}" class="btn btn-outline-secondary text-start py-3">
                        <i class="fas fa-receipt text-danger me-3"></i> Enregistrer une dépense
                    </a>
                    <a href="{{ route('gel-client.coordination.index') }}" class="btn btn-outline-secondary text-start py-3">
                        <i class="fas fa-comment-dots text-info me-3"></i> Contacter mon comptable
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
