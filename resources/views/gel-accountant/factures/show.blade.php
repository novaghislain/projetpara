@extends('layouts.gel-accountant')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('content')

{{-- â•â•â•â•â•â•â•â•â•â•â• EN-TÀŠTE â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">
            <i class="fas fa-file-invoice-dollar" style="color:var(--gel-primary);"></i>
            {{ $invoice->invoice_number }}
        </h1>
        <p class="gel-page-subtitle">
            Créée le {{ $invoice->created_at->format('d/m/Y À  H:i') }}
            — Statut :
            @php
                $statusColors = [
                    'draft' => 'background:rgba(107, 114, 128, 0.1); color:var(--gel-text-secondary);',
                    'sent' => 'background:rgba(59, 130, 246, 0.1); color:var(--gel-info);',
                    'paid' => 'background:rgba(16, 185, 129, 0.1); color:var(--gel-success);',
                    'overdue' => 'background:rgba(239, 68, 68, 0.1); color:var(--gel-danger);',
                    'cancelled' => 'background:rgba(107, 114, 128, 0.1); color:var(--gel-text-muted);',
                ];
                $color = $statusColors[$invoice->status] ?? 'background:rgba(107, 114, 128, 0.1); color:var(--gel-text-secondary);';
            @endphp
            <span class="gel-badge" style="{{ $color }}">{{ \App\Models\Invoice::STATUS[$invoice->status] ?? $invoice->status }}</span>
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.factures.index') }}" class="gel-btn gel-btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a href="{{ route('gel-accountant.factures.pdf', $invoice->id) }}" target="_blank" class="gel-btn gel-btn-primary">
            <i class="fas fa-download"></i> Télécharger PDF
        </a>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• CONTENU PRINCIPAL â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="invoice-show-grid">
    {{-- Colonne principale : Facture --}}
    <div class="invoice-show-left">
        <div class="invoice-paper">

            {{-- En-tête de la facture --}}
            <div class="invoice-paper-header">
                <div>
                    <div class="invoice-paper-company">{{ auth()->user()->name ?? 'Mon Cabinet' }}</div>
                    <div class="invoice-paper-detail">Cabinet Comptable</div>
                </div>
                <div style="text-align:right;">
                    <div class="invoice-paper-title">FACTURE</div>
                    <div class="invoice-paper-number">{{ $invoice->invoice_number }}</div>
                </div>
            </div>

            {{-- Infos Client / Dates --}}
            <div class="invoice-paper-meta">
                <div class="invoice-paper-to">
                    <div class="invoice-paper-label">FACTURÉ À</div>
                    <div class="invoice-paper-client-name">{{ $invoice->partner_name ?? '—' }}</div>
                    @if($invoice->partner_address)
                        <div class="invoice-paper-client-detail">{{ $invoice->partner_address }}</div>
                    @endif
                    @if($invoice->partner_tax_id)
                        <div class="invoice-paper-client-detail">IFU : {{ $invoice->partner_tax_id }}</div>
                    @endif
                </div>
                <div class="invoice-paper-dates">
                    <div><span class="invoice-paper-label">DATE : </span>{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                    <div><span class="invoice-paper-label">ÉCHÉANCE : </span>{{ $invoice->due_date->format('d/m/Y') }}</div>
                    @if($invoice->payment_term)
                        <div><span class="invoice-paper-label">PAIEMENT : </span>{{ $invoice->payment_term }}</div>
                    @endif
                </div>
            </div>

            {{-- Tableau des lignes --}}
            <table class="invoice-paper-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th style="text-align:center;">Qté</th>
                        <th style="text-align:right;">Prix unit.</th>
                        <th style="text-align:center;">TVA</th>
                        <th style="text-align:right;">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->lines as $line)
                    <tr>
                        <td style="color:var(--gel-text-muted);">{{ $line->line_number }}</td>
                        <td><strong>{{ $line->description }}</strong></td>
                        <td style="text-align:center;">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', ' '), '0'), ',') }}</td>
                        <td style="text-align:right;">{{ number_format($line->unit_price, 0, ',', ' ') }} F</td>
                        <td style="text-align:center;">{{ $line->vat_rate }}%</td>
                        <td style="text-align:right; font-weight:600;">{{ number_format($line->total, 0, ',', ' ') }} F</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--gel-text-muted); padding:24px;">Aucune ligne</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Totaux --}}
            <div class="invoice-paper-totals">
                <div class="invoice-paper-totals-row">
                    <span>Sous-total HT</span>
                    <span>{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</span>
                </div>
                @if($invoice->discount > 0)
                <div class="invoice-paper-totals-row">
                    <span>Remise</span>
                    <span style="color:var(--gel-danger);">-{{ number_format($invoice->discount, 0, ',', ' ') }} FCFA</span>
                </div>
                @endif
                <div class="invoice-paper-totals-row">
                    <span>TVA</span>
                    <span>{{ number_format($invoice->vat_total, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="invoice-paper-totals-divider"></div>
                <div class="invoice-paper-totals-row invoice-paper-totals-grand">
                    <span>Total TTC</span>
                    <span>{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</span>
                </div>
                @if($invoice->paid_amount > 0)
                <div class="invoice-paper-totals-row" style="color:var(--gel-success);">
                    <span>Montant payé</span>
                    <span>-{{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="invoice-paper-totals-row" style="font-weight:700; font-size:15px; color:var(--gel-danger);">
                    <span>Solde dû</span>
                    <span>{{ number_format($invoice->balance_due, 0, ',', ' ') }} FCFA</span>
                </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
            <div class="invoice-paper-notes">
                <div class="invoice-paper-label">NOTES</div>
                <p>{{ $invoice->notes }}</p>
            </div>
            @endif
            @if($invoice->terms_conditions)
            <div class="invoice-paper-notes" style="margin-top:12px;">
                <div class="invoice-paper-label">CONDITIONS GÉNÉRALES</div>
                <p style="font-size:11px;">{{ $invoice->terms_conditions }}</p>
            </div>
            @endif

            {{-- Bloc e-MECeF au bas de la facture --}}
            @if($invoice->emecef_statut === 'emise' && !empty($invoice->emecef_qr))
            <div style="margin-top:40px; padding:16px; border:1px solid #e5e7eb; border-radius:8px; display:flex; gap:16px; align-items:center;">
                <div style="width:100px; height:100px;">
                    <img src="{{ $qrCodeBase64 }}" alt="QR Code" style="width:100%; height:100%;" />
                </div>
                <div>
                    <div style="font-weight:700; color:var(--gel-text);">FACTURE NORMALISÉE (e-MECeF)</div>
                    <div style="font-size:12px; color:var(--gel-text-secondary); margin-top:4px;">
                        <strong>NIM :</strong> {{ $invoice->emecef_nim }}<br>
                        <strong>Compteur :</strong> {{ $invoice->emecef_compteur }}<br>
                        <strong>Date e-MECeF :</strong> {{ $invoice->emecef_datetime ? $invoice->emecef_datetime->format('d/m/Y H:i:s') : '' }}
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Colonne droite : Résumé --}}
    <div class="invoice-show-right">
        <div class="gel-card invoice-summary-sidebar p-4 mb-4">
            <div class="invoice-section-title"><i class="fas fa-info-circle"></i> Résumé</div>

            <div class="invoice-sidebar-item">
                <span class="invoice-sidebar-label">Statut</span>
                <span class="gel-badge" style="{{ $color }}">{{ \App\Models\Invoice::STATUS[$invoice->status] ?? $invoice->status }}</span>
            </div>
            <div class="invoice-sidebar-item">
                <span class="invoice-sidebar-label">Total TTC</span>
                <span class="invoice-sidebar-value">{{ number_format($invoice->total, 0, ',', ' ') }} F</span>
            </div>
            <div class="invoice-sidebar-item">
                <span class="invoice-sidebar-label">Solde dû</span>
                <span class="invoice-sidebar-value" style="color:{{ $invoice->balance_due > 0 ? 'var(--gel-danger)' : 'var(--gel-success)' }};">
                    {{ number_format($invoice->balance_due, 0, ',', ' ') }} F
                </span>
            </div>
            <div class="invoice-sidebar-item">
                <span class="invoice-sidebar-label">Client</span>
                <span class="invoice-sidebar-value">{{ $invoice->partner_name ?? '—' }}</span>
            </div>
            <div class="invoice-sidebar-item">
                <span class="invoice-sidebar-label">Date</span>
                <span class="invoice-sidebar-value">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
            </div>
            <div class="invoice-sidebar-item">
                <span class="invoice-sidebar-label">Échéance</span>
                <span class="invoice-sidebar-value" style="{{ $invoice->isOverdue() ? 'color:var(--gel-danger); font-weight:700;' : '' }}">
                    {{ $invoice->due_date->format('d/m/Y') }}
                    @if($invoice->isOverdue()) âš ï¸ @endif
                </span>
            </div>
        </div>

        {{-- Section e-MECeF --}}
        <div class="gel-card invoice-summary-sidebar p-4 mb-4" style="border-top: 4px solid var(--gel-success);">
            <div class="invoice-section-title"><i class="fas fa-qrcode"></i> Certification e-MECeF</div>
            
            @if($invoice->emecef_statut === 'emise')
                <div style="text-align:center; padding:12px 0;">
                    <div style="color:var(--gel-success); font-size:40px; margin-bottom:8px;"><i class="fas fa-check-circle"></i></div>
                    <div style="font-weight:600; color:var(--gel-text);">Facture Certifiée</div>
                    <div style="font-size:12px; color:var(--gel-text-secondary); margin-top:4px;">NIM : {{ $invoice->emecef_nim }}</div>
                </div>
            @else
                <p style="font-size:13px; color:var(--gel-text-secondary); margin-bottom:12px;">Cette facture n'a pas encore été certifiée auprès de la DGI.</p>
                <form action="{{ route('gel-accountant.factures.certify', $invoice->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="gel-btn gel-btn-success" style="width:100%; justify-content:center;">
                        <i class="fas fa-certificate"></i> Certifier e-MECeF
                    </button>
                </form>
            @endif
        </div>

        @if($invoice->status === 'draft')
        <div style="margin-top:12px;">
            <form action="{{ route('gel-accountant.factures.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cette facture brouillon ?');">
                @csrf @method('DELETE')
                <button class="gel-btn gel-btn-danger" style="width:100%; justify-content:center;">
                    <i class="fas fa-trash"></i> Supprimer ce brouillon
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .invoice-show-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 20px;
        align-items: start;
    }

    /* Paper style */
    .invoice-paper {
        background: white;
        border: 1px solid var(--gel-border);
        border-radius: 8px;
        padding: 36px 40px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .invoice-paper-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--gel-primary);
    }
    .invoice-paper-company {
        font-size: 20px;
        font-weight: 700;
        color: var(--gel-primary);
    }
    .invoice-paper-detail {
        font-size: 13px;
        color: var(--gel-text-secondary);
    }
    .invoice-paper-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--gel-primary);
        letter-spacing: 2px;
    }
    .invoice-paper-number {
        font-size: 14px;
        font-weight: 600;
        color: var(--gel-text-secondary);
    }

    .invoice-paper-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .invoice-paper-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gel-text-muted);
        margin-bottom: 4px;
    }
    .invoice-paper-client-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--gel-text-primary);
    }
    .invoice-paper-client-detail {
        font-size: 13px;
        color: var(--gel-text-secondary);
    }
    .invoice-paper-dates {
        text-align: right;
        font-size: 13px;
        color: var(--gel-text-primary);
        line-height: 1.8;
    }

    /* Table */
    .invoice-paper-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .invoice-paper-table thead th {
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: white;
        background: var(--gel-primary);
        border: none;
    }
    .invoice-paper-table thead th:first-child { border-radius: 6px 0 0 0; }
    .invoice-paper-table thead th:last-child { border-radius: 0 6px 0 0; }
    .invoice-paper-table tbody td {
        padding: 12px;
        font-size: 13px;
        border-bottom: 1px solid var(--gel-border);
    }
    .invoice-paper-table tbody tr:last-child td { border-bottom: none; }

    /* Totals */
    .invoice-paper-totals {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        margin-bottom: 24px;
    }
    .invoice-paper-totals-row {
        display: flex;
        justify-content: space-between;
        width: 280px;
        padding: 6px 0;
        font-size: 13px;
        color: var(--gel-text-secondary);
    }
    .invoice-paper-totals-divider {
        width: 280px;
        height: 1px;
        background: var(--gel-border);
        margin: 4px 0;
    }
    .invoice-paper-totals-grand {
        font-size: 18px;
        font-weight: 700;
        color: var(--gel-text-primary);
        padding-top: 8px;
    }

    .invoice-paper-notes {
        background: var(--gel-sidebar-bg);
        border-radius: 6px;
        padding: 12px 16px;
    }
    .invoice-paper-notes p {
        font-size: 13px;
        color: var(--gel-text-secondary);
        margin: 0;
        line-height: 1.5;
    }

    /* Sidebar */
    .invoice-summary-sidebar {
        background: var(--gel-sidebar-bg);
    }
    .invoice-section-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--gel-text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .invoice-section-title i { color: var(--gel-primary); }
    .invoice-sidebar-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--gel-border);
        font-size: 13px;
    }
    .invoice-sidebar-item:last-child { border-bottom: none; }
    .invoice-sidebar-label { color: var(--gel-text-secondary); }
    .invoice-sidebar-value { font-weight: 600; color: var(--gel-text-primary); }

    @media (max-width: 900px) {
        .invoice-show-grid { grid-template-columns: 1fr; }
    }

    @media print {
        .gel-topbar, .gel-sidebar, .gel-page-header, .invoice-show-right { display: none !important; }
        .gel-content { margin: 0 !important; padding: 0 !important; }
        .invoice-show-grid { grid-template-columns: 1fr !important; }
        .invoice-paper { box-shadow: none; border: none; }
    }
</style>
@endpush

