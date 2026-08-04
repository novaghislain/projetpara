@extends('layouts.gel-accountant')

@section('title', 'Bon de Commande ' . $po->invoice_number)

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-contract" style="color:var(--gel-primary); margin-right:8px;"></i> Bon de Commande {{ $po->invoice_number }}</h1>
        <p class="gel-page-subtitle">Détails du bon de commande fournisseur</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.purchase-orders.index') }}" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
        
        @if($po->status == 'draft')
        <form action="{{ route('gel-accountant.purchase-orders.send', $po->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="gel-btn gel-btn-primary">
                <i class="fas fa-paper-plane"></i> Marquer comme Envoyé
            </button>
        </form>
        @endif
        
        <a href="?export=pdf" target="_blank" class="gel-btn gel-btn-secondary">
            <i class="fas fa-download"></i> Télécharger PDF
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="gel-card p-4 mb-4 printable-area">
            
            <div class="row mb-5">
                <div class="col-6">
                    <h4 class="mb-1" style="font-weight:700; color:var(--gel-primary);">VOTRE CABINET</h4>
                    <div class="text-muted">
                        Cabinet d'Expertise Comptable<br>
                        IFU : 1234567890123<br>
                        Adresse : Avenue de la Paix
                    </div>
                </div>
                <div class="col-6 text-end">
                    <h2 class="mb-2" style="font-weight:700; color:#333;">BON DE COMMANDE</h2>
                    <div><strong>N° :</strong> {{ $po->invoice_number }}</div>
                    <div><strong>Date :</strong> {{ \Carbon\Carbon::parse($po->invoice_date)->format('d/m/Y') }}</div>
                    @if($po->delivery_date)
                    <div><strong>Livraison prévue :</strong> {{ \Carbon\Carbon::parse($po->delivery_date)->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-12">
                    <div style="padding:15px; border:1px solid #eee; border-radius:8px; background:#fafafa;">
                        <h6 style="text-transform:uppercase; font-size:12px; font-weight:700; color:#888; margin-bottom:8px;">Fournisseur</h6>
                        <h5 style="font-weight:700; margin-bottom:4px;">
                            @if($po->partner)
                                {{ $po->partner->company_name ?: $po->partner->first_name.' '.$po->partner->last_name }}
                            @else
                                {{ $po->partner_name ?? '-' }}
                            @endif
                        </h5>
                        @if($po->partner)
                            <div>{{ $po->partner->address }}</div>
                            @if($po->partner->tax_id)
                            <div class="mt-1"><strong>IFU:</strong> {{ $po->partner->tax_id }}</div>
                            @endif
                            @if($po->partner->email)
                            <div><strong>Email:</strong> {{ $po->partner->email }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-sm mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Désignation</th>
                        <th style="text-align:right;">Qté</th>
                        <th style="text-align:right;">Prix Unitaire</th>
                        <th style="text-align:right;">TVA</th>
                        <th style="text-align:right;">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->lines as $line)
                    <tr>
                        <td>{{ $line->description }}</td>
                        <td style="text-align:right;">{{ $line->quantity }}</td>
                        <td style="text-align:right;">{{ number_format($line->unit_price, 0, ',', ' ') }}</td>
                        <td style="text-align:right;">{{ $line->vat_rate }}%</td>
                        <td style="text-align:right; font-weight:bold;">{{ number_format($line->subtotal, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right;">Sous-total HT</th>
                        <th style="text-align:right;">{{ number_format($po->subtotal, 0, ',', ' ') }} FCFA</th>
                    </tr>
                    <tr>
                        <th colspan="4" style="text-align:right;">TVA</th>
                        <th style="text-align:right;">{{ number_format($po->tax_amount, 0, ',', ' ') }} FCFA</th>
                    </tr>
                    <tr>
                        <th colspan="4" style="text-align:right; font-size:16px;">Total TTC</th>
                        <th style="text-align:right; font-size:16px; color:var(--gel-primary);">{{ number_format($po->total, 0, ',', ' ') }} FCFA</th>
                    </tr>
                </tfoot>
            </table>
            
            @if($po->notes)
            <div class="mt-4">
                <h6 style="font-weight:700;">Notes & Instructions :</h6>
                <p class="text-muted">{{ $po->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;"><i class="fas fa-info-circle"></i> Statut & Suivi</h3>
            
            <div class="mb-3">
                <strong>Statut actuel :</strong><br>
                @if($po->status == 'draft')
                    <span class="badge bg-secondary mt-1" style="font-size:14px;">Brouillon</span>
                @elseif($po->status == 'sent')
                    <span class="badge bg-primary mt-1" style="font-size:14px;">Envoyé au fournisseur</span>
                @elseif($po->status == 'received')
                    <span class="badge bg-success mt-1" style="font-size:14px;">Marchandise reçue</span>
                @else
                    <span class="badge bg-warning mt-1" style="font-size:14px;">{{ $po->status }}</span>
                @endif
            </div>
            
            @if($po->terms_conditions)
            <div class="mb-3">
                <strong>Référence Devis / Vendeur :</strong><br>
                {{ $po->terms_conditions }}
            </div>
            @endif
            
            <hr>
            
            @if($po->status == 'sent')
            <p class="text-muted" style="font-size:13px;">Lorsque la marchandise ou le service sera reçu, vous pourrez transformer ce bon de commande en Facture Fournisseur (Dépense).</p>
            <div class="d-grid mt-3">
                <button class="btn btn-outline-success"><i class="fas fa-box-open"></i> Marquer comme reçu</button>
                <a href="{{ route('gel-accountant.expenses.create') }}?po={{ $po->id }}" class="btn btn-outline-primary mt-2"><i class="fas fa-file-invoice"></i> Convertir en Dépense</a>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .printable-area, .printable-area * { visibility: visible; }
    .printable-area { position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
    .gel-page-header, .btn, .gel-btn, form { display: none !important; }
}
</style>

@endsection
