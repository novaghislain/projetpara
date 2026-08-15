@extends('layouts.gel-secretary')

@section('content')
<div class="pro-header animate-fade">
    <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm" style="background:#FFF3E0; color:#FF7900;">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div>
                <div class="pro-title">Facture {{ $facture->numero }}</div>
                <div class="pro-subtitle">
                    @if($facture->statut == 'brouillon')
                        <span class="badge bg-warning text-dark"><i class="fas fa-edit"></i> Brouillon</span>
                    @elseif($facture->statut == 'validée')
                        <span class="badge bg-success"><i class="fas fa-check"></i> Validée</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($facture->statut) }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('gel-secretary.clients.ventes.index', ['client_id' => $clientId]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print"></i> Imprimer
            </button>
            @if($facture->statut == 'brouillon')
            <form action="{{ route('gel-secretary.clients.ventes.valider', ['id' => $facture->id, 'client_id' => $clientId]) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="pro-btn pro-btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir valider cette facture ? Cette action va générer automatiquement les écritures comptables et est irréversible.')">
                    <i class="fas fa-check-double"></i> Valider & Comptabiliser
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
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="pro-panel" id="invoiceToPrint">
        <div class="panel-body p-4 p-md-5">
            <div class="row mb-5">
                <div class="col-sm-6">
                    <h1 class="text-uppercase" style="color: #FF7900;">FACTURE</h1>
                    <div class="mt-4">
                        <p class="mb-1"><strong>N° Facture :</strong> {{ $facture->numero }}</p>
                        <p class="mb-1"><strong>Date :</strong> {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</p>
                        @if($facture->date_echeance)
                            <p class="mb-1"><strong>Date d'échéance :</strong> {{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="mb-4">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 50px;">
                    </div>
                    <div class="border rounded p-3 text-start d-inline-block" style="min-width: 250px; background-color: #f8f9fa;">
                        <h6 class="text-uppercase text-muted mb-2">Facturé à :</h6>
                        @if($facture->contact)
                            <h5 class="mb-1">{{ $facture->contact->name }}</h5>
                            <p class="mb-1 text-muted">{{ $facture->contact->email }}</p>
                            @if($facture->contact->phone)
                                <p class="mb-1 text-muted">{{ $facture->contact->phone }}</p>
                            @endif
                        @else
                            <p><i>Contact non défini</i></p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Désignation</th>
                            <th class="text-center" width="10%">Qté</th>
                            <th class="text-end" width="20%">Prix Unitaire HT</th>
                            <th class="text-end" width="20%">Total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facture->lignes as $ligne)
                        <tr>
                            <td>{{ $ligne->designation }}</td>
                            <td class="text-center">{{ $ligne->quantite }}</td>
                            <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->montant_ht, 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    @if($facture->notes)
                        <div class="p-3 bg-light rounded h-100">
                            <h6 class="text-muted text-uppercase mb-2">Notes / Conditions :</h6>
                            <p class="mb-0">{{ $facture->notes }}</p>
                        </div>
                    @endif
                </div>
                <div class="col-md-5 offset-md-1">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td><strong>Total HT</strong></td>
                                <td class="text-end">{{ number_format($facture->montant_ht, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            <tr>
                                <td><strong>TVA (18%)</strong></td>
                                <td class="text-end">{{ number_format($facture->montant_tva, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            <tr class="border-top border-dark border-2">
                                <td><h4 class="mb-0 text-primary">Total TTC</h4></td>
                                <td class="text-end"><h4 class="mb-0 text-primary">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA</h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($facture->ecriture_id)
            <div class="alert alert-info mt-5 d-print-none">
                <i class="fas fa-info-circle me-2"></i> 
                Une écriture comptable a été générée pour cette facture (Réf. Écriture : #{{ $facture->ecriture_id }}).
            </div>
            @endif
        </div>
    </div>
</div>

<style type="text/css" media="print">
    body * {
        visibility: hidden;
    }
    #invoiceToPrint, #invoiceToPrint * {
        visibility: visible;
    }
    #invoiceToPrint {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .pro-panel {
        border: none !important;
        box-shadow: none !important;
    }
</style>
@endsection
