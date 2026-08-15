@extends('layouts.gel-accountant')

@section('title', 'Déclarations TVA')

@push('styles')
<style>
/* ==========================================================================
   TVA INDEX - DESIGN
   ========================================================================== */
.table-container {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); overflow: hidden;
}
.filters-bar { padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; }
.filters-left { display: flex; gap: 12px; align-items: center; }
.form-select-sm { border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 12px; font-size: 13px; outline: none; }
.form-select-sm:focus { border-color: var(--gel-primary); }

.gel-table { width: 100%; border-collapse: collapse; }
.gel-table th { background: white; padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; }
.gel-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: #1E293B; vertical-align: middle; }
.gel-table tr:hover { background: #F8FAFC; }

.btn-action { background: white; border: 1px solid #E2E8F0; color: #64748B; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 12px; transition: all 0.2s; }
.btn-action:hover { background: #F1F5F9; color: var(--gel-primary); border-color: #CBD5E1; }

.btn-primary-action { background: var(--gel-primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); }

.status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.status-draft { background: #FEF9C3; color: #CA8A04; border: 1px solid #FEF08A; }
.status-submitted { background: #ECFDF5; color: #10B981; border: 1px solid #A7F3D0; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Déclarations TVA</h1>
        <div class="gel-page-subtitle">Gérez vos déclarations mensuelles ou trimestrielles de TVA.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('gel-accountant.fiscalite.tva.create') }}" class="btn-primary-action">
            <i class="fas fa-plus"></i> Nouvelle Déclaration
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="table-container">
    <div class="filters-bar">
        <div class="filters-left">
            <select class="form-select-sm">
                <option value="">Toutes les années</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
            </select>
            <select class="form-select-sm">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="submitted">Soumise</option>
            </select>
        </div>
    </div>

    <table class="gel-table">
        <thead>
            <tr>
                <th>Période</th>
                <th>Type</th>
                <th class="text-end">TVA Collectée</th>
                <th class="text-end">TVA Déductible</th>
                <th class="text-end">TVA Nette (à payer/crédit)</th>
                <th>Statut</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($declarations as $declaration)
            <tr>
                <td style="font-weight:600;">{{ \Carbon\Carbon::parse($declaration->period . '-01')->translatedFormat('F Y') }}</td>
                <td>{{ $declaration->type === 'monthly' ? 'Mensuelle' : 'Trimestrielle' }}</td>
                <td class="text-end font-monospace">{{ number_format($declaration->tva_collected, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace">{{ number_format($declaration->tva_deductible, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace" style="font-weight:700; color:{{ $declaration->tva_net > 0 ? '#EF4444' : '#10B981' }}">
                    {{ number_format($declaration->tva_net, 0, ',', ' ') }} F
                </td>
                <td>
                    @if($declaration->status == 'draft') <span class="status-badge status-draft"><i class="fas fa-edit"></i> Brouillon</span>
                    @elseif($declaration->status == 'submitted') <span class="status-badge status-submitted"><i class="fas fa-check"></i> Soumise</span>
                    @endif
                </td>
                <td class="text-end">
                    @if($declaration->status == 'draft')
                        <form action="{{ route('gel-accountant.fiscalite.tva.submit', $declaration->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-action" title="Soumettre" style="color:#10B981; border-color:#A7F3D0; background:#ECFDF5;" onclick="return confirm('Confirmez-vous la soumission de cette déclaration ?')"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    @endif
                    <a href="#" class="btn-action" title="Voir les détails"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn-action" title="Télécharger PDF"><i class="fas fa-file-pdf"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="text-center" style="padding: 40px 20px; color: #94A3B8;">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i>
                        <div>Aucune déclaration TVA trouvée.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
