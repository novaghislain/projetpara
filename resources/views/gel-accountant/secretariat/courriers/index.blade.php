@extends('gel-accountant.layouts.app')

@section('title', 'Gestion du Courrier - Secrétariat')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Registre des Courriers</h1>
            <a href="{{ route('gel-accountant.secretariat.courriers.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nouvel Enregistrement
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('gel-accountant.secretariat.courriers.index') }}" method="GET" class="row">
                <div class="col-md-3 mb-3">
                    <label for="statut">Statut (Étape Workflow)</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="">Tous</option>
                        <option value="creation" {{ request('statut') == 'creation' ? 'selected' : '' }}>Création</option>
                        <option value="validation" {{ request('statut') == 'validation' ? 'selected' : '' }}>Validation</option>
                        <option value="visa" {{ request('statut') == 'visa' ? 'selected' : '' }}>Visa</option>
                        <option value="envoi" {{ request('statut') == 'envoi' ? 'selected' : '' }}>Envoi</option>
                        <option value="affectation" {{ request('statut') == 'affectation' ? 'selected' : '' }}>Affectation</option>
                        <option value="archive" {{ request('statut') == 'archive' ? 'selected' : '' }}>Archivé</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">Tous</option>
                        <option value="entrant" {{ request('type') == 'entrant' ? 'selected' : '' }}>Entrant</option>
                        <option value="sortant" {{ request('type') == 'sortant' ? 'selected' : '' }}>Sortant</option>
                        <option value="interne" {{ request('type') == 'interne' ? 'selected' : '' }}>Interne</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>N° Enregistrement</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Objet</th>
                            <th>Expéditeur / Destinataire</th>
                            <th>Statut Workflow</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courriers as $courrier)
                            <tr>
                                <td><strong>{{ $courrier->numero_enregistrement }}</strong></td>
                                <td>{{ $courrier->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($courrier->type == 'entrant')
                                        <span class="badge bg-info text-white">Entrant</span>
                                    @elseif($courrier->type == 'sortant')
                                        <span class="badge bg-warning text-dark">Sortant</span>
                                    @else
                                        <span class="badge bg-secondary text-white">Interne</span>
                                    @endif
                                </td>
                                <td>{{ $courrier->objet }}</td>
                                <td>{{ $courrier->expediteur_destinataire }}</td>
                                <td>
                                    @php
                                        $badges = [
                                            'creation' => 'bg-secondary',
                                            'validation' => 'bg-primary',
                                            'visa' => 'bg-info',
                                            'envoi' => 'bg-warning',
                                            'affectation' => 'bg-dark',
                                            'archive' => 'bg-success'
                                        ];
                                        $badgeClass = $badges[$courrier->statut] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} text-white">{{ ucfirst($courrier->statut) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('gel-accountant.secretariat.courriers.show', $courrier->id) }}" class="btn btn-sm btn-outline-primary">Détails & Workflow</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucun courrier trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $courriers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
