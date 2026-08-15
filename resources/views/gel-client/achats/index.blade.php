@extends('layouts.gel-client')

@section('title', 'Mes Dépenses')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Mes Dépenses</h1>
            <p class="text-muted mt-1">Soumettez vos justificatifs d'achats ou notes de frais.</p>
        </div>
        <div>
            <a href="{{ route('gel-client.achats.depenses.create') }}" class="btn btn-danger">
                <i class="fas fa-plus me-2"></i> Soumettre une dépense
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Fournisseur / Catégorie</th>
                            <th>Montant (TTC)</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depenses as $depense)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($depense->date_depense)->format('d/m/Y') }}</td>
                            <td>{{ $depense->fournisseur ?? 'N/A' }}</td>
                            <td>{{ number_format($depense->montant_ttc ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge bg-secondary">{{ $depense->status ?? 'Soumis' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-sm btn-light" title="Voir"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <div class="mb-3"><i class="fas fa-receipt fa-3x text-light"></i></div>
                                Aucune dépense enregistrée.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $depenses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
