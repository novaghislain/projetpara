@extends('layouts.portal')

@section('title', 'Mes Factures')

@section('content')

<div class="portal-page-header portal-mb-6">
    <h1 class="portal-title"><i class="fas fa-file-invoice-dollar" style="color:var(--portal-accent); margin-right:8px;"></i> Mes Factures</h1>
    <p class="portal-subtitle">Consultez et téléchargez vos factures</p>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px;">
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 13px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 8px; text-transform: uppercase;">Total Factures</h3>
        <div style="font-size: 24px; font-weight: 700;">{{ $stats['total'] }}</div>
    </div>
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 13px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 8px; text-transform: uppercase;">Payées</h3>
        <div style="font-size: 24px; font-weight: 700; color: var(--portal-success);">{{ $stats['paid'] }}</div>
    </div>
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 13px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 8px; text-transform: uppercase;">Non Payées</h3>
        <div style="font-size: 24px; font-weight: 700; color: #F59E0B;">{{ $stats['unpaid'] }}</div>
    </div>
    <div class="portal-card" style="margin-bottom: 0;">
        <h3 style="font-size: 13px; font-weight: 600; color: var(--portal-text-secondary); margin-bottom: 8px; text-transform: uppercase;">Solde Restant Dû</h3>
        <div style="font-size: 24px; font-weight: 700; color: var(--portal-danger);">{{ number_format($stats['total_due'], 0, ',', ' ') }} F</div>
    </div>
</div>

<div class="portal-card" style="padding: 0; overflow: hidden;">
    @if($invoices->count() > 0)
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: var(--portal-bg); border-bottom: 1px solid var(--portal-border);">
            <tr>
                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">N° Facture</th>
                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Date</th>
                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Échéance</th>
                <th style="padding: 16px 24px; text-align: right; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Total TTC</th>
                <th style="padding: 16px 24px; text-align: right; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Solde dû</th>
                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Statut</th>
                <th style="padding: 16px 24px; text-align: right; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr style="border-bottom: 1px solid var(--portal-border);">
                <td style="padding: 16px 24px; font-weight: 600; color: var(--portal-accent);">{{ $invoice->invoice_number }}</td>
                <td style="padding: 16px 24px;">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                <td style="padding: 16px 24px; {{ $invoice->isOverdue() ? 'color: var(--portal-danger); font-weight: 600;' : '' }}">
                    {{ $invoice->due_date->format('d/m/Y') }}
                </td>
                <td style="padding: 16px 24px; text-align: right; font-weight: 600;">{{ number_format($invoice->total, 0, ',', ' ') }} F</td>
                <td style="padding: 16px 24px; text-align: right;">
                    @if($invoice->balance_due > 0)
                        <span style="color: var(--portal-danger); font-weight: 600;">{{ number_format($invoice->balance_due, 0, ',', ' ') }} F</span>
                    @else
                        <span style="color: var(--portal-success); font-weight: 600;">0 F</span>
                    @endif
                </td>
                <td style="padding: 16px 24px;">
                    @php
                        $color = match($invoice->status) {
                            'paid' => 'color: var(--portal-success); background: #ECFDF5;',
                            'partially_paid' => 'color: #D97706; background: #FEF3C7;',
                            'overdue' => 'color: var(--portal-danger); background: #FEF2F2;',
                            'sent' => 'color: #2563EB; background: #EFF6FF;',
                            'cancelled' => 'color: var(--portal-text-secondary); background: #F3F4F6;',
                            default => 'color: var(--portal-text-secondary); background: #F3F4F6;'
                        };
                    @endphp
                    <span style="{{ $color }} padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                        {{ \App\Models\Invoice::STATUS[$invoice->status] ?? $invoice->status }}
                    </span>
                </td>
                <td style="padding: 16px 24px; text-align: right;">
                    <a href="{{ route('portal.invoices.show', ['slug' => request()->route('slug'), 'id' => $invoice->id]) }}" class="portal-btn portal-btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($invoices->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--portal-border);">
        {{ $invoices->links() }}
    </div>
    @endif
    @else
    <div style="text-align: center; padding: 60px 24px;">
        <i class="fas fa-file-invoice-dollar" style="font-size: 48px; color: var(--portal-text-muted); margin-bottom: 16px; opacity: 0.5;"></i>
        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Aucune facture trouvée</h3>
        <p style="color: var(--portal-text-secondary);">Vos factures apparaîtront ici une fois qu'elles auront été émises par votre cabinet.</p>
    </div>
    @endif
</div>

@endsection
