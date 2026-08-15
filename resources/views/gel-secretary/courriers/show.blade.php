@extends('layouts.gel-secretary')
@section('title', 'Détails du Courrier')

@section('content')
<div class="sec-page-header animate-fade">
    <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm" style="background:#EFF6FF; color:#3B82F6;">
                @if($courrier->type === 'entrant')
                    <i class="fas fa-inbox"></i>
                @elseif($courrier->type === 'sortant')
                    <i class="fas fa-paper-plane"></i>
                @else
                    <i class="fas fa-handshake"></i>
                @endif
            </div>
            <div>
                <div class="sec-page-title">
                    @if($courrier->type === 'entrant')
                        Registre ARRIVÉE : {{ $courrier->reference }}
                    @elseif($courrier->type === 'sortant')
                        Registre DÉPART : {{ $courrier->numero_ordre ?? $courrier->reference }}
                    @else
                        Registre GÉNÉRAL : {{ $courrier->numero_ordre ?? $courrier->reference }}
                    @endif
                </div>
                <div class="sec-page-sub">Enregistré le {{ $courrier->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('gel-secretary.courriers.index', ['client_id' => request('client_id'), 'type' => $courrier->type]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            
            @if($courrier->statut === 'recu' || $courrier->statut === 'en_cours' || $courrier->statut === 'brouillon')
                <form action="{{ route('gel-secretary.courriers.updateStatut', $courrier->id) }}" method="POST" class="m-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                    <input type="hidden" name="statut" value="traite">
                    <button type="submit" class="sec-btn sec-btn-primary" onclick="return confirm('Confirmer le traitement/l\'envoi de ce courrier ?')">
                        <i class="fas fa-check-double"></i> 
                        @if($courrier->type === 'sortant') Marquer comme Envoyé @else Marquer comme Traité @endif
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="dashboard-wrapper animate-fade delay-1">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Colonne Principale -->
        <div class="col-lg-8">
            <div class="sec-card mb-4">
                <div class="panel-header">
                    <div class="panel-title">Informations du registre</div>
                </div>
                <div class="panel-body">
                    <div class="row g-4">
                        
                        <!-- Ligne Commune -->
                        <div class="col-md-12">
                            <div class="text-muted small text-uppercase fw-bold mb-1">Objet</div>
                            <div class="fs-5 fw-bold text-dark">{{ $courrier->objet }}</div>
                        </div>

                        <!-- Champs spécifiques ARRIVEE -->
                        @if($courrier->type === 'entrant')
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Expéditeur</div>
                                <div class="fw-bold text-primary fs-6">{{ $courrier->expediteur }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Date d'arrivée</div>
                                <div class="fw-bold">{{ $courrier->date_reception ? \Carbon\Carbon::parse($courrier->date_reception)->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Date Correspondance</div>
                                <div>{{ $courrier->date_courrier ? $courrier->date_courrier->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Date Réponse</div>
                                <div>{{ $courrier->date_reponse ? \Carbon\Carbon::parse($courrier->date_reponse)->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">N° Réponse</div>
                                <div>{{ $courrier->numero_reponse ?: '-' }}</div>
                            </div>

                        <!-- Champs spécifiques DEPART -->
                        @elseif($courrier->type === 'sortant')
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">N° Ordre</div>
                                <div class="fw-bold text-danger fs-6">{{ $courrier->numero_ordre ?: '-' }}</div>
                            </div>
                            <div class="col-md-8">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Destinataire</div>
                                <div class="fw-bold text-primary fs-6">{{ $courrier->destinataire }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Date du départ</div>
                                <div>{{ $courrier->date_courrier ? $courrier->date_courrier->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Nbre de pièces</div>
                                <div>{{ $courrier->nombre_pieces ?: '0' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">N° Archives</div>
                                <div>{{ $courrier->numero_archives ?: '-' }}</div>
                            </div>
                            <div class="col-md-12">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Observations</div>
                                <div class="bg-light p-2 rounded">{{ $courrier->observations ?: '-' }}</div>
                            </div>

                        <!-- Champs spécifiques GENERAL/INTERNE -->
                        @else
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">N° Ordre</div>
                                <div class="fw-bold text-info fs-6">{{ $courrier->numero_ordre ?: '-' }}</div>
                            </div>
                            <div class="col-md-8">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Noms et Adresses</div>
                                <div class="fw-bold text-primary fs-6">{{ $courrier->noms_adresses ?? $courrier->destinataire }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Date</div>
                                <div>{{ $courrier->date_courrier ? $courrier->date_courrier->format('d/m/Y') : '-' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Nbre de pièces</div>
                                <div>{{ $courrier->nombre_pieces ?: '0' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Signature Destinataire</div>
                                <div>
                                    @if($courrier->signature_destinataire)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> {{ $courrier->signature_destinataire }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> En attente</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- Contenu textuel -->
            @if($courrier->contenu)
            <div class="sec-card mb-4">
                <div class="panel-header">
                    <div class="panel-title">Contenu / Notes</div>
                </div>
                <div class="panel-body">
                    <div class="bg-light p-3 rounded text-dark" style="white-space: pre-wrap;">{{ $courrier->contenu }}</div>
                </div>
            </div>
            @endif

            <!-- Visionneuse Pièce Jointe -->
            @if($courrier->fichier_joint)
            <div class="sec-card mb-4">
                <div class="panel-header d-flex justify-content-between align-items-center">
                    <div class="panel-title">Pièce Jointe Numérisée</div>
                    <a href="{{ asset('storage/' . $courrier->fichier_joint) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-external-link-alt"></i> Ouvrir dans un nouvel onglet
                    </a>
                </div>
                <div class="panel-body p-0 bg-dark text-center">
                    @php $ext = pathinfo($courrier->fichier_joint, PATHINFO_EXTENSION); @endphp
                    
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <img src="{{ asset('storage/' . $courrier->fichier_joint) }}" class="img-fluid" style="max-height: 800px;">
                    @elseif(strtolower($ext) == 'pdf')
                        <iframe src="{{ asset('storage/' . $courrier->fichier_joint) }}" style="width:100%; height:600px; border:none;"></iframe>
                    @else
                        <div class="p-5 text-white">Fichier non prévisualisable directement.</div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        <!-- Colonne Latérale -->
        <div class="col-lg-4">
            
            <!-- Statut Card -->
            <div class="sec-card mb-4">
                <div class="panel-header">
                    <div class="panel-title">Statut & Suivi</div>
                </div>
                <div class="panel-body">
                    <div class="mb-4 text-center">
                        @if($courrier->statut == 'recu')
                            <span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-exclamation-circle me-1"></i> À Traiter</span>
                        @elseif($courrier->statut == 'traite')
                            <span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-check-double me-1"></i> Traité</span>
                        @elseif($courrier->statut == 'envoye')
                            <span class="badge bg-primary fs-6 px-3 py-2"><i class="fas fa-paper-plane me-1"></i> Envoyé</span>
                        @elseif($courrier->statut == 'en_cours')
                            <span class="badge bg-info fs-6 px-3 py-2"><i class="fas fa-spinner fa-spin me-1"></i> En cours</span>
                        @elseif($courrier->statut == 'brouillon')
                            <span class="badge bg-secondary fs-6 px-3 py-2"><i class="fas fa-edit me-1"></i> Brouillon</span>
                        @else
                            <span class="badge bg-secondary fs-6 px-3 py-2">{{ ucfirst($courrier->statut) }}</span>
                        @endif
                    </div>

                    <form action="{{ route('gel-secretary.courriers.updateStatut', $courrier->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                        <label class="form-label text-muted small fw-bold">Mettre à jour le statut</label>
                        <div class="input-group">
                            <select name="statut" class="form-select">
                                <option value="recu" {{ $courrier->statut == 'recu' ? 'selected' : '' }}>À traiter / Reçu</option>
                                <option value="brouillon" {{ $courrier->statut == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                <option value="en_cours" {{ $courrier->statut == 'en_cours' ? 'selected' : '' }}>En cours de traitement</option>
                                <option value="traite" {{ $courrier->statut == 'traite' ? 'selected' : '' }}>Traité</option>
                                <option value="envoye" {{ $courrier->statut == 'envoye' ? 'selected' : '' }}>Envoyé</option>
                                <option value="archive" {{ $courrier->statut == 'archive' ? 'selected' : '' }}>Archivé</option>
                            </select>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assignation -->
            <div class="sec-card mb-4">
                <div class="panel-header">
                    <div class="panel-title">Assignation</div>
                </div>
                <div class="panel-body">
                    @if($courrier->assigned_to)
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded">
                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold">
                                {{ strtoupper(substr($courrier->assignedTo->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $courrier->assignedTo->name ?? 'Utilisateur Inconnu' }}</div>
                                <div class="text-muted small">En charge du traitement</div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary text-center mb-4">
                            <i class="fas fa-user-slash d-block mb-2 fs-4"></i>
                            Aucun agent assigné
                        </div>
                    @endif

                    <form action="{{ route('gel-secretary.courriers.assign', $courrier->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ request('client_id') }}">
                        <label class="form-label text-muted small fw-bold">Assigner à un agent / comptable</label>
                        <div class="input-group">
                            <select name="assigned_to" class="form-select" required>
                                <option value="">Sélectionner...</option>
                                @foreach(\App\Models\User::where('account_type', 'cabinet')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-user-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
