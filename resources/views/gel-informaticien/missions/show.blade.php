@extends('layouts.gel-informaticien')

@section('title', 'Détail de la Mission IT')

@section('content')
<div class="p-4">
    <div class="mb-4">
        <a href="{{ route('gel-informaticien.missions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Informations de la mission -->
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails de la Mission</h5>
                    <form action="{{ route('gel-informaticien.missions.update_status', $mission->id) }}" method="POST">
                        @csrf
                        <div class="input-group input-group-sm">
                            <select name="status" class="form-select">
                                <option value="en_attente" {{ $mission->status == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                <option value="en_cours" {{ $mission->status == 'en_cours' ? 'selected' : '' }}>En cours</option>
                                <option value="terminee" {{ $mission->status == 'terminee' ? 'selected' : '' }}>Terminée</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <h6>Client : <span class="text-primary">{{ $mission->client->company_name ?? 'Inconnu' }}</span></h6>
                    <hr>
                    <p><strong>Sujet :</strong> {{ $mission->subject }}</p>
                    <p><strong>Type :</strong> {{ ucfirst($mission->type) }}</p>
                    <p><strong>Volume/Périmètre :</strong> {{ $mission->volume ?: 'Non spécifié' }}</p>
                    
                    <div class="bg-light p-3 rounded mt-3">
                        <strong>Description :</strong><br>
                        {{ $mission->description }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal des Interventions -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4 border-top-info">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history text-info me-2"></i> Journal des Interventions</h5>
                </div>
                <div class="card-body">
                    <!-- Formulaire d'ajout -->
                    <form action="{{ route('gel-informaticien.missions.store_intervention', $mission->id) }}" method="POST" class="mb-4 bg-light p-3 rounded">
                        @csrf
                        <h6>Ajouter une intervention</h6>
                        <div class="mb-2">
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Description de l'action menée..." required></textarea>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="realisee">Action réalisée (Terminée)</option>
                                    <option value="programmee">Action programmée (À venir)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" placeholder="Date programmée (opt.)">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-info text-white w-100">Enregistrer l'intervention</button>
                    </form>

                    <!-- Historique -->
                    <div class="timeline">
                        @forelse($mission->interventions as $intervention)
                            <div class="border-start border-2 border-primary ps-3 pb-3 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $intervention->informaticien->name ?? 'Système' }}</strong>
                                    <small class="text-muted">{{ $intervention->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <p class="mb-1 text-sm">{{ $intervention->description }}</p>
                                <div>
                                    @if($intervention->status == 'realisee')
                                        <span class="badge bg-success">Réalisée le {{ $intervention->completed_at ? $intervention->completed_at->format('d/m/Y') : '' }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Programmée: {{ $intervention->scheduled_at ? $intervention->scheduled_at->format('d/m/Y H:i') : '' }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center">Aucune intervention enregistrée pour le moment.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
