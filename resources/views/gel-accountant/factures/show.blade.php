@extends('layouts.gel-accountant')

@section('title', 'Détail de la Facture ' . $invoice->invoice_number)

@push('styles')
<style>
/* ==========================================================================
   FACTURE SHOW - DESIGN
   ========================================================================== */
.invoice-wrapper {
    display: flex; gap: 24px; margin-bottom: 24px;
}
.invoice-document {
    flex: 1; background: white; padding: 40px; border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--gel-border);
}
.invoice-sidebar {
    width: 320px; display: flex; flex-direction: column; gap: 20px;
}
.sidebar-box {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.sidebar-title {
    font-size: 14px; font-weight: 700; color: #1E293B; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;
}

.invoice-header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
.invoice-logo { font-size: 24px; font-weight: 800; color: var(--gel-primary); }
.invoice-title { font-size: 28px; font-weight: 300; color: #64748B; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
.invoice-num { font-size: 16px; font-weight: 700; color: #1E293B; margin-top: 4px; }

.invoice-addresses { display: flex; justify-content: space-between; margin-bottom: 40px; }
.address-box { width: 45%; }
.address-title { font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; margin-bottom: 8px; }
.address-content { font-size: 14px; color: #334155; line-height: 1.6; }
.address-content strong { color: #1E293B; font-size: 16px; }

.invoice-meta { display: flex; gap: 40px; margin-bottom: 40px; padding: 16px 20px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; }
.meta-item { display: flex; flex-direction: column; }
.meta-label { font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px; }
.meta-value { font-size: 14px; font-weight: 600; color: #1E293B; }

.lines-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
.lines-table th { background: #F1F5F9; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; padding: 12px; text-align: left; border-bottom: 2px solid #E2E8F0; }
.lines-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; font-size: 13px; color: #334155; }
.lines-table th.right, .lines-table td.right { text-align: right; }
.lines-table th.center, .lines-table td.center { text-align: center; }

.totals-area { display: flex; justify-content: flex-end; }
.totals-box { width: 320px; }
.total-line { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #475569; }
.total-line.grand { border-top: 2px solid #E2E8F0; padding-top: 12px; margin-top: 4px; font-size: 18px; font-weight: 700; color: var(--gel-primary); }

.status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-align: center; width: 100%; margin-bottom: 16px; }
.status-draft { background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }
.status-sent { background: #DBEAFE; color: #2563EB; border: 1px solid #BFDBFE; }
.status-paid { background: #ECFDF5; color: #10B981; border: 1px solid #A7F3D0; }
.status-overdue { background: #FEF2F2; color: #EF4444; border: 1px solid #FECACA; }

.action-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; margin-bottom: 10px; }
.action-primary { background: var(--gel-primary); color: white; }
.action-primary:hover { background: var(--gel-primary-hover); color: white; }
.action-secondary { background: white; border: 1px solid #E2E8F0; color: #475569; }
.action-secondary:hover { background: #F8FAFC; border-color: #CBD5E1; }
.action-danger { background: white; border: 1px solid #FECACA; color: #EF4444; }
.action-danger:hover { background: #FEF2F2; }

.emecef-box { background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 8px; padding: 16px; text-align: center; }
.emecef-title { font-size: 12px; font-weight: 700; color: #64748B; margin-bottom: 8px; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.factures.index') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux factures</a>
        <h1 class="gel-page-title">Facture {{ $invoice->invoice_number }}</h1>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
</div>
@endif

<div class="invoice-wrapper">
    <div class="invoice-document">
        
        <div class="invoice-header-top">
            <div>
                <div class="invoice-logo">MON CABINET COMPTABLE</div>
                <div style="font-size:13px; color:#64748B; margin-top:8px;">
                    Adresse du cabinet<br>
                    Téléphone : +225 00 00 00 00<br>
                    Email : contact@cabinet.com
                </div>
            </div>
            <div style="text-align: right;">
                <h1 class="invoice-title">FACTURE</h1>
                <div class="invoice-num">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <div class="invoice-addresses">
            <div class="address-box">
                <div class="address-title">Facturé à :</div>
                <div class="address-content">
                    <strong>{{ $invoice->partner_name }}</strong><br>
                    @if($invoice->partner_address) {{ $invoice->partner_address }}<br> @endif
                    @if($invoice->partner_tax_id) NIF : {{ $invoice->partner_tax_id }}<br> @endif
                    @if($invoice->partner && $invoice->partner->email) Email : {{ $invoice->partner->email }} @endif
                </div>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="meta-item">
                <span class="meta-label">Date de facturation</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Date d'échéance</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</span>
            </div>
            @if($invoice->payment_term)
            <div class="meta-item">
                <span class="meta-label">Conditions</span>
                <span class="meta-value">{{ $invoice->payment_term }}</span>
            </div>
            @endif
        </div>

        <table class="lines-table">
            <thead>
                <tr>
                    <th style="width:45%;">Description</th>
                    <th class="center" style="width:10%;">Qté</th>
                    <th class="right" style="width:15%;">Prix Unit.</th>
                    <th class="right" style="width:15%;">TVA</th>
                    <th class="right" style="width:15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
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
                    <span class="font-monospace">{{ number_format($invoice->subtotal, 0, ',', ' ') }} F</span>
                </div>
                <div class="total-line">
                    <span>TVA</span>
                    <span class="font-monospace">{{ number_format($invoice->vat_total, 0, ',', ' ') }} F</span>
                </div>
                <div class="total-line grand">
                    <span>Total TTC</span>
                    <span class="font-monospace">{{ number_format($invoice->total, 0, ',', ' ') }} F</span>
                </div>
                
                @if($invoice->paid_amount > 0)
                <div class="total-line" style="margin-top:12px; color:#10B981;">
                    <span>Montant Payé</span>
                    <span class="font-monospace">- {{ number_format($invoice->paid_amount, 0, ',', ' ') }} F</span>
                </div>
                <div class="total-line" style="font-weight:700; color:#EF4444;">
                    <span>Reste à payer</span>
                    <span class="font-monospace">{{ number_format($invoice->balance_due, 0, ',', ' ') }} F</span>
                </div>
                @endif
            </div>
        </div>
        
        @if($invoice->notes)
        <div style="margin-top: 40px; font-size:13px; color:#64748B;">
            <strong>Notes :</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif
        
    </div>

    <div class="invoice-sidebar">
        
        <div class="sidebar-box">
            <div class="sidebar-title"><i class="fas fa-info-circle"></i> Statut & Actions</div>
            
            @if($invoice->status == 'draft') <div class="status-badge status-draft">BROUILLON</div>
            @elseif($invoice->status == 'sent') <div class="status-badge status-sent">ENVOYÉE</div>
            @elseif($invoice->status == 'paid') <div class="status-badge status-paid">PAYÉE</div>
            @elseif($invoice->status == 'overdue') <div class="status-badge status-overdue">EN RETARD</div>
            @endif
            
            <a href="{{ route('gel-accountant.factures.show', ['facture' => $invoice->id, 'export' => 'pdf']) }}" class="action-btn action-secondary" target="_blank">
                <i class="fas fa-file-pdf"></i> Voir le PDF
            </a>
            
            <a href="{{ route('gel-accountant.factures.downloadPdf', $invoice->id) }}" class="action-btn action-primary">
                <i class="fas fa-download"></i> Télécharger PDF
            </a>

            @if($invoice->status == 'draft')
                <form action="{{ route('gel-accountant.factures.destroy', $invoice->id) }}" method="POST" style="margin-top:20px;">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn action-danger" onclick="return confirm('Supprimer cette facture ?')">
                        <i class="fas fa-trash"></i> Supprimer le brouillon
                    </button>
                </form>
            @endif
        </div>

        <div class="sidebar-box">
            <div class="sidebar-title"><i class="fas fa-qrcode"></i> Certification e-MECeF</div>
            
            @if($invoice->emecef_statut === 'emise')
                <div class="emecef-box" style="background: #ECFDF5; border-color: #A7F3D0;">
                    <div class="emecef-title" style="color: #065F46;"><i class="fas fa-check-circle"></i> Facture Certifiée</div>
                    <div style="font-size:11px; margin-bottom:8px; word-break:break-all;">NIM: {{ $invoice->emecef_nim }}</div>
                    @if($qrCodeBase64)
                        <img src="{{ $qrCodeBase64 }}" alt="QR Code" style="width:120px; height:120px; display:block; margin:0 auto;">
                    @endif
                </div>
            @else
                <div class="emecef-box">
                    <div class="emecef-title">Non certifiée</div>
                    <p style="font-size:12px; color:#64748B;">Cette facture n'est pas encore enregistrée sur les serveurs de la DGI.</p>
                    <form action="{{ route('gel-accountant.factures.certify', $invoice->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="action-btn action-secondary" style="border-color:var(--gel-primary); color:var(--gel-primary);">
                            <i class="fas fa-cloud-upload-alt"></i> Certifier (DGI)
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
