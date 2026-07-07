@php $currentSection = 'ventes'; @endphp
@extends('layouts.gel-business')
@section('title', 'Factures - GEL Business')
@section('content')
<div class="gel-page-header">
    <div><h1 class="gel-page-title">Factures</h1><p class="gel-page-subtitle">Toutes vos factures clients</p></div>
    <div style="display:flex;gap:8px;">
        <button class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
        <button class="gel-btn gel-btn-primary gel-btn-sm"><i class="fas fa-plus"></i> Nouvelle facture</button>
    </div>
</div>
<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @php $factures = $factures ?? []; @endphp
        @if(count($factures) > 0)
        <table class="gel-table">
            <tr><th>N°</th><th>Client</th><th>Date</th><th>Montant</th><th>Statut</th><th></th></tr>
            @foreach($factures as $f)
            <tr>
                <td>{{ $f->numero ?? '—' }}</td>
                <td>{{ $f->client_nom ?? '—' }}</td>
                <td>{{ $f->date_facture ?? '—' }}</td>
                <td class="gel-text-right">{{ number_format($f->montant ?? 0, 0, ',', ' ') }} CFA</td>
                <td><span class="gel-badge gel-badge-info">{{ $f->statut ?? 'Brouillon' }}</span></td>
                <td><i class="fas fa-ellipsis-v" style="color:var(--gel-text-muted);cursor:pointer;"></i></td>
            </tr>
            @endforeach
        </table>
        @else
        <div class="gel-empty">
            <i class="fas fa-file-invoice"></i>
            <h3>Aucune facture</h3>
            <p>Les factures apparaîtront ici.</p>
        </div>
        @endif
    </div>
</div>
@endsection
