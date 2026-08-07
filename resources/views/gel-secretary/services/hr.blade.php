@extends('layouts.gel-secretary')
@section('title', 'Ressources Humaines - Secrétariat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-users" style="color:#0EA5E9; margin-right:8px;"></i> Ressources Humaines
    </h1>
    <p class="sec-page-sub">
      @if($activeClient)
        Gestion du personnel et des congés pour : <strong>{{ $activeClient->nom_entreprise }}</strong>
      @else
        Veuillez sélectionner une entreprise active dans la barre supérieure.
      @endif
    </p>
  </div>
  @if($activeClient)
  <div style="display:flex; gap:10px;">
    <button class="sec-btn" style="background:white; border:1px solid var(--sec-border); color:var(--sec-text);" onclick="document.getElementById('employeeModal').style.display='flex'">
      <i class="fas fa-user-plus"></i> Nouvel employé
    </button>
    <button class="sec-btn sec-btn-primary" onclick="document.getElementById('leaveModal').style.display='flex'">
      <i class="fas fa-plane-departure"></i> Saisir un congé
    </button>
  </div>
  @endif
</div>

@if($activeClient)
<!-- Système d'onglets simples -->
<div style="margin-bottom:20px; border-bottom:1px solid var(--sec-border); display:flex; gap:20px;">
    <button class="tab-btn active" onclick="switchTab('tab-employees', this)" style="background:none; border:none; padding:10px 4px; font-size:14px; font-weight:600; color:var(--sec-primary); border-bottom:2px solid var(--sec-primary); cursor:pointer;">
        Employés ({{ $employees->count() }})
    </button>
    <button class="tab-btn" onclick="switchTab('tab-leaves', this)" style="background:none; border:none; padding:10px 4px; font-size:14px; font-weight:600; color:var(--sec-text-muted); cursor:pointer;">
        Demandes de congés ({{ $leaves->count() }})
    </button>
</div>

<!-- ONGLET EMPLOYÉS -->
<div id="tab-employees" class="tab-content" style="display:block;">
    <div class="sec-card">
        <div class="sec-card-header">
            <div class="sec-card-title">Liste du personnel</div>
        </div>
        <div class="sec-card-body" style="padding:0;">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Poste</th>
                        <th>Contact</th>
                        <th>Embauche</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td><strong>{{ $emp->matricule ?? '—' }}</strong></td>
                        <td>
                            <div style="font-weight:600; color:var(--sec-text);">{{ $emp->prenom }} {{ $emp->nom }}</div>
                            <div style="font-size:11px; color:var(--sec-text-muted);">{{ $emp->email ?? '' }}</div>
                        </td>
                        <td>
                            <div>{{ $emp->poste ?? 'Non spécifié' }}</div>
                            <span class="sec-badge sec-badge-info" style="font-size:10px;">{{ $emp->type_contrat ?? 'CDI' }}</span>
                        </td>
                        <td>{{ $emp->phone ?? '—' }}</td>
                        <td>{{ $emp->date_embauche ? $emp->date_embauche->format('d/m/Y') : '—' }}</td>
                        <td>
                            <button class="sec-btn" style="padding:4px 8px; font-size:11px; background:#F1F5F9; color:var(--sec-text);" title="Éditer" onclick="openEditEmployee({{ json_encode($emp) }})"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:var(--sec-text-muted);">
                            <i class="fas fa-user-times" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            Aucun employé trouvé pour cette entreprise.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ONGLET CONGÉS -->
<div id="tab-leaves" class="tab-content" style="display:none;">
    <div class="sec-card">
        <div class="sec-card-header">
            <div class="sec-card-title">Suivi des absences et congés</div>
        </div>
        <div class="sec-card-body" style="padding:0;">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Date demande</th>
                        <th>Employé</th>
                        <th>Type</th>
                        <th>Période</th>
                        <th>Durée</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->created_at->format('d/m/Y') }}</td>
                        <td><strong>{{ $leave->employee->prenom }} {{ $leave->employee->nom }}</strong></td>
                        <td>{{ ucfirst($leave->type) }}</td>
                        <td>
                            Du {{ \Carbon\Carbon::parse($leave->date_debut)->format('d/m') }}
                            au {{ \Carbon\Carbon::parse($leave->date_fin)->format('d/m') }}
                        </td>
                        <td>{{ $leave->duree_jours }} jour(s)</td>
                        <td>
                            @if($leave->statut === 'pending')
                                <span class="sec-badge sec-badge-warning">En attente</span>
                            @elseif($leave->statut === 'approved')
                                <span class="sec-badge sec-badge-success">Validé</span>
                            @elseif($leave->statut === 'rejected')
                                <span class="sec-badge sec-badge-danger">Refusé</span>
                            @else
                                <span class="sec-badge sec-badge-muted">{{ ucfirst($leave->statut) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($leave->statut === 'pending')
                            <div style="display:flex; gap:6px;">
                                <form action="{{ route('gel-secretary.services.hr.leave.status', $leave->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="statut" value="approved">
                                    <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#DCFCE7; color:#16A34A; border:1px solid #BBF7D0;" title="Approuver"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('gel-secretary.services.hr.leave.status', $leave->id) }}" method="POST" onsubmit="return confirm('Refuser ce congé ?');">
                                    @csrf
                                    <input type="hidden" name="statut" value="rejected">
                                    <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#FEE2E2; color:#DC2626; border:1px solid #FECACA;" title="Refuser"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                            @else
                                <span style="font-size:11px; color:var(--sec-text-muted);"><i class="fas fa-lock"></i> Traité</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px; color:var(--sec-text-muted);">
                            <i class="fas fa-calendar-check" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            Aucune demande de congé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Employé -->
<div id="employeeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:100%; max-width:600px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom:20px; font-size:18px;"><i class="fas fa-user-plus text-primary"></i> Ajouter un employé</h3>
        <form action="{{ route('gel-secretary.services.hr.employee.store') }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Nom *</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Matricule</label>
                    <input type="text" name="matricule" class="form-control" placeholder="Ex: MAT-001">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Téléphone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Poste</label>
                    <input type="text" name="poste" class="form-control" placeholder="Ex: Comptable">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Type de contrat</label>
                    <select name="type_contrat" class="form-select">
                        <option value="CDI">CDI</option>
                        <option value="CDD">CDD</option>
                        <option value="Stage">Stage</option>
                        <option value="Prestation">Prestation</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Date d'embauche</label>
                    <input type="date" name="date_embauche" class="form-control">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" style="background:#F1F5F9; color:#475569;" onclick="document.getElementById('employeeModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Demande de congé -->
<div id="leaveModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:100%; max-width:500px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom:20px; font-size:18px;"><i class="fas fa-plane-departure text-primary"></i> Saisir une demande</h3>
        <form action="{{ route('gel-secretary.services.hr.leave.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Employé *</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">-- Sélectionner un employé --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->prenom }} {{ $emp->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Type d'absence *</label>
                <select name="type" class="form-select" required>
                    <option value="conge">Congé payé</option>
                    <option value="maladie">Arrêt maladie</option>
                    <option value="maternite">Maternité / Paternité</option>
                    <option value="formation">Formation</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Date de début *</label>
                    <input type="date" name="date_debut" class="form-control" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Date de fin *</label>
                    <input type="date" name="date_fin" class="form-control" required>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Motif / Commentaire</label>
                <textarea name="motif" class="form-control" rows="3"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" style="background:#F1F5F9; color:#475569;" onclick="document.getElementById('leaveModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Enregistrer la demande</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edition Employé -->
<div id="editEmployeeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:100%; max-width:600px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom:20px; font-size:18px;"><i class="fas fa-user-edit text-primary"></i> Modifier un employé</h3>
        <form id="editEmployeeForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Prénom *</label>
                    <input type="text" name="prenom" id="edit_prenom" class="form-control" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Nom *</label>
                    <input type="text" name="nom" id="edit_nom" class="form-control" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Matricule</label>
                    <input type="text" name="matricule" id="edit_matricule" class="form-control">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Téléphone</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Poste</label>
                    <input type="text" name="poste" id="edit_poste" class="form-control">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Type de contrat</label>
                    <select name="type_contrat" id="edit_type_contrat" class="form-select">
                        <option value="CDI">CDI</option>
                        <option value="CDD">CDD</option>
                        <option value="Stage">Stage</option>
                        <option value="Prestation">Prestation</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Date d'embauche</label>
                    <input type="date" name="date_embauche" id="edit_date_embauche" class="form-control">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" style="background:#F1F5F9; color:#475569;" onclick="document.getElementById('editEmployeeModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditEmployee(emp) {
    document.getElementById('edit_prenom').value = emp.prenom || '';
    document.getElementById('edit_nom').value = emp.nom || '';
    document.getElementById('edit_matricule').value = emp.matricule || '';
    document.getElementById('edit_phone').value = emp.phone || '';
    document.getElementById('edit_poste').value = emp.poste || '';
    document.getElementById('edit_type_contrat').value = emp.type_contrat || 'CDI';
    
    if (emp.date_embauche) {
        // Date is returned as a string like "2026-08-07T00:00:00.000000Z" if casted to date
        document.getElementById('edit_date_embauche').value = emp.date_embauche.substring(0, 10);
    } else {
        document.getElementById('edit_date_embauche').value = '';
    }

    const form = document.getElementById('editEmployeeForm');
    form.action = `{{ url('gel-secretary/services/hr/employee') }}/${emp.id}`;

    document.getElementById('editEmployeeModal').style.display = 'flex';
}

function switchTab(tabId, btnElement) {
    // Masquer tous les contenus
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    // Réinitialiser le style des boutons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.color = 'var(--sec-text-muted)';
        btn.style.borderBottom = 'none';
        btn.classList.remove('active');
    });
    
    // Afficher le contenu ciblé
    document.getElementById(tabId).style.display = 'block';
    // Activer le bouton
    btnElement.style.color = 'var(--sec-primary)';
    btnElement.style.borderBottom = '2px solid var(--sec-primary)';
    btnElement.classList.add('active');
}
</script>
@endif

@endsection
