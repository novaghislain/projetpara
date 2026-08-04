@extends('layouts.portal')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
    <div>
        <h1 class="portal-title"><i class="fas fa-file-invoice-dollar" style="color:var(--portal-accent); margin-right:8px;"></i> Facture {{ $invoice->invoice_number }}</h1>
        <p class="portal-subtitle">Créée le {{ $invoice->created_at->format('d/m/Y') }}</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('portal.invoices', request()->route('slug')) }}" class="portal-btn portal-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a href="?export=pdf" target="_blank" class="portal-btn portal-btn-primary">
            <i class="fas fa-download"></i> Télécharger PDF
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
    <div class="portal-card" style="padding: 40px;">
        
        {{-- En-tête de la facture --}}
        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--portal-border); padding-bottom: 24px; margin-bottom: 32px;">
            <div>
                <h4 style="font-size: 18px; font-weight: 700; color: var(--portal-accent); margin-bottom: 4px;">{{ $invoice->cabinet->nom ?? 'Cabinet Comptable' }}</h4>
                <p style="font-size: 13px; color: var(--portal-text-secondary);">Émetteur</p>
            </div>
            <div style="text-align: right;">
                <h2 style="font-size: 24px; font-weight: 800; color: var(--portal-accent); margin-bottom: 4px; letter-spacing: 1px;">FACTURE</h2>
                <p style="font-size: 14px; color: var(--portal-text-secondary); font-weight: 600;">{{ $invoice->invoice_number }}</p>
            </div>
        </div>

        {{-- Informations --}}
        <div style="display: flex; justify-content: space-between; margin-bottom: 40px;">
            <div>
                <h6 style="font-size: 11px; font-weight: 700; color: var(--portal-text-muted); text-transform: uppercase; margin-bottom: 8px;">Facturé à</h6>
                <h5 style="font-size: 16px; font-weight: 700; margin-bottom: 4px;">{{ $invoice->partner_name }}</h5>
                @if($invoice->partner_address)
                    <p style="font-size: 14px; color: var(--portal-text-secondary); margin-bottom: 0;">{{ $invoice->partner_address }}</p>
                @endif
                @if($invoice->partner_tax_id)
                    <p style="font-size: 14px; color: var(--portal-text-secondary); margin-bottom: 0;">IFU : {{ $invoice->partner_tax_id }}</p>
                @endif
            </div>
            <div style="text-align: right;">
                <p style="font-size: 14px; margin-bottom: 8px;"><span style="color: var(--portal-text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; margin-right: 8px;">Date :</span> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                <p style="font-size: 14px; margin-bottom: 8px;"><span style="color: var(--portal-text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; margin-right: 8px;">Échéance :</span> {{ $invoice->due_date->format('d/m/Y') }}</p>
                @if($invoice->payment_term)
                    <p style="font-size: 14px; margin-bottom: 0;"><span style="color: var(--portal-text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; margin-right: 8px;">Paiement :</span> {{ $invoice->payment_term }}</p>
                @endif
            </div>
        </div>

        {{-- Lignes --}}
        <div style="margin-bottom: 40px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--portal-bg);">
                    <tr>
                        <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Description</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Qté</th>
                        <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Prix unitaire</th>
                        <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">TVA</th>
                        <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 600; color: var(--portal-text-secondary); text-transform: uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->lines as $line)
                    <tr style="border-bottom: 1px solid var(--portal-border);">
                        <td style="padding: 16px;"><strong>{{ $line->description }}</strong></td>
                        <td style="padding: 16px; text-align: center;">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', ' '), '0'), ',') }}</td>
                        <td style="padding: 16px; text-align: right;">{{ number_format($line->unit_price, 0, ',', ' ') }} F</td>
                        <td style="padding: 16px; text-align: center;">{{ $line->vat_rate }}%</td>
                        <td style="padding: 16px; text-align: right; font-weight: 700;">{{ number_format($line->total, 0, ',', ' ') }} F</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 24px; text-align: center; color: var(--portal-text-muted);">Aucune ligne</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totaux --}}
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 320px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px;">
                    <span style="color: var(--portal-text-secondary);">Sous-total HT</span>
                    <span>{{ number_format($invoice->subtotal, 0, ',', ' ') }} F</span>
                </div>
                @if($invoice->discount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: var(--portal-danger);">
                    <span>Remise</span>
                    <span>-{{ number_format($invoice->discount, 0, ',', ' ') }} F</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px;">
                    <span style="color: var(--portal-text-secondary);">TVA</span>
                    <span>{{ number_format($invoice->vat_total, 0, ',', ' ') }} F</span>
                </div>
                <div style="border-top: 1px solid var(--portal-border); margin: 16px 0;"></div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 18px; font-weight: 700;">
                    <span>Total TTC</span>
                    <span>{{ number_format($invoice->total, 0, ',', ' ') }} F</span>
                </div>
                @if($invoice->paid_amount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: var(--portal-success);">
                    <span>Montant payé</span>
                    <span>-{{ number_format($invoice->paid_amount, 0, ',', ' ') }} F</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 16px; font-weight: 700; color: var(--portal-danger);">
                    <span>Solde dû</span>
                    <span>{{ number_format($invoice->balance_due, 0, ',', ' ') }} F</span>
                </div>
                @endif
            </div>
        </div>

        {{-- e-MECeF --}}
        @if($invoice->emecef_statut === 'emise' && !empty($invoice->emecef_qr))
        <div style="display: flex; gap: 20px; align-items: center; padding: 20px; margin-top: 40px; border: 1px solid var(--portal-border); border-radius: 8px; background: var(--portal-bg);">
            <div style="width: 90px; height: 90px; flex-shrink: 0;">
                <img src="{{ $qrCodeBase64 }}" alt="QR Code e-MECeF" style="width: 100%; height: 100%;">
            </div>
            <div>
                <h6 style="font-size: 14px; font-weight: 700; margin-bottom: 8px;">FACTURE NORMALISÉE (e-MECeF)</h6>
                <div style="font-size: 12px; color: var(--portal-text-secondary); line-height: 1.6;">
                    <strong>NIM :</strong> {{ $invoice->emecef_nim }}<br>
                    <strong>Compteur :</strong> {{ $invoice->emecef_compteur }}<br>
                    <strong>Date de certification :</strong> {{ $invoice->emecef_datetime ? $invoice->emecef_datetime->format('d/m/Y H:i:s') : '' }}
                </div>
            </div>
        </div>
        @endif

    </div>

    <div>
        <div class="portal-card">
            <h5 style="font-size: 16px; font-weight: 700; margin-bottom: 24px;"><i class="fas fa-info-circle" style="color: var(--portal-accent); margin-right: 8px;"></i> Résumé</h5>
            
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--portal-border); padding-bottom: 12px; margin-bottom: 12px; font-size: 14px;">
                <span style="color: var(--portal-text-secondary);">Statut</span>
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
                <span style="{{ $color }} padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                    {{ \App\Models\Invoice::STATUS[$invoice->status] ?? $invoice->status }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--portal-border); padding-bottom: 12px; margin-bottom: 12px; font-size: 14px;">
                <span style="color: var(--portal-text-secondary);">Total</span>
                <span style="font-weight: 700;">{{ number_format($invoice->total, 0, ',', ' ') }} F</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--portal-border); padding-bottom: 12px; margin-bottom: 12px; font-size: 14px;">
                <span style="color: var(--portal-text-secondary);">Solde dû</span>
                <span style="font-weight: 700; color: {{ $invoice->balance_due > 0 ? 'var(--portal-danger)' : 'var(--portal-success)' }};">
                    {{ number_format($invoice->balance_due, 0, ',', ' ') }} F
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--portal-border); padding-bottom: 12px; margin-bottom: 12px; font-size: 14px;">
                <span style="color: var(--portal-text-secondary);">Date</span>
                <span style="font-weight: 600;">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 14px;">
                <span style="color: var(--portal-text-secondary);">Échéance</span>
                <span style="font-weight: 600; {{ $invoice->isOverdue() ? 'color: var(--portal-danger);' : '' }}">
                    {{ $invoice->due_date->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>
</div>

@endsection
