@extends('layouts.gel-accountant')

@section('title', 'Saisie des Écritures Comptables')

@push('styles')
<style>
/* ==========================================================================
   ECRITURES - BENTO GRID & TABLE DESIGN
   ========================================================================== */
.table-container {
    background: white;
    border: 1px solid var(--gel-border);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.filters-bar {
    padding: 16px 20px;
    border-bottom: 1px solid #E2E8F0;
    display: flex;
    gap: 16px;
    align-items: center;
    background: #F8FAFC;
    flex-wrap: wrap;
}

.form-select-sm, .form-control-sm {
    border: 1px solid #E2E8F0;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 13px;
    color: #475569;
    outline: none;
    min-width: 140px;
}
.form-select-sm:focus, .form-control-sm:focus { border-color: var(--gel-primary); }

.gel-table {
    width: 100%; border-collapse: collapse;
}
.gel-table th {
    background: white; padding: 12px 20px; font-size: 11px;
    font-weight: 700; color: #64748B; text-transform: uppercase;
    letter-spacing: 0.5px; border-bottom: 2px solid #E2E8F0;
}
.gel-table td {
    padding: 12px 20px; border-bottom: 1px solid #F1F5F9;
    font-size: 13px; color: #1E293B; vertical-align: middle;
}
.gel-table tr:hover { background: #F8FAFC; }

.status-badge {
    padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
}
.status-valide { background: #ECFDF5; color: #10B981; }
.status-brouillon { background: #FFFBEB; color: #F59E0B; }

.actions-cell { display: flex; gap: 8px; justify-content: flex-end; }
.btn-action {
    background: white; border: 1px solid #E2E8F0; color: #64748B;
    width: 28px; height: 28px; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s; text-decoration: none; font-size: 12px;
}
.btn-action:hover { background: #F1F5F9; color: var(--gel-primary); border-color: #CBD5E1; }
.btn-action.delete:hover { color: #EF4444; border-color: #FECACA; background: #FEF2F2; }

.totals-row td { background: #F8FAFC; font-weight: 700; border-top: 2px solid #E2E8F0; }

.btn-primary-action {
    background: var(--gel-primary); color: white; border: none;
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.2s;
}
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); color: white; }

.btn-outline-action {
    background: white; color: var(--gel-text-primary); border: 1px solid #E2E8F0;
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.2s;
}
.btn-outline-action:hover { background: #F8FAFC; border-color: #CBD5E1; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Saisie des Écritures</h1>
        <div class="gel-page-subtitle">Consultez et enregistrez les journaux d'écritures comptables.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('gel-accountant.comptabilite.ecritures.export') }}" class="btn-outline-action">
            <i class="fas fa-file-export"></i> Exporter CSV
        </a>
        <a href="{{ route('gel-accountant.comptabilite.ecritures.create') }}" class="btn-primary-action">
            <i class="fas fa-plus"></i> Nouvelle Écriture
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; font-size:14px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger d-flex align-items-center" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 8px; font-size:14px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-exclamation-circle me-2" style="font-size:18px;"></i>
    {{ $errors->first() }}
</div>
@endif

<div class="table-container">
    <form action="{{ route('gel-accountant.comptabilite.ecritures') }}" method="GET" class="filters-bar">
        <div style="font-weight:600; font-size:12px; color:#64748B; text-transform:uppercase;">Filtres :</div>
        
        <select name="client_id" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les dossiers</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
            @endforeach
        </select>
        
        <select name="journal_id" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les journaux</option>
            @foreach($journaux as $j)
                <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code }} - {{ $j->libelle }}</option>
            @endforeach
        </select>

        <select name="statut" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="valide" {{ request('statut') === 'valide' ? 'selected' : '' }}>Validée</option>
            <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
        </select>

        <input type="date" name="date_from" class="form-control-sm" value="{{ request('date_from') }}" onchange="this.form.submit()" placeholder="Du">
        <input type="date" name="date_to" class="form-control-sm" value="{{ request('date_to') }}" onchange="this.form.submit()" placeholder="Au">

        @if(request()->anyFilled(['client_id', 'journal_id', 'statut', 'date_from', 'date_to']))
            <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="text-danger" style="font-size:12px; text-decoration:none; margin-left:auto;"><i class="fas fa-times"></i> Réinitialiser</a>
        @endif
    </form>

    <div style="overflow-x: auto;">
        <table class="gel-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° Pièce</th>
                    <th>Libellé</th>
                    <th>Journal</th>
                    <th>Client (Dossier)</th>
                    <th class="text-end">Débit (F)</th>
                    <th class="text-end">Crédit (F)</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ecritures as $e)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($e->date_ecriture)->format('d/m/Y') }}</td>
                        <td style="font-weight:600; color:var(--gel-primary);">{{ $e->ref_piece ?? $e->numero }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($e->libelle, 40) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $e->journal->code ?? '-' }}</span></td>
                        <td>{{ $e->client->nom_entreprise ?? 'Cabinet (Interne)' }}</td>
                        <td class="text-end font-monospace">{{ number_format($e->total_debit, 0, ',', ' ') }}</td>
                        <td class="text-end font-monospace">{{ number_format($e->total_credit, 0, ',', ' ') }}</td>
                        <td class="text-center">
                            @if($e->valide)
                                <span class="status-badge status-valide"><i class="fas fa-lock me-1"></i> VALIDÉE</span>
                            @else
                                <span class="status-badge status-brouillon"><i class="fas fa-edit me-1"></i> BROUILLON</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <a href="{{ route('gel-accountant.comptabilite.ecritures.show', $e->id) }}" class="btn-action" title="Détails"><i class="fas fa-eye"></i></a>
                            
                            @if(!$e->valide)
                                <a href="{{ route('gel-accountant.comptabilite.ecritures.edit', $e->id) }}" class="btn-action" title="Modifier"><i class="fas fa-pen"></i></a>
                                
                                <form action="{{ route('gel-accountant.comptabilite.ecritures.valider', $e->id) }}" method="POST" style="margin:0; display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action" style="color:#10B981;" title="Valider définitivement" onclick="return confirm('Valider cette écriture ? Elle ne pourra plus être modifiée.')"><i class="fas fa-check-double"></i></button>
                                </form>
                                
                                <form action="{{ route('gel-accountant.comptabilite.ecritures.destroy', $e->id) }}" method="POST" style="margin:0; display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete" title="Supprimer" onclick="return confirm('Supprimer définitivement ce brouillon d\'écriture ?')"><i class="fas fa-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('gel-accountant.comptabilite.ecritures.extourner', $e->id) }}" method="POST" style="margin:0; display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action" style="color:#F59E0B;" title="Extourner (Créer une écriture inverse)" onclick="return confirm('Générer l\'extourne de cette écriture ?')"><i class="fas fa-undo"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="text-center" style="padding: 40px 20px; color: #94A3B8;">
                                <i class="fas fa-file-invoice" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i>
                                <div>Aucune écriture comptable trouvée.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                
                @if($ecritures->count() > 0)
                    <tr class="totals-row">
                        <td colspan="5" class="text-end">TOTAUX DE LA PAGE :</td>
                        <td class="text-end font-monospace text-primary" style="font-size:15px;">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                        <td class="text-end font-monospace text-primary" style="font-size:15px;">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($ecritures->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0; display:flex; justify-content:flex-end;">
        {{ $ecritures->links() }}
    </div>
    @endif
</div>
@endsection
