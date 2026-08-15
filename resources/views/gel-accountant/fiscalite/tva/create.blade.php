@extends('layouts.gel-accountant')

@section('title', 'Nouvelle Déclaration TVA')

@push('styles')
<style>
/* ==========================================================================
   TVA CREATE - DESIGN
   ========================================================================== */
.form-section {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; padding: 24px; margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.section-title {
    font-size: 15px; font-weight: 700; color: #1E293B; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;
}
.section-title i { color: var(--gel-primary); }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
    background: #F8FAFC;
}
.form-control:focus, .form-select:focus { background: white; border-color: var(--gel-primary); }

.summary-box {
    background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;
}
.summary-line {
    display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed #CBD5E1; font-size: 14px; color: #475569;
}
.summary-line:last-child { border-bottom: none; }
.summary-line .amount { font-family: monospace; font-size: 16px; font-weight: 700; color: #1E293B; }
.summary-line.net { margin-top: 8px; padding-top: 16px; border-top: 2px solid #94A3B8; border-bottom: none; }
.summary-line.net .amount { font-size: 20px; color: var(--gel-primary); }

.btn-primary-action { background: var(--gel-primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); }
.btn-cancel { background: white; color: #475569; border: 1px solid #E2E8F0; padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; }

.empty-state { text-align: center; padding: 40px; color: #64748B; background: #F8FAFC; border-radius: 8px; border: 1px dashed #CBD5E1; }
.empty-state i { font-size: 48px; color: #94A3B8; margin-bottom: 16px; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <a href="{{ route('gel-accountant.fiscalite.tva.index') }}" style="font-size:13px; color:#64748B; text-decoration:none; margin-bottom:8px; display:inline-block;"><i class="fas fa-arrow-left"></i> Retour aux déclarations</a>
        <h1 class="gel-page-title">Nouvelle Déclaration TVA</h1>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
</div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="form-section">
            <div class="section-title"><i class="fas fa-calendar-alt"></i> Période de déclaration</div>
            <form action="{{ route('gel-accountant.fiscalite.tva.create') }}" method="GET">
                <div class="form-group">
                    <label class="form-label">Mois à déclarer (YYYY-MM)</label>
                    <input type="month" name="period" class="form-control" value="{{ $period ?? date('Y-m') }}" required>
                </div>
                <button type="submit" class="btn-primary-action w-100 justify-content-center"><i class="fas fa-calculator"></i> Calculer la TVA</button>
            </form>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="form-section">
            <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Résultat du calcul</div>
            
            @if(isset($result))
                <div class="summary-box mb-4">
                    <div class="summary-line">
                        <span>TVA Collectée (Ventes)</span>
                        <span class="amount text-success">+ {{ number_format($result['tva_collected'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="summary-line">
                        <span>TVA Déductible (Achats)</span>
                        <span class="amount text-danger">- {{ number_format($result['tva_deductible'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="summary-line net">
                        <span>TVA Nette ({{ $result['tva_net'] > 0 ? 'À Payer' : 'Crédit de TVA' }})</span>
                        <span class="amount" style="color: {{ $result['tva_net'] > 0 ? '#EF4444' : '#10B981' }}">{{ number_format($result['tva_net'], 0, ',', ' ') }} F</span>
                    </div>
                </div>

                <form action="{{ route('gel-accountant.fiscalite.tva.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="tva_collected" value="{{ $result['tva_collected'] }}">
                    <input type="hidden" name="tva_deductible" value="{{ $result['tva_deductible'] }}">
                    <input type="hidden" name="tva_net" value="{{ $result['tva_net'] }}">
                    
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('gel-accountant.fiscalite.tva.index') }}" class="btn-cancel">Annuler</a>
                        <button type="submit" class="btn-primary-action"><i class="fas fa-save"></i> Enregistrer la déclaration</button>
                    </div>
                </form>
            @else
                <div class="empty-state">
                    <i class="fas fa-calculator"></i>
                    <h4>Aucun calcul effectué</h4>
                    <p>Sélectionnez une période à gauche et cliquez sur "Calculer la TVA" pour générer les montants basés sur vos écritures comptables (ventes, achats avec codes de taxe TVA).</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
