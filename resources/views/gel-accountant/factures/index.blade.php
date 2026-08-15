@extends('layouts.gel-accountant')

@section('title', 'Gestion des Factures Clients')

@push('styles')
<style>
/* ==========================================================================
   FACTURES - BENTO GRID DESIGN
   ========================================================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}
.stat-card {
    background: white; border: 1px solid var(--gel-border); border-radius: 12px;
    padding: 20px; display: flex; align-items: center; gap: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.stat-icon.primary { background: #E0F2FE; color: #0284C7; }
.stat-icon.success { background: #DCFCE7; color: #16A34A; }
.stat-icon.warning { background: #FEF9C3; color: #CA8A04; }
.stat-icon.danger { background: #FEE2E2; color: #DC2626; }
.stat-info h3 { margin: 0; font-size: 20px; font-weight: 700; color: #1E293B; }
.stat-info p { margin: 0; font-size: 13px; color: #64748B; font-weight: 600; text-transform: uppercase; }

.table-container {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); overflow: hidden;
}
.filters-bar { padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; gap: 16px; align-items: center; background: #F8FAFC; }
.form-select-sm, .form-control-sm { border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 12px; font-size: 13px; outline: none; }
.form-select-sm:focus, .form-control-sm:focus { border-color: var(--gel-primary); }

.gel-table { width: 100%; border-collapse: collapse; }
.gel-table th { background: white; padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; }
.gel-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: #1E293B; vertical-align: middle; }
.gel-table tr:hover { background: #F8FAFC; }

.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.status-draft { background: #F1F5F9; color: #475569; }
.status-sent { background: #DBEAFE; color: #2563EB; }
.status-paid { background: #ECFDF5; color: #10B981; }
.status-overdue { background: #FEF2F2; color: #EF4444; }

.btn-action { background: white; border: 1px solid #E2E8F0; color: #64748B; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 12px; }
.btn-action:hover { background: #F1F5F9; color: var(--gel-primary); border-color: #CBD5E1; }
.btn-action.delete:hover { color: #EF4444; border-color: #FECACA; background: #FEF2F2; }

.btn-primary-action { background: var(--gel-primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); color: white; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Factures Clients</h1>
        <div class="gel-page-subtitle">Gérez la facturation et les encaissements de vos clients.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('gel-accountant.factures.create') }}" class="btn-primary-action">
            <i class="fas fa-plus"></i> Nouvelle Facture
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; font-size:14px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="stat-info">
            <p>Total Facturé</p>
            <h3>{{ number_format($stats['total_amount'], 0, ',', ' ') }} F</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <p>Reste à Encaisser</p>
            <h3>{{ number_format($stats['total_due'], 0, ',', ' ') }} F</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <p>Factures Payées</p>
            <h3>{{ $stats['paid'] }}</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <p>En Retard</p>
            <h3>{{ $stats['overdue'] }}</h3>
        </div>
    </div>
</div>

<div class="table-container">
    <form action="{{ route('gel-accountant.factures.index') }}" method="GET" class="filters-bar">
        <div style="font-weight:600; font-size:12px; color:#64748B; text-transform:uppercase;">Filtres :</div>
        
        <input type="text" name="search" class="form-control-sm" value="{{ request('search') }}" placeholder="N° Facture, Client..." style="width:200px;">
        
        <select name="status" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Envoyée</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>En Retard</option>
        </select>
        
        <button type="submit" class="btn-action" style="width:auto; padding:0 12px;">Filtrer</button>

        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('gel-accountant.factures.index') }}" class="text-danger" style="font-size:12px; text-decoration:none; margin-left:auto;"><i class="fas fa-times"></i> Réinitialiser</a>
        @endif
    </form>

    <div style="overflow-x: auto;">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Date</th>
                    <th>Client (Partenaire)</th>
                    <th class="text-end">Montant TTC</th>
                    <th class="text-end">Reste à payer</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td style="font-weight:600; color:var(--gel-primary);">{{ $inv->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}</td>
                        <td>{{ $inv->partner_name }}</td>
                        <td class="text-end font-monospace">{{ number_format($inv->total, 0, ',', ' ') }} F</td>
                        <td class="text-end font-monospace" style="color:{{ $inv->balance_due > 0 ? '#EF4444' : '#10B981' }}">{{ number_format($inv->balance_due, 0, ',', ' ') }} F</td>
                        <td class="text-center">
                            @if($inv->status == 'draft') <span class="status-badge status-draft">BROUILLON</span>
                            @elseif($inv->status == 'sent') <span class="status-badge status-sent">ENVOYÉE</span>
                            @elseif($inv->status == 'paid') <span class="status-badge status-paid">PAYÉE</span>
                            @elseif($inv->status == 'overdue') <span class="status-badge status-overdue">EN RETARD</span>
                            @endif
                        </td>
                        <td class="text-end" style="display:flex; gap:8px; justify-content:flex-end;">
                            <a href="{{ route('gel-accountant.factures.show', $inv->id) }}" class="btn-action" title="Détails"><i class="fas fa-eye"></i></a>
                            @if($inv->status == 'draft')
                                <form action="{{ route('gel-accountant.factures.destroy', $inv->id) }}" method="POST" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action delete" title="Supprimer" onclick="return confirm('Supprimer cette facture ?')"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center" style="padding: 40px 20px; color: #94A3B8;">
                                <i class="fas fa-file-invoice" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i>
                                <div>Aucune facture trouvée.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0; display:flex; justify-content:flex-end;">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
