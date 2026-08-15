@extends('layouts.gel-accountant')

@section('title', 'Paiements Reçus')

@push('styles')
<style>
/* ==========================================================================
   PAYMENTS - DESIGN
   ========================================================================== */
.table-container {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); overflow: hidden;
}
.filters-bar { padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; }
.filters-left { display: flex; gap: 12px; align-items: center; }
.form-control-sm, .form-select-sm { border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 12px; font-size: 13px; outline: none; }
.form-control-sm:focus, .form-select-sm:focus { border-color: var(--gel-primary); }

.gel-table { width: 100%; border-collapse: collapse; }
.gel-table th { background: white; padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; }
.gel-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: #1E293B; vertical-align: middle; }
.gel-table tr:hover { background: #F8FAFC; }

.btn-action { background: white; border: 1px solid #E2E8F0; color: #64748B; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 12px; transition: all 0.2s; }
.btn-action:hover { background: #F1F5F9; color: var(--gel-primary); border-color: #CBD5E1; }

.btn-primary-action { background: var(--gel-primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); }

.payment-method { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #475569; background: #F1F5F9; padding: 4px 8px; border-radius: 4px; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Paiements Reçus</h1>
        <div class="gel-page-subtitle">Historique des encaissements clients.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('gel-accountant.payments.create') }}" class="btn-primary-action">
            <i class="fas fa-hand-holding-usd"></i> Recevoir un Paiement
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="table-container">
    <div class="filters-bar">
        <div class="filters-left">
            <input type="text" class="form-control-sm" placeholder="N° Paiement, Client..." style="width:250px;">
            <select class="form-select-sm">
                <option value="">Tous les modes</option>
                <option value="virement">Virement</option>
                <option value="especes">Espèces</option>
                <option value="cheque">Chèque</option>
                <option value="mobile_money">Mobile Money</option>
            </select>
        </div>
    </div>

    <table class="gel-table">
        <thead>
            <tr>
                <th>N° Paiement</th>
                <th>Date</th>
                <th>Client</th>
                <th>Facture(s)</th>
                <th>Mode</th>
                <th class="text-end">Montant</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td style="font-weight:600; color:var(--gel-primary);">{{ $payment->payment_number }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                <td>{{ $payment->partner->company_name ?? ($payment->partner->first_name . ' ' . $payment->partner->last_name) }}</td>
                <td>
                    @if($payment->invoice)
                        <a href="{{ route('gel-accountant.factures.show', $payment->invoice->id) }}" style="color:var(--gel-primary); font-weight:600; text-decoration:none;">{{ $payment->invoice->invoice_number }}</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="payment-method">
                        @if($payment->payment_method == 'virement') <i class="fas fa-university"></i>
                        @elseif($payment->payment_method == 'especes') <i class="fas fa-money-bill"></i>
                        @elseif($payment->payment_method == 'cheque') <i class="fas fa-money-check"></i>
                        @elseif($payment->payment_method == 'mobile_money') <i class="fas fa-mobile-alt"></i>
                        @else <i class="fas fa-credit-card"></i>
                        @endif
                        {{ ucfirst($payment->payment_method) }}
                    </span>
                </td>
                <td class="text-end font-monospace" style="font-weight:700; color:#10B981;">+ {{ number_format($payment->amount, 0, ',', ' ') }} F</td>
                <td class="text-end">
                    <a href="#" class="btn-action" title="Reçu PDF"><i class="fas fa-file-pdf"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="text-center" style="padding: 40px 20px; color: #94A3B8;">
                        <i class="fas fa-hand-holding-usd" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i>
                        <div>Aucun paiement reçu.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($payments->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0; display:flex; justify-content:flex-end;">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
