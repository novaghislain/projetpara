@extends('gel-accountant.layouts.app')

@section('title', 'Détails du Courrier - ' . $courrier->numero_enregistrement)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Courrier : {{ $courrier->numero_enregistrement }}</h1>
            <a href="{{ route('gel-accountant.secretariat.courriers.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour au Registre
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Workflow Stepper -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Workflow du Courrier</h6>
        </div>
        <div class="card-body">
            @php
                $steps = ['creation', 'validation', 'visa', 'envoi', 'affectation', 'archive'];
                $currentIndex = array_search($courrier->statut, $steps);
            @endphp
            <div class="d-flex justify-content-between align-items-center position-relative mb-4" style="z-index: 1;">
                <div class="progress position-absolute w-100" style="height: 4px; top: 50%; transform: translateY(-50%); z-index: -1;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($currentIndex / (count($steps) - 1)) * 100 }}%"></div>
                </div>
                
                @foreach($steps as $index => $step)
                    @php
                        $isCompleted = $index <= $currentIndex;
                        $isCurrent = $index === $currentIndex;
                        $icon = 'fa-check';
                        if ($step == 'creation') $icon = 'fa-file-alt';
                        if ($step == 'validation') $icon = 'fa-user-check';
                        if ($step == 'visa') $icon = 'fa-stamp';
                        if ($step == 'envoi') $icon = 'fa-paper-plane';
                        if ($step == 'affectation') $icon = 'fa-user-tag';
                        if ($step == 'archive') $icon = 'fa-archive';
                    @endphp
                    <div class="text-center bg-white px-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $isCompleted ? 'bg-success text-white' : 'bg-light text-muted border' }}" style="width: 40px; height: 40px; border-width: 2px !important;">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <span class="small font-weight-bold {{ $isCurrent ? 'text-primary' : ($isCompleted ? 'text-success' : 'text-muted') }}">
                            {{ ucfirst($step) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Actions de Workflow -->
            <div class="text-center mt-4 pt-3 border-top">
                <form action="{{ route('gel-accountant.secretariat.courriers.update-status', $courrier->id) }}" method="POST" class="d-inline">
                    @csrf
                    <div class="input-group" style="max-width: 400px; margin: 0 auto;">
                        <select name="statut" class="form-control" {{ $courrier->statut == 'archive' ? 'disabled' : '' }}>
                            @foreach($steps as $step)
                                <option value="{{ $step }}" {{ $courrier->statut == $step ? 'selected' : '' }}>Passer à : {{ ucfirst($step) }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary" {{ $courrier->statut == 'archive' ? 'disabled' : '' }}>
                                Mettre à jour
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Détails -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations Détaillées</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Objet</div>
                        <div class="col-sm-8 font-weight-bold">{{ $courrier->objet }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Expéditeur / Destinataire</div>
                        <div class="col-sm-8">{{ $courrier->expediteur_destinataire }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Type</div>
                        <div class="col-sm-8">{{ ucfirst($courrier->type) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Catégorie</div>
                        <div class="col-sm-8">{{ $courrier->categorie ?? 'Non spécifiée' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Priorité</div>
                        <div class="col-sm-8">
                            <span class="badge {{ $courrier->priorite == 'urgente' ? 'bg-danger' : ($courrier->priorite == 'haute' ? 'bg-warning text-dark' : 'bg-info') }} text-white">
                                {{ ucfirst($courrier->priorite) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Date de réception/envoi</div>
                        <div class="col-sm-8">{{ $courrier->date_reception_envoi ? $courrier->date_reception_envoi->format('d/m/Y') : 'Non définie' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Enregistré par</div>
                        <div class="col-sm-8">{{ $courrier->creePar->nom ?? $courrier->creePar->email ?? 'Système' }} le {{ $courrier->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Affectation -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Affectation</h6>
                </div>
                <div class="card-body">
                    @if($courrier->assigne_a)
                        <div class="text-center mb-4">
                            <i class="fas fa-user-circle fa-4x text-gray-300 mb-3"></i>
                            <h5>{{ $courrier->assigneA->nom ?? $courrier->assigneA->email }}</h5>
                            <p class="text-muted">Est actuellement en charge de ce courrier.</p>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <i class="fas fa-question-circle fa-4x text-warning mb-3"></i>
                            <h5>Non assigné</h5>
                            <p class="text-muted">Personne n'est en charge pour le moment.</p>
                        </div>
                    @endif

                    <hr>

                    <form action="{{ route('gel-accountant.secretariat.courriers.assign', $courrier->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="assigne_a">Nouvelle affectation :</label>
                            <select name="assigne_a" id="assigne_a" class="form-control" required {{ $courrier->statut == 'archive' ? 'disabled' : '' }}>
                                <option value="">Choisir un collaborateur...</option>
                                @foreach($utilisateurs as $user)
                                    <option value="{{ $user->id }}" {{ $courrier->assigne_a == $user->id ? 'selected' : '' }}>
                                        {{ $user->nom ?? $user->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" {{ $courrier->statut == 'archive' ? 'disabled' : '' }}>
                            Affecter le courrier
                        </button>
                    </form>
                </div>
            </div>

            <!-- Documents rattachés -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Pièces Jointes</h6>
                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#attachDocumentModal" {{ $courrier->statut == 'archive' ? 'disabled' : '' }}>
                        <i class="fas fa-paperclip"></i> Joindre
                    </button>
                </div>
                <div class="card-body">
                    @if($courrier->documents && $courrier->documents->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($courrier->documents as $doc)
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file text-secondary mr-2"></i>
                                        {{ $doc->name }}
                                        <div class="small text-muted">{{ number_format($doc->file_size / 1024, 2) }} KB</div>
                                    </div>
                                    <div>
                                        <a href="{{ route('gel-accountant.secretariat.documents.download', $doc->id) }}" class="btn btn-sm btn-circle btn-success" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="{{ route('gel-accountant.secretariat.courriers.documents.detach', [$courrier->id, $doc->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Détacher ce document du courrier ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-circle btn-danger" title="Détacher" {{ $courrier->statut == 'archive' ? 'disabled' : '' }}>
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-3">
                            <p class="mb-0">Aucun document rattaché.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour joindre un document -->
<div class="modal fade" id="attachDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Joindre un document existant</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('gel-accountant.secretariat.courriers.documents.attach', $courrier->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="document_id">Sélectionner un document dans la GED</label>
                        <select name="document_id" id="document_id" class="form-control" required>
                            <option value="">-- Choisir un document --</option>
                            @php
                                $clientId = session('active_client_id') ?? session('current_client_id');
                                $availableDocs = \App\Models\Document::where('client_id', $clientId)
                                    ->whereNotIn('id', $courrier->documents->pluck('id'))
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            @endphp
                            @foreach($availableDocs as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->name }} ({{ $doc->created_at->format('d/m/Y') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    <p class="mb-2">Ou importer un nouveau document :</p>
                    <a href="{{ route('gel-accountant.secretariat.documents.index') }}" class="btn btn-sm btn-secondary w-100">Aller à la GED pour importer</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rattacher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
