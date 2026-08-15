@extends('layouts.gel-accountant')

@section('title', 'Clôture et Exercices')

@push('styles')
<style>
/* ==========================================================================
   FISCAL YEARS - DESIGN
   ========================================================================== */
.years-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-top: 24px; }
.year-card {
    background: white; border: 1px solid var(--gel-border); border-radius: 12px;
    padding: 24px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); display: flex; flex-direction: column;
}
.year-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.year-title { font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px; }
.year-status { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.status-open { background: #ECFDF5; color: #10B981; border: 1px solid #A7F3D0; }
.status-closed { background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; }

.year-details { font-size: 13px; color: #475569; margin-bottom: 24px; flex: 1; }
.detail-item { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.detail-item i { width: 16px; text-align: center; color: #94A3B8; }

.checklist { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
.checklist-title { font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 12px; }
.check-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1E293B; margin-bottom: 8px; }
.check-item i { color: #10B981; }

.btn-close-year { background: #1E293B; color: white; border: none; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; width: 100%; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-close-year:hover { background: #0F172A; }

.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
.modal-content { background-color: #fefefe; margin: 10% auto; padding: 0; border: none; width: 500px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; }
.modal-header { background: #1E293B; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
.modal-header h2 { margin: 0; font-size: 18px; font-weight: 700; }
.close-modal { color: rgba(255,255,255,0.7); font-size: 24px; font-weight: bold; cursor: pointer; transition: color 0.2s; }
.close-modal:hover { color: white; }
.modal-body { padding: 24px; }
.modal-footer { background: #F8FAFC; padding: 16px 24px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 12px; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Exercices et Clôture</h1>
        <div class="gel-page-subtitle">Gérez vos exercices fiscaux, validez les vérifications et effectuez la clôture définitive.</div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="years-grid">
    @forelse($years as $year)
        <div class="year-card">
            <div class="year-header">
                <h2 class="year-title">
                    <i class="fas fa-folder-open" style="color:var(--gel-primary);"></i> Exercice {{ $year->year }}
                </h2>
                @if($year->status == 'open') <span class="year-status status-open">En cours</span>
                @else <span class="year-status status-closed">Clôturé</span>
                @endif
            </div>

            <div class="year-details">
                <div class="detail-item"><i class="far fa-calendar-alt"></i> Période : 01/01/{{ $year->year }} - 31/12/{{ $year->year }}</div>
                @if($year->status == 'closed')
                    <div class="detail-item"><i class="fas fa-lock"></i> Clôturé le : {{ \Carbon\Carbon::parse($year->closed_at)->format('d/m/Y') }}</div>
                    @if($year->closedBy)
                        <div class="detail-item"><i class="fas fa-user-check"></i> Par : {{ $year->closedBy->first_name }} {{ $year->closedBy->last_name }}</div>
                    @endif
                @endif
            </div>

            @if($year->status == 'open')
                <div class="checklist">
                    <div class="checklist-title">Vérifications avant clôture</div>
                    <div class="check-item"><i class="fas fa-check-circle"></i> Saisie de toutes les écritures</div>
                    <div class="check-item"><i class="fas fa-check-circle"></i> Rapprochement bancaire</div>
                    <div class="check-item"><i class="fas fa-check-circle"></i> Déclarations TVA finalisées</div>
                    <div class="check-item"><i class="fas fa-check-circle"></i> Écritures d'inventaire</div>
                </div>

                <button class="btn-close-year" onclick="openCloseModal('{{ $year->id }}', '{{ $year->year }}')">
                    <i class="fas fa-lock"></i> Clôturer l'exercice {{ $year->year }}
                </button>
            @else
                <div style="background:#F1F5F9; border-radius:8px; padding:16px; text-align:center; color:#64748B; font-size:13px; font-weight:600;">
                    <i class="fas fa-shield-alt" style="margin-bottom:8px; font-size:24px; display:block;"></i>
                    Période verrouillée. Aucune écriture ne peut être modifiée.
                </div>
            @endif
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border: 1px dashed #CBD5E1; border-radius: 12px;">
            <i class="fas fa-calendar-times" style="font-size: 48px; color: #94A3B8; margin-bottom: 16px;"></i>
            <h3 style="color:#1E293B; margin-bottom:8px;">Aucun exercice configuré</h3>
            <p style="color:#64748B;">L'expert-comptable doit initialiser un exercice pour commencer.</p>
        </div>
    @endforelse
</div>

<!-- Modal de clôture -->
<div id="closeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle" style="color:#FBBF24;"></i> Clôture d'exercice</h2>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <form id="closeForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <p style="color:#475569; font-size:14px; margin-bottom:20px;">
                    Vous êtes sur le point de clôturer définitivement l'exercice <strong id="modalYearNum"></strong>.
                </p>
                <div style="background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:16px; margin-bottom:20px;">
                    <ul style="color:#991B1B; font-size:13px; margin:0; padding-left:16px;">
                        <li style="margin-bottom:4px;">Toutes les écritures de cet exercice seront verrouillées (non modifiables/supprimables).</li>
                        <li style="margin-bottom:4px;">Le résultat de l'exercice sera calculé.</li>
                        <li>Les soldes des comptes de bilan seront automatiquement reportés en <strong>À Nouveaux</strong> sur l'exercice suivant.</li>
                    </ul>
                </div>
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    <input type="checkbox" name="confirm_close" id="confirm_close" required style="margin-top:4px;">
                    <label for="confirm_close" style="font-size:13px; font-weight:600; color:#1E293B;">
                        Je confirme avoir terminé toutes les vérifications et je souhaite clôturer cet exercice. Je comprends que cette action est irréversible sans intervention du Super Administrateur.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeModal()" style="font-weight:600;">Annuler</button>
                <button type="submit" class="btn btn-danger" style="background:#EF4444; border:none; font-weight:600;"><i class="fas fa-lock"></i> Confirmer la clôture</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCloseModal(id, year) {
        document.getElementById('modalYearNum').innerText = year;
        document.getElementById('closeForm').action = '/gel-accountant/fiscalite/exercices/' + id + '/cloture';
        document.getElementById('confirm_close').checked = false;
        document.getElementById('closeModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('closeModal').style.display = 'none';
    }

    window.onclick = function(event) {
        let modal = document.getElementById('closeModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
@endpush
@endsection
