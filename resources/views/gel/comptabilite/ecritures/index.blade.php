@extends('layouts.gel')

@section('title', 'Écritures comptables — GEL Cabinet')

@section('styles')
<style>
    /* QuickBooks Online Style Overrides */
    .qbo-toolbar {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .qbo-search {
        border: 1px solid #d1d5db;
        border-radius: 9999px;
        padding: 0.4rem 1rem 0.4rem 2.5rem;
        font-size: 0.85rem;
        width: 300px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%239ca3af"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>') no-repeat 10px center;
        background-size: 16px;
    }
    .qbo-search:focus { outline: none; border-color: #2ca01c; box-shadow: 0 0 0 2px rgba(44, 160, 28, 0.2); }
    
    .qbo-grid-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .qbo-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .qbo-table th {
        text-align: left;
        padding: 0.75rem 1rem;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .qbo-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        vertical-align: middle;
    }
    .qbo-table tbody tr { transition: background-color 0.1s; }
    .qbo-table tbody tr:hover { background-color: #f9fafb; cursor: pointer; }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    .status-brouillon { background: #f3f4f6; color: #4b5563; }
    .status-valide { background: #def7ec; color: #03543f; }
    
    .action-menu-btn {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
    }
    .action-menu-btn:hover { background: #e5e7eb; color: #111827; }
    
    .btn-qbo-primary {
        background-color: #2ca01c;
        color: white;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background 0.15s;
    }
    .btn-qbo-primary:hover { background-color: #238016; color: white; }
    
    /* Hide scrollbar for select to make it look cleaner */
    .filter-select {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.4rem;
        font-size: 0.85rem;
        color: #374151;
        outline: none;
    }
    .filter-select:focus { border-color: #2ca01c; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1" style="font-weight: 700; color: #111827;">Écritures comptables</h1>
        <p class="text-muted" style="font-size: 0.9rem;">Gérez vos entrées de journal et écritures manuelles.</p>
    </div>
    <div>
        @can('comptabilite.creer')
        <a href="{{ route('gel.comptabilite.ecritures.create') }}" class="btn-qbo-primary text-decoration-none">
            Nouvelle écriture
        </a>
        @endcan
    </div>
</div>

<div class="qbo-grid-container">
    <form method="GET" id="filter-form">
        <div class="qbo-toolbar">
            <div class="d-flex align-items-center gap-3">
                <input type="text" name="search" class="qbo-search" placeholder="Rechercher par N° ou libellé..." value="{{ request('search') }}" onchange="this.form.submit()">
                
                <select name="journal_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">Tous les journaux</option>
                    @foreach($journaux as $j)
                    <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code }}</option>
                    @endforeach
                </select>

                <select name="valide" class="filter-select" onchange="this.form.submit()">
                    <option value="">Tous statuts</option>
                    <option value="0" {{ request('valide') === '0' ? 'selected' : '' }}>Brouillon</option>
                    <option value="1" {{ request('valide') === '1' ? 'selected' : '' }}>Validé</option>
                </select>
                
                @if(request()->anyFilled(['search', 'journal_id', 'valide']))
                    <a href="{{ route('gel.comptabilite.ecritures.index') }}" class="text-decoration-none" style="color: #6b7280; font-size: 0.8rem;">Effacer les filtres</a>
                @endif
            </div>
            
            <div class="d-flex gap-2">
                @can('comptabilite.exporter')
                <a href="{{ route('gel.comptabilite.ecritures.export', request()->query()) }}" class="action-menu-btn text-decoration-none" title="Exporter">
                    <i class="bi bi-download"></i>
                </a>
                @endcan
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="qbo-table">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>N° PIÈCE</th>
                    <th>JOURNAL</th>
                    <th>LIBELLÉ</th>
                    <th class="text-end">DÉBIT</th>
                    <th class="text-end">CRÉDIT</th>
                    <th class="text-center">STATUT</th>
                    <th class="text-end" style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($ecritures as $e)
                <tr onclick="window.location='{{ route('gel.comptabilite.ecritures.show', $e->id) }}'">
                    <td>{{ $e->date_ecriture->format('d/m/Y') }}</td>
                    <td style="font-weight: 500;">{{ $e->numero ?? '—' }}</td>
                    <td>{{ $e->journal?->code }}</td>
                    <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $e->libelle }}
                    </td>
                    <td class="text-end" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $e->total_debit, 0, ',', ' ') }}</td>
                    <td class="text-end" style="font-variant-numeric: tabular-nums;">{{ number_format((float) $e->total_credit, 0, ',', ' ') }}</td>
                    <td class="text-center">
                        @if($e->valide)
                            <span class="status-badge status-valide">Validé</span>
                        @else
                            <span class="status-badge status-brouillon">Brouillon</span>
                        @endif
                    </td>
                    <td class="text-end" onclick="event.stopPropagation();">
                        <div class="dropdown">
                            <button class="action-menu-btn" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
                                <li><a class="dropdown-item" href="{{ route('gel.comptabilite.ecritures.show', $e->id) }}">Consulter</a></li>
                                @if(!$e->valide && Auth::user()->can('comptabilite.modifier'))
                                <li><a class="dropdown-item" href="{{ route('gel.comptabilite.ecritures.edit', $e->id) }}">Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('gel.comptabilite.ecritures.destroy', $e->id) }}" method="POST" onsubmit="return confirm('Supprimer ce brouillon ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">Supprimer</button>
                                    </form>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-journal-text mb-2 d-block" style="font-size: 2rem; color: #9ca3af;"></i>
                        Aucune écriture trouvée pour ces critères.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($ecritures->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $ecritures->links() }}
</div>
@endif

@endsection
