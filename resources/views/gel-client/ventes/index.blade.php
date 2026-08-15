@extends('layouts.gel-client')

@section('title', 'Mes Ventes & Factures')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Mes Ventes & Factures</h1>
            <p class="text-muted mt-1">Gérez vos factures clients.</p>
        </div>
        <div>
            <a href="{{ route('gel-client.ventes.factures.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Nouvelle facture
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Facture</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Montant (TTC)</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factures as $facture)
                        <tr>
                            <td>{{ $facture->numero ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                            <td>{{ $facture->client_nom ?? 'N/A' }}</td>
                            <td>{{ number_format($facture->montant_ttc ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge bg-secondary">{{ $facture->status ?? 'Brouillon' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-sm btn-light" title="Voir"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <div class="mb-3"><i class="fas fa-file-invoice fa-3x text-light"></i></div>
                                Aucune facture pour le moment.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $factures->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
