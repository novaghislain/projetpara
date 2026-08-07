@extends('layouts.portal')

@section('title', 'Service Informatique')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Mon Service Informatique</h1>
            <p class="text-muted">Suivi de vos missions et commandes d'équipements gérées par GEL SABINET.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('client.it.subscribe', ['slug' => $slug]) }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Mission</a>
            <a href="{{ route('client.it.order', ['slug' => $slug]) }}" class="btn btn-outline-primary"><i class="fas fa-shopping-cart"></i> Commander Matériel</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tasks text-primary me-2"></i> Mes Missions Actives</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Sujet</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($missions as $mission)
                                <tr>
                                    <td>
                                        @if($mission->type == 'securite') <span class="badge bg-danger">Sécurité</span>
                                        @elseif($mission->type == 'maintenance') <span class="badge bg-warning text-dark">Maintenance</span>
                                        @else <span class="badge bg-info text-white">Développement</span>
                                        @endif
                                    </td>
                                    <td>{{ $mission->subject }}</td>
                                    <td>
                                        @if($mission->status == 'en_attente') <span class="badge bg-secondary">En attente d'affectation</span>
                                        @elseif($mission->status == 'en_cours') <span class="badge bg-primary">En cours</span>
                                        @else <span class="badge bg-success">Terminée</span>
                                        @endif
                                    </td>
                                    <td>{{ $mission->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucune mission en cours.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-box text-success me-2"></i> Mes Commandes d'Équipement</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Aucune commande en cours.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history text-info me-2"></i> Interventions Récentes / Prévues</h5>
                </div>
                <div class="card-body">
                    @forelse($interventions as $intervention)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $intervention->scheduled_at ? $intervention->scheduled_at->format('d/m/Y H:i') : 'Non planifié' }}</strong>
                                @if($intervention->status == 'realisee')
                                    <span class="badge bg-success">Réalisée</span>
                                @else
                                    <span class="badge bg-warning text-dark">Prévue</span>
                                @endif
                            </div>
                            <p class="mb-1 text-muted small">{{ Str::limit($intervention->description, 100) }}</p>
                            @if($intervention->informaticien)
                                <small class="text-primary"><i class="fas fa-user-cog"></i> {{ $intervention->informaticien->name }}</small>
                            @endif
                        </div>
                    @endforelse
                    @if($interventions->isEmpty())
                        <p class="text-muted text-center mb-0">Aucune intervention enregistrée.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
