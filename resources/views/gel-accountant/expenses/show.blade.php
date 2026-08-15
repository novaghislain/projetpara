@extends('layouts.gel-accountant')

@section('title', 'Détail de la Dépense ' . $expense->invoice_number)

@push('styles')
<style>
/* ==========================================================================
   EXPENSE SHOW - DESIGN
   ========================================================================== */
.expense-wrapper { display: flex; gap: 24px; margin-bottom: 24px; }
.expense-main {
    flex: 1; background: white; padding: 32px; border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02); border: 1px solid var(--gel-border);
}
.expense-sidebar { width: 320px; display: flex; flex-direction: column; gap: 20px; }
.sidebar-box {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.sidebar-title {
    font-size: 14px; font-weight: 700; color: #1E293B; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;
}

.expense-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px dashed #E2E8F0; }
.vendor-info h2 { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #1E293B; }
.vendor-info p { margin: 0; font-size: 14px; color: #64748B; }
.expense-meta { text-align: right; }
.expense-meta h3 { margin: 0; font-size: 16px; color: #64748B; }
.expense-meta .num { font-size: 20px; font-weight: 700; color: var(--gel-primary); margin-top: 4px; }

.lines-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
.lines-table th { background: #F8FAFC; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 2px solid #E2E8F0; border-top: 1px solid #E2E8F0; }
.lines-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; font-size: 13px; color: #334155; }
.lines-table th.right, .lines-table td.right { text-align: right; }
.lines-table th.center, .lines-table td.center { text-align: center; }

.totals-area { display: flex; justify-content: flex-end; }
.totals-box { width: 300px; background: #F8FAFC; padding: 20px; border-radius: 8px; border: 1px solid #E2E8F0; }
.total-line { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #475569; }
.total-line.grand { border-top: 2px solid #CBD5E1; padding-top: 12px; margin-top: 4px; font-size: 18px; font-weight: 700; color: #1E293B; }

.status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-align: center; width: 100%; margin-bottom: 16px; }
.status-unpaid { background: #FEF9C3; color: #CA8A04; border: 1px solid #FEF08A; }
.status-paid { background: #ECFDF5; color: #10B981; border: 1px solid #A7F3D0; }

.attachment-preview { background: #F1F5F9; border: 1px dashed #CBD5E1; border-radius: 8px; padding: 32px; text-align: center; margin-top: 24px; }
.attachment-preview i { font-size: 40px; color: #94A3B8; margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.expenses.index') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux dépenses</a>
        <h1 class="gel-page-title">Dépense {{ $expense->invoice_number }}</h1>
    </div>
</div>

<div class="expense-wrapper">
    <div class="expense-main">
        <div class="expense-header">
            <div class="vendor-info">
                <div style="font-size:11px; font-weight:700; color:#94A3B8; text-transform:uppercase; margin-bottom:4px;">Fournisseur</div>
                <h2>{{ $expense->partner->company_name ?? ($expense->partner->first_name . ' ' . $expense->partner->last_name) ?? 'N/A' }}</h2>
                @if($expense->partner && $expense->partner->address) <p>{{ $expense->partner->address }}</p> @endif
                @if($expense->partner && $expense->partner->tax_id) <p>IFU: {{ $expense->partner->tax_id }}</p> @endif
            </div>
            <div class="expense-meta">
                <h3>Facture d'achat</h3>
                <div class="num">{{ $expense->invoice_number }}</div>
                <div style="margin-top:12px; font-size:13px; color:#475569;">
                    <strong>Date :</strong> {{ \Carbon\Carbon::parse($expense->invoice_date)->format('d/m/Y') }}<br>
                    <strong>Réf Frs :</strong> {{ $expense->terms_conditions ?? 'N/A' }}
                </div>
            </div>
        </div>

        <table class="lines-table">
            <thead>
                <tr>
                    <th style="width:50%;">Description (Imputation)</th>
                    <th class="center" style="width:10%;">Qté</th>
                    <th class="right" style="width:15%;">Prix Unit. HT</th>
                    <th class="right" style="width:10%;">TVA</th>
                    <th class="right" style="width:15%;">Total TTC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expense->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="center">{{ $line->quantity }}</td>
                    <td class="right font-monospace">{{ number_format($line->unit_price, 0, ',', ' ') }} F</td>
                    <td class="right font-monospace">{{ $line->vat_rate }}%</td>
                    <td class="right font-monospace">{{ number_format($line->total, 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-area">
            <div class="totals-box">
                <div class="total-line">
                    <span>Sous-total HT</span>
                    <span class="font-monospace">{{ number_format($expense->subtotal, 0, ',', ' ') }} F</span>
                </div>
                <div class="total-line">
                    <span>TVA</span>
                    <span class="font-monospace">{{ number_format($expense->vat_total, 0, ',', ' ') }} F</span>
                </div>
                @if(stripos($expense->notes, 'Retenue AIB') !== false)
                    @php
                        // Extraction basique pour l'affichage si le montant AIB est dans les notes (logique du controller store)
                        preg_match('/Retenue AIB.*:\s*([0-9.]+)/', $expense->notes, $matches);
                        $aibAmount = isset($matches[1]) ? (float)$matches[1] : 0;
                    @endphp
                    @if($aibAmount > 0)
                    <div class="total-line" style="color:#EF4444;">
                        <span>Retenue AIB</span>
                        <span class="font-monospace">- {{ number_format($aibAmount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                @endif
                <div class="total-line grand">
                    <span>Net à Payer</span>
                    <span class="font-monospace">{{ number_format($expense->total, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        @if($expense->notes)
        <div style="margin-top: 32px; font-size:13px; color:#475569; background:#F8FAFC; padding:16px; border-radius:8px; border:1px solid #E2E8F0;">
            <strong>Note / Mémo :</strong><br>
            {{ nl2br(e($expense->notes)) }}
        </div>
        @endif

        @if($expense->attachment_path)
            <div class="attachment-preview">
                <i class="fas fa-file-invoice"></i>
                <div style="font-weight:600; margin-bottom:12px;">Pièce jointe originale</div>
                <a href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank" class="btn btn-primary btn-sm" style="background:var(--gel-primary); border:none;"><i class="fas fa-download"></i> Voir la facture</a>
            </div>
        @endif
    </div>

    <div class="expense-sidebar">
        <div class="sidebar-box">
            <div class="sidebar-title"><i class="fas fa-info-circle"></i> Statut & Paiement</div>
            
            @if($expense->status == 'unpaid') <div class="status-badge status-unpaid">À PAYER</div>
            @elseif($expense->status == 'paid') <div class="status-badge status-paid">PAYÉ</div>
            @elseif($expense->status == 'disputed') <div class="status-badge status-disputed" style="background:#FEF2F2; color:#EF4444; border:1px solid #FECACA;">LITIGE</div>
            @endif

            @if($expense->status == 'unpaid')
                <button class="btn btn-success w-100 mb-2" style="background:#10B981; border:none; font-weight:600;"><i class="fas fa-money-bill-wave"></i> Enregistrer le paiement</button>
            @endif
            <button class="btn btn-outline-secondary w-100" style="font-size:13px; font-weight:600;"><i class="fas fa-edit"></i> Modifier</button>
        </div>
    </div>
</div>
@endsection
