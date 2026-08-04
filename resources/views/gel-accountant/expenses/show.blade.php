@extends('layouts.gel-accountant')

@section('title', 'Détail Dépense - ' . $expense->invoice_number)

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-file-invoice" style="color:var(--gel-primary); margin-right:8px;"></i> Dépense {{ $expense->invoice_number }}</h1>
        <p class="gel-page-subtitle">Détails de la facture fournisseur / dépense</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.expenses.index') }}" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Informations Générales</h3>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Fournisseur :</div>
                <div class="col-sm-8 font-weight-bold">
                    @if($expense->partner)
                        {{ $expense->partner->company_name ?: $expense->partner->first_name.' '.$expense->partner->last_name }}
                    @else
                        {{ $expense->partner_name ?? '-' }}
                    @endif
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Date :</div>
                <div class="col-sm-8">{{ \Carbon\Carbon::parse($expense->invoice_date)->format('d/m/Y') }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Référence :</div>
                <div class="col-sm-8">{{ $expense->terms_conditions ?? '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Statut :</div>
                <div class="col-sm-8">
                    @if($expense->status == 'paid')
                        <span class="badge bg-success">Payée</span>
                    @elseif($expense->status == 'draft')
                        <span class="badge bg-secondary">Brouillon</span>
                    @else
                        <span class="badge bg-warning">{{ $expense->status }}</span>
                    @endif
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Mémo :</div>
                <div class="col-sm-8">{{ $expense->notes ?? '-' }}</div>
            </div>
        </div>

        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">Lignes de Dépense</h3>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th style="text-align:right;">Montant HT</th>
                        <th style="text-align:right;">TVA</th>
                        <th style="text-align:right;">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expense->lines as $line)
                    <tr>
                        <td>{{ $line->description }}</td>
                        <td style="text-align:right;">{{ number_format($line->subtotal, 0, ',', ' ') }}</td>
                        <td style="text-align:right;">{{ number_format($line->vat_amount, 0, ',', ' ') }} ({{ $line->vat_rate }}%)</td>
                        <td style="text-align:right; font-weight:bold;">{{ number_format($line->total, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right;">Sous-total HT</th>
                        <th style="text-align:right;">{{ number_format($expense->subtotal, 0, ',', ' ') }} FCFA</th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align:right;">Total TVA</th>
                        <th style="text-align:right;">{{ number_format($expense->tax_amount, 0, ',', ' ') }} FCFA</th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align:right; font-size:16px;">Total TTC</th>
                        <th style="text-align:right; font-size:16px; color:var(--gel-primary);">{{ number_format($expense->total, 0, ',', ' ') }} FCFA</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        @if($expense->emecef_nim)
        <div class="gel-card p-4 mb-4" style="border-top: 4px solid var(--gel-success);">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:15px;"><i class="fas fa-qrcode" style="color:var(--gel-success);"></i> Informations e-MECeF (Fournisseur)</h3>
            <p style="margin-bottom:8px; font-size:13px;"><strong>NIM :</strong> {{ $expense->emecef_nim }}</p>
            <p style="margin-bottom:8px; font-size:13px;"><strong>Compteur :</strong> {{ $expense->emecef_compteur ?: '—' }}</p>
            <p style="margin-bottom:0; font-size:13px;"><strong>Date de certification :</strong> {{ $expense->emecef_datetime ? \Carbon\Carbon::parse($expense->emecef_datetime)->format('d/m/Y H:i') : '—' }}</p>
        </div>
        @endif

        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;"><i class="fas fa-paperclip"></i> Pièce jointe</h3>
            @if($expense->attachment_path)
                @php
                    $ext = strtolower(pathinfo($expense->attachment_path, PATHINFO_EXTENSION));
                @endphp
                
                @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                    <div style="text-align:center; margin-bottom:15px;">
                        <img src="{{ Storage::url($expense->attachment_path) }}" alt="Reçu" style="max-width:100%; border-radius:8px; border:1px solid #ddd;">
                    </div>
                @else
                    <div style="text-align:center; padding:30px; background:#f8f9fa; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-file-pdf" style="font-size:48px; color:#e25555; margin-bottom:10px;"></i>
                        <div>Document PDF</div>
                    </div>
                @endif
                <div class="d-grid">
                    <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt"></i> Ouvrir le document</a>
                </div>
            @else
                <div style="text-align:center; padding:30px; color:#999;">
                    <i class="fas fa-file-image" style="font-size:48px; margin-bottom:10px; opacity:0.3;"></i>
                    <p class="mb-0">Aucun justificatif joint</p>
                </div>
            @endif
        </div>
        
        <div class="gel-card p-4 mb-4">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;"><i class="fas fa-book"></i> Comptabilité</h3>
            <p class="text-muted" style="font-size:13px;">Cette dépense génère automatiquement les écritures de charges correspondantes dans le journal des achats.</p>
            <div class="d-grid mt-3">
                <a href="#" class="btn btn-outline-secondary btn-sm"><i class="fas fa-exchange-alt"></i> Voir les écritures</a>
            </div>
        </div>
    </div>
</div>

@endsection
