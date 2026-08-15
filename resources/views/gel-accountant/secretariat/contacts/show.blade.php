@extends('gel-accountant.layouts.app')

@section('title', 'Fiche Contact 360° - ' . $contact->nom)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Fiche Contact 360°</h1>
            <a href="{{ route('gel-accountant.secretariat.contacts.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à l'annuaire
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Informations Générales -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-id-card mr-2"></i> Identité</h6>
                    @php
                        $badge = 'secondary';
                        if($contact->type == 'client') $badge = 'success';
                        if($contact->type == 'fournisseur') $badge = 'warning text-dark';
                        if($contact->type == 'partenaire') $badge = 'info';
                        if($contact->type == 'administration') $badge = 'danger';
                    @endphp
                    <span class="badge bg-{{ $badge }} text-white">{{ ucfirst($contact->type) }}</span>
                </div>
                <div class="card-body text-center pt-4">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3 border border-primary" style="width: 100px; height: 100px;">
                        <i class="fas fa-building fa-3x text-primary"></i>
                    </div>
                    <h4 class="font-weight-bold">{{ $contact->nom }}</h4>
                    <p class="text-muted mb-4">Créé le {{ $contact->created_at->format('d/m/Y') }}</p>

                    <ul class="list-group list-group-flush text-left">
                        <li class="list-group-item px-0">
                            <i class="fas fa-envelope text-primary mr-2" style="width: 20px;"></i> {{ $contact->email ?? 'Non renseigné' }}
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-phone text-primary mr-2" style="width: 20px;"></i> {{ $contact->telephone ?? 'Non renseigné' }}
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-globe text-primary mr-2" style="width: 20px;"></i>
                            @if($contact->site_web)
                                <a href="{{ $contact->site_web }}" target="_blank">{{ $contact->site_web }}</a>
                            @else
                                Non renseigné
                            @endif
                        </li>
                        <li class="list-group-item px-0 pb-0">
                            <i class="fas fa-map-marker-alt text-primary mr-2" style="width: 20px;"></i>
                            {{ $contact->adresse ?? 'Adresse inconnue' }}<br>
                            <span class="ml-4 pl-1 text-muted">{{ $contact->code_postal }} {{ $contact->ville }}, {{ $contact->pays }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Informations Complémentaires -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-invoice-dollar mr-2"></i> Informations Commerciales & Financières</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">IFU (Numéro Fiscal)</div>
                            <div class="font-weight-bold">{{ $contact->ifu ?? 'Non renseigné' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Devise de Facturation par défaut</div>
                            <div class="font-weight-bold">{{ $contact->devise_facturation }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Délai de Paiement accordé</div>
                            <div class="font-weight-bold">{{ $contact->delai_paiement ?? 'Non défini' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Méthode de Paiement préférée</div>
                            <div class="font-weight-bold">{{ $contact->methode_paiement ?? 'Non définie' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">IBAN / RIB</div>
                            <div class="font-weight-bold">{{ $contact->iban ?? 'Non renseigné' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted mb-1">Code SWIFT</div>
                            <div class="font-weight-bold">{{ $contact->swift ?? 'Non renseigné' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactions (360 view concept) -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history mr-2"></i> Activités & Interactions (Vue 360°)</h6>
                    <span class="badge bg-info text-white">IA-ORCH-01 Prêt</span>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="events-tab" data-toggle="tab" href="#events" role="tab">Réunions / Agenda</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-muted" id="factures-tab" data-toggle="tab" href="#factures" role="tab">Factures (Bientôt)</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link text-muted" id="courriers-tab" data-toggle="tab" href="#courriers" role="tab">Courriers (Bientôt)</a>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="events" role="tabpanel">
                            @if($contact->events && $contact->events->count() > 0)
                                <div class="list-group">
                                    @foreach($contact->events as $event)
                                        <a href="{{ route('gel-accountant.secretariat.events.show', $event->id) }}" class="list-group-item list-group-item-action flex-column align-items-start">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 text-primary">{{ $event->titre }}</h6>
                                                <small class="text-muted">{{ $event->date_debut->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <p class="mb-1 small">{{ Str::limit($event->description, 100) }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">Aucun événement planifié avec ce contact.</p>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('gel-accountant.secretariat.events.create') }}" class="btn btn-sm btn-outline-primary">Planifier une réunion</a>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="factures" role="tabpanel">
                            <p class="text-muted mb-0">L'intégration avec le moteur fiscal et la saisie comptable affichera ici l'historique des factures de ce contact.</p>
                        </div>
                        <div class="tab-pane fade" id="courriers" role="tabpanel">
                            <p class="text-muted mb-0">L'intégration avec le registre des courriers affichera ici tous les courriers envoyés/reçus par ce contact.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
