@extends('layouts.gel-accountant')

@section('title', 'Dépenses & Factures Fournisseurs')

@section('content')

{{-- ═══════════ EN-TÊTE ═══════════ --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-shopping-cart" style="color:var(--gel-primary); margin-right:8px;"></i> Dépenses & Achats</h1>
        <p class="gel-page-subtitle">Gérez vos factures fournisseurs, reçus et notes de frais</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.expenses.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Saisir une dépense
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mt-3 mb-3">{{ session('success') }}</div>
@endif

{{-- ═══════════ TABLEAU DES DÉPENSES ═══════════ --}}
<div class="gel-card gel-p-0 p-4 mb-4" style="overflow:hidden;">
    @if($expenses->count() > 0)
    <table class="gel-table">
        <thead>
            <tr>
                <th>N° Réf</th>
                <th>Fournisseur / Bénéficiaire</th>
                <th>Date</th>
                <th>Statut</th>
                <th style="text-align:right;">Montant TTC</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $e)
            <tr>
                <td style="font-weight:600; color:var(--gel-primary);">{{ $e->invoice_number }}</td>
                <td>
                    @if($e->partner)
                        {{ $e->partner->company_name ?: $e->partner->first_name.' '.$e->partner->last_name }}
                    @else
                        {{ $e->partner_name ?? '-' }}
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($e->invoice_date)->format('d/m/Y') }}</td>
                <td>
                    @if($e->status == 'paid')
                        <span class="badge bg-success">Payée</span>
                    @elseif($e->status == 'unpaid' || $e->status == 'partial')
                        <span class="badge bg-warning text-dark">À payer</span>
                    @elseif($e->status == 'disputed')
                        <span class="badge bg-danger">En litige</span>
                    @else
                        <span class="badge bg-secondary">{{ $e->status }}</span>
                    @endif
                </td>
                <td style="text-align:right; font-weight:600;">{{ number_format($e->total, 0, ',', ' ') }} FCFA</td>
                <td style="text-align:center;">
                    <a href="{{ route('gel-accountant.expenses.show', $e->id) }}" class="gel-btn gel-btn-sm gel-btn-secondary" title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if($e->status == 'unpaid' || $e->status == 'partial')
                    <a href="{{ route('gel-accountant.pay-bills') }}" class="gel-btn gel-btn-sm gel-btn-primary" title="Payer" style="margin-left:4px;">
                        <i class="fas fa-credit-card"></i> Payer
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="padding:16px;">
        {{ $expenses->links() }}
    </div>
    
    @else
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-box-open" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucune dépense enregistrée</h3>
        <p style="margin-bottom:20px;">Commencez à suivre vos achats en ajoutant votre première facture fournisseur.</p>
        <a href="{{ route('gel-accountant.expenses.create') }}" class="gel-btn gel-btn-primary">
            Saisir une dépense
        </a>
    </div>
    @endif
</div>

@endsection
