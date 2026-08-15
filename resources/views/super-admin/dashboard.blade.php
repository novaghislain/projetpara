@extends('layouts.gel-super-admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Vue d'ensemble de la Plateforme</h1>
    <div style="color: #6B7280; font-size: 14px;">Indicateurs clés et santé du système SaaS</div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="sa-card mb-0">
            <div class="sa-card-body d-flex align-items-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,121,0,0.1); color: var(--sa-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 16px;">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <div style="font-size: 13px; color: #6B7280; font-weight: 600; text-transform: uppercase;">Total Tenants</div>
                    <div style="font-size: 24px; font-weight: 700; color: #111827;">{{ $stats['total_entreprises'] }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="sa-card mb-0">
            <div class="sa-card-body d-flex align-items-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 16px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div style="font-size: 13px; color: #6B7280; font-weight: 600; text-transform: uppercase;">Abonnements Actifs</div>
                    <div style="font-size: 24px; font-weight: 700; color: #111827;">{{ $stats['active_subscriptions'] }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="sa-card mb-0">
            <div class="sa-card-body d-flex align-items-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #E0E7FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 16px;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div style="font-size: 13px; color: #6B7280; font-weight: 600; text-transform: uppercase;">Utilisateurs Globaux</div>
                    <div style="font-size: 24px; font-weight: 700; color: #111827;">{{ $stats['total_users'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="sa-card mb-0">
            <div class="sa-card-body d-flex align-items-center">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 16px;">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div style="font-size: 13px; color: #6B7280; font-weight: 600; text-transform: uppercase;">MRR Estimé</div>
                    <div style="font-size: 24px; font-weight: 700; color: #111827;">{{ number_format($stats['total_mrr'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="sa-card">
            <div class="sa-card-header">
                <div class="sa-card-title">Dernières Entreprises Inscrites</div>
            </div>
            <div class="sa-card-body p-0">
                <table class="table mb-0" style="font-size: 14px;">
                    <thead style="background: #F9FAFB;">
                        <tr>
                            <th class="border-0 px-4 py-3 text-muted">Entreprise</th>
                            <th class="border-0 px-4 py-3 text-muted">Date d'inscription</th>
                            <th class="border-0 px-4 py-3 text-muted">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_entreprises as $ent)
                        <tr>
                            <td class="px-4 py-3 font-weight-bold">{{ $ent->raison_sociale }}</td>
                            <td class="px-4 py-3 text-muted">{{ $ent->cree_le ? \Carbon\Carbon::parse($ent->cree_le)->format('d/m/Y') : 'Date inconnue' }}</td>
                            <td class="px-4 py-3"><span class="badge bg-success">Actif</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <div class="sa-card-title">Santé du Système</div>
            </div>
            <div class="sa-card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Charge CPU</span>
                    <span class="font-weight-bold">12%</span>
                </div>
                <div class="progress mb-4" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: 12%"></div>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Espace Disque</span>
                    <span class="font-weight-bold">45%</span>
                </div>
                <div class="progress mb-4" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: 45%"></div>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Mémoire</span>
                    <span class="font-weight-bold">68%</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 68%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
