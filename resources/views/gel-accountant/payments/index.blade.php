@extends('layouts.gel-accountant')

@section('title', 'Paiements Reçus')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-hand-holding-usd" style="color:var(--gel-primary); margin-right:8px;"></i> Paiements Reçus</h1>
        <p class="gel-page-subtitle">Suivi des encaissements sur vos factures clients.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.payments.create') }}" class="gel-btn gel-btn-primary"><i class="fas fa-plus"></i> Nouveau paiement</a>
    </div>
</div>

<div class="gel-card gel-p-0">
    <div class="table-responsive">
        <table class="table gel-table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Méthode</th>
                    <th>Montant</th>
                    <th>Facture associée</th>
                    <th>Référence</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                    <td>
                        <span style="font-weight: 500; color: var(--gel-text-primary);">
                            {{ $payment->partner->company_name ?? ($payment->partner->first_name . ' ' . $payment->partner->last_name) }}
                        </span>
                    </td>
                    <td>
                        <span class="gel-badge gel-badge-secondary">
                            {{ ucfirst($payment->payment_method) }}
                        </span>
                    </td>
                    <td style="font-weight: 600; color: var(--gel-success);">
                        {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                    </td>
                    <td>
                        @if($payment->invoice)
                            <a href="{{ route('gel-accountant.factures.show', $payment->invoice->id) }}" style="color: var(--gel-primary); text-decoration: none;">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $payment->reference ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div style="padding:60px 20px; text-align:center; color:var(--gel-text-muted);">
                            <i class="fas fa-check-circle" style="font-size:48px; margin-bottom:16px; opacity:0.5; color:var(--gel-success);"></i>
                            <h3 style="font-size:18px; font-weight:600; color:var(--gel-text-primary);">Aucun paiement enregistré</h3>
                            <p style="margin-bottom:20px;">Enregistrez votre premier paiement client.</p>
                            <a href="{{ route('gel-accountant.payments.create') }}" class="gel-btn gel-btn-primary">
                                <i class="fas fa-plus"></i> Nouveau paiement
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($payments->hasPages())
    <div class="gel-p-4" style="border-top: 1px solid var(--gel-border);">
        {{ $payments->links() }}
    </div>
    @endif
</div>

@endsection
