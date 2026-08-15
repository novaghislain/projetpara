@extends('layouts.gel-secretary')
@section('title', 'Facturation & Ventes')

@push('styles')
<style>
/* ==========================================================================
   FACTURATION - BENTO GRID DESIGN
   ========================================================================== */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(100px, auto);
    gap: 24px;
    margin-bottom: 40px;
}

.bento-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.bento-header { grid-column: span 12; grid-row: span 1; display:flex; align-items:center; justify-content:space-between; background: linear-gradient(135deg, #1E293B 0%, #334155 100%); color:white; border:none;}
.bento-kpi { grid-column: span 3; grid-row: span 2; display:flex; flex-direction:column; justify-content:center;}
.bento-list { grid-column: span 12; grid-row: span 5; padding:0; overflow:hidden;}

/* Header Elements */
.hc-title { font-size: 24px; font-weight: 800; font-family: 'Inter', sans-serif;}
.hc-sub { font-size: 13px; opacity: 0.8; margin-top: 4px; }
.btn-new { background: #10B981; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display:flex; align-items:center; gap:8px; text-decoration:none;}
.btn-new:hover { background: #059669; transform: translateY(-2px); color:white;}

/* KPIs */
.kpi-value { font-size: 28px; font-weight: 800; color: #1E293B; line-height: 1; margin-bottom: 8px;}
.kpi-label { font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;}

/* Table Styles */
.cl-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #E2E8F0; background:rgba(255,255,255,0.5);}
.cl-title { font-size:18px; font-weight:800; color:#1E293B;}
.cl-search input { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:8px 12px 8px 36px; outline:none; font-size:13px; width:250px;}

.table-wrapper { width: 100%; overflow-x: auto; }
.f-table { width: 100%; border-collapse: collapse; text-align: left; }
.f-table th { padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; background: #F8FAFC; }
.f-table td { padding: 16px 24px; font-size: 13.5px; color: #334155; border-bottom: 1px solid #F1F5F9; font-weight: 500;}
.f-table tbody tr { transition: all 0.2s; cursor:pointer;}
.f-table tbody tr:hover { background: #F8FAFC; }

.badge-statut { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
.bs-brouillon { background: #F1F5F9; color: #64748B; }
.bs-validee { background: #ECFDF5; color: #10B981; }
.bs-payee { background: #EFF6FF; color: #3B82F6; }

/* Animations */
.stagger-1 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.2s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

@php
    $totalVentes = $factures->where('statut', '!=', 'brouillon')->sum('montant_ht');
    $totalAttente = $factures->where('statut', 'validée')->sum('montant_ttc');
    $nbBrouillons = $factures->where('statut', 'brouillon')->count();
    $nbFactures = $factures->count();
@endphp

<div class="bento-grid">

    <!-- HEADER -->
    <div class="bento-card bento-header stagger-1">
        <div>
            <div class="hc-title">Facturation & Ventes</div>
            <div class="hc-sub">Gestion des factures clients et du chiffre d'affaires.</div>
        </div>
        <div>
            <a href="{{ route('gel-secretary.ventes.create') }}" class="btn-new">
                <i class="fas fa-plus"></i> Créer une Facture
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="bento-card bento-kpi stagger-2">
        <div class="kpi-value">{{ number_format($totalVentes, 2, ',', ' ') }} €</div>
        <div class="kpi-label"><i class="fas fa-chart-line" style="color:#10B981;"></i> CA Généré (HT)</div>
    </div>
    <div class="bento-card bento-kpi stagger-2" style="animation-delay:0.3s;">
        <div class="kpi-value" style="color:#F59E0B;">{{ number_format($totalAttente, 2, ',', ' ') }} €</div>
        <div class="kpi-label"><i class="fas fa-clock" style="color:#F59E0B;"></i> En attente de paiement</div>
    </div>
    <div class="bento-card bento-kpi stagger-2" style="animation-delay:0.4s;">
        <div class="kpi-value">{{ $nbFactures }}</div>
        <div class="kpi-label"><i class="fas fa-file-invoice" style="color:#3B82F6;"></i> Total Factures</div>
    </div>
    <div class="bento-card bento-kpi stagger-2" style="animation-delay:0.5s;">
        <div class="kpi-value" style="color:#94A3B8;">{{ $nbBrouillons }}</div>
        <div class="kpi-label"><i class="fas fa-pen-square" style="color:#94A3B8;"></i> Brouillons</div>
    </div>

    <!-- TABLE -->
    <div class="bento-card bento-list stagger-2">
        <div class="cl-header">
            <div class="cl-title">Toutes les factures</div>
        </div>
        
        <div class="table-wrapper">
            <table class="f-table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Client / Contact</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th>Montant HT</th>
                        <th>Montant TTC</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $fac)
                        @php
                            $badge = 'bs-brouillon';
                            if($fac->statut == 'validée') $badge = 'bs-validee';
                            if($fac->statut == 'payée') $badge = 'bs-payee';
                        @endphp
                        <tr onclick="window.location.href='{{ route('gel-secretary.ventes.show', $fac->id) }}'">
                            <td style="font-weight:700;">{{ $fac->numero }}</td>
                            <td>
                                <div style="font-weight:700;">{{ $fac->contact->name ?? 'Inconnu' }}</div>
                                <div style="font-size:11px; color:#94A3B8;">{{ $fac->contact->company ?? '' }}</div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($fac->date_facture)->format('d/m/Y') }}</td>
                            <td>{{ $fac->date_echeance ? \Carbon\Carbon::parse($fac->date_echeance)->format('d/m/Y') : '—' }}</td>
                            <td>{{ number_format($fac->montant_ht, 2, ',', ' ') }} €</td>
                            <td style="font-weight:700;">{{ number_format($fac->montant_ttc, 2, ',', ' ') }} €</td>
                            <td><span class="badge-statut {{ $badge }}">{{ ucfirst($fac->statut) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:#94A3B8;">
                                <i class="fas fa-box-open" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i>
                                <div>Aucune facture enregistrée.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
