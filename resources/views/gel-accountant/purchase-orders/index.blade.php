@extends('layouts.gel-accountant')

@section('title', 'Bons de Commande')

@section('content')

{{-- ═══════════ EN-TÊTE ═══════════ --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-contract" style="color:var(--gel-primary); margin-right:8px;"></i> Bons de Commande</h1>
        <p class="gel-page-subtitle">Gérez vos commandes fournisseurs et suivez les livraisons</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.purchase-orders.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Créer un bon de commande
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mt-3 mb-3">{{ session('success') }}</div>
@endif

{{-- ═══════════ TABLEAU ═══════════ --}}
<div class="gel-card gel-p-0 p-4 mb-4" style="overflow:hidden;">
    @if($purchaseOrders->count() > 0)
    <table class="gel-table">
        <thead>
            <tr>
                <th>N° BDC</th>
                <th>Fournisseur</th>
                <th>Date Commande</th>
                <th>Statut</th>
                <th style="text-align:right;">Montant TTC</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrders as $po)
            <tr>
                <td style="font-weight:600; color:var(--gel-primary);">{{ $po->invoice_number }}</td>
                <td>
                    @if($po->partner)
                        {{ $po->partner->company_name ?: $po->partner->first_name.' '.$po->partner->last_name }}
                    @else
                        {{ $po->partner_name ?? '-' }}
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($po->invoice_date)->format('d/m/Y') }}</td>
                <td>
                    @if($po->status == 'draft')
                        <span class="badge bg-secondary">Brouillon</span>
                    @elseif($po->status == 'sent')
                        <span class="badge bg-primary">Envoyé</span>
                    @else
                        <span class="badge bg-warning">{{ $po->status }}</span>
                    @endif
                </td>
                <td style="text-align:right; font-weight:600;">{{ number_format($po->total, 0, ',', ' ') }} FCFA</td>
                <td style="text-align:center;">
                    <a href="{{ route('gel-accountant.purchase-orders.show', $po->id) }}" class="gel-btn gel-btn-sm gel-btn-secondary" title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="padding:16px;">
        {{ $purchaseOrders->links() }}
    </div>
    
    @else
    <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
        <i class="fas fa-file-contract" style="font-size:48px; margin-bottom:16px; opacity:0.5;"></i>
        <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucun bon de commande</h3>
        <p style="margin-bottom:20px;">Commencez à gérer vos commandes fournisseurs.</p>
        <a href="{{ route('gel-accountant.purchase-orders.create') }}" class="gel-btn gel-btn-primary">
            Créer un bon de commande
        </a>
    </div>
    @endif
</div>

@endsection
