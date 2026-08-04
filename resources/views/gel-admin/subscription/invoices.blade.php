@extends('layouts.gel-admin')

@section('title', 'Factures d\'abonnement')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Factures d'abonnement</h1>
        <p class="admin-page-sub">Historique de vos paiements à GEL SABINET.</p>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 px-4 py-3">Numéro</th>
                    <th class="border-0 px-4 py-3">Date</th>
                    <th class="border-0 px-4 py-3">Plan</th>
                    <th class="border-0 px-4 py-3">Montant</th>
                    <th class="border-0 px-4 py-3">Statut</th>
                    <th class="border-0 px-4 py-3 text-end">Télécharger</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="px-4 fw-medium">{{ $inv->invoice_number }}</td>
                        <td class="px-4 text-muted">{{ $inv->paid_at ? \Carbon\Carbon::parse($inv->paid_at)->format('d/m/Y') : $inv->created_at->format('d/m/Y') }}</td>
                        <td class="px-4"><span class="badge bg-secondary">{{ ucfirst($inv->plan_name) }}</span></td>
                        <td class="px-4 fw-bold">{{ number_format($inv->amount, 0, ',', ' ') }} FCFA</td>
                        <td class="px-4">
                            @if($inv->status == 'paid')
                                <span class="badge bg-success">Payée</span>
                            @else
                                <span class="badge bg-warning text-dark">En attente</span>
                            @endif
                        </td>
                        <td class="px-4 text-end">
                            <button class="btn btn-sm btn-light" onclick="alert('Téléchargement PDF en cours de développement')"><i class="fas fa-download"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Aucune facture disponible.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
