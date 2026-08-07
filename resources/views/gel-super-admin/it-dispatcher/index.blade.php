@extends('layouts.gel-super-admin')

@section('title', 'Affectation des Missions IT')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 mb-1">Centre d'Affectation Informatique</h1>
            <p class="text-muted mb-0">Affectation manuelle des missions et commandes clients aux membres du Pôle IT.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Missions en attente -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-top-primary">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-tasks text-primary me-2"></i> 
                    <h6 class="mb-0">Missions en attente d'affectation ({{ $unassignedMissions->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($unassignedMissions as $mission)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $mission->subject }}</h6>
                                        <small class="text-muted">Client: <strong>{{ $mission->client->company_name ?? 'Inconnu' }}</strong> | Type: {{ $mission->type }}</small>
                                    </div>
                                    <span class="badge bg-secondary">En attente</span>
                                </div>
                                <p class="small mb-3">{{ Str::limit($mission->description, 150) }}</p>
                                
                                <form action="{{ route('gel-super-admin.it-dispatcher.assign-mission', $mission->id) }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <select name="informaticien_ids[]" class="form-select form-select-sm me-2" multiple style="max-height: 80px;" required>
                                        @foreach($informaticiens as $info)
                                            <option value="{{ $info->id }}">{{ $info->name }} ({{ $info->email }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary shrink-0">Affecter</button>
                                </form>
                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Maintenez Ctrl pour en sélectionner plusieurs</small>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted p-4">
                                Aucune mission en attente.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Commandes en attente -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-top-success">
                <div class="card-header bg-white d-flex align-items-center">
                    <i class="fas fa-shopping-cart text-success me-2"></i> 
                    <h6 class="mb-0">Commandes de matériel reçues ({{ $unassignedOrders->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($unassignedOrders as $order)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">Commande N° {{ $order->order_number }}</h6>
                                        <small class="text-muted">Client: <strong>{{ $order->client->company_name ?? 'Inconnu' }}</strong></small>
                                    </div>
                                    <span class="badge bg-secondary">Reçue</span>
                                </div>
                                
                                <div class="small bg-light p-2 rounded mb-3">
                                    <strong>Articles demandés:</strong><br>
                                    @foreach($order->items as $item)
                                        - {{ $item }}<br>
                                    @endforeach
                                </div>

                                <form action="{{ route('gel-super-admin.it-dispatcher.assign-order', $order->id) }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <select name="informaticien_id" class="form-select form-select-sm me-2" required>
                                        <option value="">Sélectionnez un responsable...</option>
                                        @foreach($informaticiens as $info)
                                            <option value="{{ $info->id }}">{{ $info->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success shrink-0">Confier</button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted p-4">
                                Aucune commande en attente de traitement.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
