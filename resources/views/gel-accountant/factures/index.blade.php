@extends('layouts.gel-accountant')

@section('title', 'Factures')

@section('content')

{{-- â•â•â•â•â•â•â•â•â•â•â• EN-TÀŠTE â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Factures</h1>
        <p class="gel-page-subtitle">Gérez vos factures clients, suivez les paiements et les échéances</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('gel-accountant.factures.create') }}" class="gel-btn gel-btn-primary">
            <i class="fas fa-plus"></i> Créer une facture
        </a>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• CARTES STATISTIQUES â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="facture-stats-grid">
    <div class="facture-stat-card">
        <div class="facture-stat-icon" style="background:rgba(59, 130, 246, 0.1); color:var(--gel-info);">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <div class="facture-stat-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="facture-stat-label">Total factures</div>
        </div>
    </div>
    <div class="facture-stat-card">
        <div class="facture-stat-icon" style="background:rgba(245, 158, 11, 0.1); color:var(--gel-warning);">
            <i class="fas fa-pen"></i>
        </div>
        <div>
            <div class="facture-stat-value">{{ $stats['draft'] ?? 0 }}</div>
            <div class="facture-stat-label">Brouillons</div>
        </div>
    </div>
    <div class="facture-stat-card">
        <div class="facture-stat-icon" style="background:rgba(239, 68, 68, 0.1); color:var(--gel-danger);">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <div class="facture-stat-value">{{ $stats['overdue'] ?? 0 }}</div>
            <div class="facture-stat-label">En retard</div>
        </div>
    </div>
    <div class="facture-stat-card">
        <div class="facture-stat-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--gel-success);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <div class="facture-stat-value">{{ $stats['paid'] ?? 0 }}</div>
            <div class="facture-stat-label">Payées</div>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• MONTANTS â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="facture-amounts-row">
    <div class="facture-amount-card amount-total">
        <div class="facture-amount-label">Total facturé</div>
        <div class="facture-amount-value">{{ number_format($stats['total_amount'] ?? 0, 0, ',', ' ') }} <span>FCFA</span></div>
    </div>
    <div class="facture-amount-card amount-due">
        <div class="facture-amount-label">Solde dû</div>
        <div class="facture-amount-value">{{ number_format($stats['total_due'] ?? 0, 0, ',', ' ') }} <span>FCFA</span></div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• FILTRES + RECHERCHE â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="facture-toolbar">
    <form method="GET" action="{{ route('gel-accountant.factures.index') }}" style="display:flex; gap:10px; align-items:center; flex:1; flex-wrap:wrap;">
        <div class="facture-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nÂ° ou client..." class="gel-form-control">
        </div>
        <select name="status" class="gel-form-select" style="width:auto; min-width:160px;" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            @foreach(\App\Models\Invoice::STATUS as $key => $label)
                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="gel-btn gel-btn-secondary gel-btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â• TABLEAU DES FACTURES â•â•â•â•â•â•â•â•â•â•â• --}}
<div class="gel-card gel-p-0 p-4 mb-4" style="overflow:hidden;">
    @if($invoices->count() > 0)
    <table class="gel-table">
        <thead>
            <tr>
                <th>NÂ° Facture</th>
                <th>Client</th>
                <th>Date</th>
                <th>Échéance</th>
                <th style="text-align:right;">Total TTC</th>
                <th style="text-align:right;">Solde dû</th>
                <th>Statut</th>
                <th>e-MECeF</th>
                <th style="width:60px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr class="facture-row" onclick="window.location='{{ route('gel-accountant.factures.show', $invoice->id) }}'">
                <td>
                    <span class="facture-number">{{ $invoice->invoice_number }}</span>
                </td>
                <td>
                    <div class="facture-client-info">
                        <div class="facture-client-avatar">{{ strtoupper(substr($invoice->partner_name ?? 'X', 0, 2)) }}</div>
                        <span>{{ $invoice->partner_name ?? '—' }}</span>
                    </div>
                </td>
                <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '—' }}</td>
                <td>
                    @if($invoice->isOverdue())
                        <span style="color:var(--gel-danger); font-weight:600;">{{ $invoice->due_date->format('d/m/Y') }}</span>
                    @else
                        {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '—' }}
                    @endif
                </td>
                <td style="text-align:right; font-weight:600;">{{ number_format($invoice->total, 0, ',', ' ') }} F</td>
                <td style="text-align:right;">
                    @if($invoice->balance_due > 0)
                        <span style="color:var(--gel-danger); font-weight:600;">{{ number_format($invoice->balance_due, 0, ',', ' ') }} F</span>
                    @else
                        <span style="color:var(--gel-success);">0 F</span>
                    @endif
                </td>
                <td>
                    @php
                        $statusColors = [
                            'draft' => 'background:rgba(107, 114, 128, 0.1); color:var(--gel-text-secondary);',
                            'sent' => 'background:rgba(59, 130, 246, 0.1); color:var(--gel-info);',
                            'confirmed' => 'background:rgba(59, 130, 246, 0.1); color:var(--gel-primary);',
                            'partially_paid' => 'background:rgba(245, 158, 11, 0.1); color:var(--gel-warning);',
                            'paid' => 'background:rgba(16, 185, 129, 0.1); color:var(--gel-success);',
                            'overdue' => 'background:rgba(239, 68, 68, 0.1); color:var(--gel-danger);',
                            'cancelled' => 'background:rgba(107, 114, 128, 0.1); color:var(--gel-text-muted);',
                        ];
                        $color = $statusColors[$invoice->status] ?? 'background:rgba(107, 114, 128, 0.1); color:var(--gel-text-secondary);';
                    @endphp
                    <span class="gel-badge" style="{{ $color }}">{{ \App\Models\Invoice::STATUS[$invoice->status] ?? $invoice->status }}</span>
                </td>
                <td>
                    @if($invoice->emecef_statut === 'emise')
                        <span class="gel-badge" style="background:rgba(16, 185, 129, 0.1); color:var(--gel-success);" title="NIM: {{ $invoice->emecef_nim }}">
                            <i class="fas fa-check-circle"></i> Certifiée
                        </span>
                    @elseif($invoice->emecef_statut === 'annulee')
                        <span class="gel-badge" style="background:rgba(239, 68, 68, 0.1); color:var(--gel-danger);">
                            <i class="fas fa-times-circle"></i> Annulée
                        </span>
                    @else
                        <span class="gel-badge" style="background:rgba(107, 114, 128, 0.1); color:var(--gel-text-secondary);">
                            Non émise
                        </span>
                    @endif
                </td>
                <td>
                    <div class="gel-dropdown">
                        <button class="topbar-btn" style="color:var(--gel-text-secondary);" onclick="event.stopPropagation(); this.nextElementSibling.classList.toggle('show');">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="gel-dropdown-menu">
                            <a class="gel-dropdown-item" href="{{ route('gel-accountant.factures.show', $invoice->id) }}"><i class="fas fa-eye"></i> Voir</a>
                            <a class="gel-dropdown-item" href="{{ route('gel-accountant.factures.pdf', $invoice->id) }}" target="_blank"><i class="fas fa-download"></i> Télécharger PDF</a>
                            @if($invoice->status === 'draft')
                            <form action="{{ route('gel-accountant.factures.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Supprimer cette facture ?');">
                                @csrf @method('DELETE')
                                <button class="gel-dropdown-item" style="color:var(--gel-danger);"><i class="fas fa-trash"></i> Supprimer</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($invoices->hasPages())
    <div class="gel-pagination">
        {{ $invoices->links() }}
    </div>
    @endif

    @else
    {{-- État vide --}}
    <div class="facture-empty">
        <div class="facture-empty-icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <h3>Aucune facture pour le moment</h3>
        <p>Créez votre première facture pour commencer À  suivre vos revenus et vos encaissements.</p>
        <a href="{{ route('gel-accountant.factures.create') }}" class="gel-btn gel-btn-primary" style="margin-top:16px;">
            <i class="fas fa-plus"></i> Créer ma première facture
        </a>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    /* â•â•â• Stats Grid â•â•â• */
    .facture-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .facture-stat-card {
        background: white;
        border: 1px solid var(--gel-border);
        border-radius: 8px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 150ms, transform 150ms;
    }
    .facture-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }
    .facture-stat-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .facture-stat-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--gel-text-primary);
        line-height: 1.1;
    }
    .facture-stat-label {
        font-size: 12px;
        color: var(--gel-text-secondary);
        margin-top: 2px;
    }

    /* â•â•â• Amounts Row â•â•â• */
    .facture-amounts-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .facture-amount-card {
        padding: 18px 24px;
        border-radius: 8px;
        border: 1px solid var(--gel-border);
    }
    .facture-amount-card.amount-total {
        background: var(--gel-primary-light);
        border-color: var(--gel-primary);
    }
    .facture-amount-card.amount-due {
        background: rgba(239, 68, 68, 0.05);
        border-color: var(--gel-danger);
    }
    .facture-amount-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gel-text-secondary);
        margin-bottom: 4px;
    }
    .facture-amount-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--gel-text-primary);
    }
    .facture-amount-value span {
        font-size: 14px;
        font-weight: 500;
        color: var(--gel-text-secondary);
    }

    /* â•â•â• Toolbar â•â•â• */
    .facture-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        gap: 12px;
    }
    .facture-search-wrap {
        position: relative;
        flex: 1;
        max-width: 340px;
    }
    .facture-search-wrap i {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: var(--gel-text-muted);
        font-size: 13px;
    }
    .facture-search-wrap .gel-form-control {
        padding-left: 36px;
    }

    /* â•â•â• Table Row â•â•â• */
    .facture-row {
        cursor: pointer;
        transition: background 80ms;
    }
    .facture-row:hover {
        background: var(--gel-sidebar-hover) !important;
    }
    .facture-number {
        font-weight: 600;
        color: var(--gel-primary);
        font-size: 13px;
    }
    .facture-client-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .facture-client-avatar {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: var(--gel-primary-light);
        color: var(--gel-primary);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* â•â•â• Empty State â•â•â• */
    .facture-empty {
        text-align: center;
        padding: 60px 40px;
    }
    .facture-empty-icon {
        width: 80px; height: 80px;
        background: var(--gel-primary-light);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
        color: var(--gel-primary);
    }
    .facture-empty h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--gel-text-primary);
        margin-bottom: 8px;
    }
    .facture-empty p {
        font-size: 14px;
        color: var(--gel-text-secondary);
        max-width: 400px;
        margin: 0 auto;
    }

    /* â•â•â• Responsive â•â•â• */
    @media (max-width: 900px) {
        .facture-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .facture-stats-grid { grid-template-columns: 1fr; }
        .facture-amounts-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

