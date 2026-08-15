@extends('layouts.gel-secretary')
@section('title', 'Ressources Humaines — GEL Secrétariat')

@section('content')
<style>
  :root {
    --rh-primary: #0EA5E9;
    --rh-primary-dark: #0284C7;
    --rh-success: #10B981;
    --rh-warning: #F59E0B;
    --rh-danger: #EF4444;
    --rh-purple: #8B5CF6;
    --rh-card: #FFFFFF;
    --rh-border: #E2E8F0;
    --rh-text: #1E293B;
    --rh-muted: #64748B;
    --rh-bg: #F8FAFC;
  }

  .rh-tabs { display:flex; gap:4px; border-bottom:2px solid var(--rh-border); margin-bottom:24px; }
  .rh-tab-btn {
    background:none; border:none; padding:12px 20px; font-size:13px; font-weight:600;
    color:var(--rh-muted); cursor:pointer; border-bottom:2px solid transparent;
    margin-bottom:-2px; transition:all 0.2s; border-radius:8px 8px 0 0;
    display:flex; align-items:center; gap:8px;
  }
  .rh-tab-btn:hover { color:var(--rh-primary); background:#f0f9ff; }
  .rh-tab-btn.active { color:var(--rh-primary); border-bottom-color:var(--rh-primary); background:#f0f9ff; }
  .rh-tab-content { display:none; }
  .rh-tab-content.active { display:block; }

  .stat-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:16px; margin-bottom:24px; }
  .stat-card {
    background:white; border:1px solid var(--rh-border); border-radius:14px; padding:20px;
    display:flex; align-items:center; gap:16px; box-shadow:0 1px 3px rgba(0,0,0,0.04);
  }
  .stat-icon {
    width:48px; height:48px; border-radius:12px;
    display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;
  }
  .stat-num { font-size:26px; font-weight:800; color:var(--rh-text); line-height:1; }
  .stat-label { font-size:12px; color:var(--rh-muted); margin-top:3px; }

  .rh-card { background:white; border:1px solid var(--rh-border); border-radius:14px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
  .rh-card-header { padding:16px 20px; border-bottom:1px solid var(--rh-border); display:flex; align-items:center; justify-content:space-between; }
  .rh-card-title { font-size:15px; font-weight:700; color:var(--rh-text); }

  .emp-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px; padding:20px; }
  .emp-card {
    border:1px solid var(--rh-border); border-radius:12px; padding:16px;
    transition:all 0.2s; cursor:pointer; position:relative; background:#fff;
  }
  .emp-card:hover { border-color:var(--rh-primary); box-shadow:0 4px 16px rgba(14,165,233,0.12); transform:translateY(-2px); }
  .emp-avatar {
    width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#0EA5E9,#8B5CF6);
    display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700;
    color:white; margin-bottom:12px; flex-shrink:0;
  }
  .emp-name { font-size:15px; font-weight:700; color:var(--rh-text); }
  .emp-poste { font-size:12px; color:var(--rh-muted); margin-top:2px; }
  .emp-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; margin-top:8px;
  }
  .badge-actif { background:#D1FAE5; color:#065F46; }
  .badge-inactif { background:#FEE2E2; color:#991B1B; }
  .badge-suspendu { background:#FEF3C7; color:#92400E; }

  .badge-cdi { background:#DBEAFE; color:#1E40AF; }
  .badge-cdd { background:#E0E7FF; color:#3730A3; }
  .badge-stage { background:#F3E8FF; color:#6B21A8; }

  .status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
  .dot-pending { background:#F59E0B; }
  .dot-approved { background:#10B981; }
  .dot-rejected { background:#EF4444; }

  .leave-badge-pending { background:#FEF3C7; color:#92400E; }
  .leave-badge-approved { background:#D1FAE5; color:#065F46; }
  .leave-badge-rejected { background:#FEE2E2; color:#991B1B; }
  .leave-badge-cancelled { background:#F1F5F9; color:#64748B; }

  /* Modals */
  .rh-modal {
    display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6);
    z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);
  }
  .rh-modal-box {
    background:white; border-radius:20px; width:100%; max-width:680px; max-height:90vh;
    overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.2);
  }
  .rh-modal-header {
    padding:24px 28px 20px; border-bottom:1px solid var(--rh-border);
    display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:white; z-index:1;
  }
  .rh-modal-title { font-size:18px; font-weight:700; color:var(--rh-text); }
  .rh-modal-body { padding:24px 28px; }
  .rh-modal-footer { padding:16px 28px; border-top:1px solid var(--rh-border); display:flex; justify-content:flex-end; gap:10px; }

  .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .form-grid.full { grid-template-columns:1fr; }
  .form-group label { display:block; font-size:12px; font-weight:600; color:var(--rh-muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px; }
  .form-group input, .form-group select, .form-group textarea {
    width:100%; padding:10px 12px; border:1px solid var(--rh-border); border-radius:8px;
    font-size:14px; color:var(--rh-text); transition:border-color 0.2s; box-sizing:border-box;
    font-family:inherit;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline:none; border-color:var(--rh-primary); box-shadow:0 0 0 3px rgba(14,165,233,0.1);
  }
  .form-section { margin-bottom:20px; }
  .form-section-title {
    font-size:12px; font-weight:700; color:var(--rh-primary); text-transform:uppercase;
    letter-spacing:1px; margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid #e0f2fe;
  }
  .btn-rh {
    padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600;
    cursor:pointer; border:none; transition:all 0.2s; display:inline-flex; align-items:center; gap:8px;
  }
  .btn-primary { background:var(--rh-primary); color:white; }
  .btn-primary:hover { background:var(--rh-primary-dark); }
  .btn-secondary { background:var(--rh-bg); color:var(--rh-text); border:1px solid var(--rh-border); }
  .btn-secondary:hover { background:#E2E8F0; }
  .btn-danger { background:#FEE2E2; color:#991B1B; }
  .btn-danger:hover { background:#FECACA; }

  .rh-table { width:100%; border-collapse:collapse; }
  .rh-table th { padding:12px 16px; text-align:left; font-size:11px; font-weight:700; color:var(--rh-muted); text-transform:uppercase; letter-spacing:0.5px; background:#F8FAFC; border-bottom:1px solid var(--rh-border); }
  .rh-table td { padding:14px 16px; border-bottom:1px solid #F1F5F9; font-size:13px; color:var(--rh-text); vertical-align:middle; }
  .rh-table tr:hover td { background:#F8FAFC; }
  .rh-table tr:last-child td { border-bottom:none; }

  .empty-state { text-align:center; padding:60px 40px; color:var(--rh-muted); }
  .empty-icon { font-size:52px; color:#CBD5E1; margin-bottom:16px; }
  .empty-title { font-size:18px; font-weight:700; color:var(--rh-text); margin-bottom:8px; }
  .empty-text { font-size:14px; max-width:360px; margin:0 auto 24px; line-height:1.6; }

  .search-bar {
    display:flex; align-items:center; gap:8px; padding:8px 14px;
    border:1px solid var(--rh-border); border-radius:8px; background:white;
  }
  .search-bar input { border:none; outline:none; font-size:13px; color:var(--rh-text); flex:1; }
  .search-bar i { color:#94A3B8; }
</style>

<!-- ── Header ─────────────────────────────────────────────── -->
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-users" style="color:#0EA5E9; margin-right:8px;"></i> Ressources Humaines
    </h1>
    <p class="sec-page-sub">
      @if($activeClient)
        Gestion du personnel pour : <strong>{{ $activeClient->nom_entreprise }}</strong>
      @else
        Veuillez sélectionner une entreprise dans la barre supérieure.
      @endif
    </p>
  </div>
  @if($activeClient)
  <div style="display:flex; gap:10px;">
    <button class="btn-rh btn-secondary" onclick="openModal('leaveModal')">
      <i class="fas fa-plane-departure"></i> Congé / Absence
    </button>
    <button class="btn-rh btn-primary" onclick="openModal('employeeModal')">
      <i class="fas fa-user-plus"></i> Nouvel employé
    </button>
  </div>
  @endif
</div>

@if(session('success'))
<div style="background:#D1FAE5; border:1px solid #6EE7B7; color:#065F46; padding:12px 18px; border-radius:10px; margin-bottom:16px; font-size:14px; font-weight:600;">
  <i class="fas fa-check-circle" style="margin-right:8px;"></i> {{ session('success') }}
</div>
@endif

@if(!$activeClient)
<div class="rh-card" style="text-align:center; padding:60px;">
  <i class="fas fa-building" style="font-size:48px; color:#CBD5E1; display:block; margin-bottom:16px;"></i>
  <h2 style="font-size:20px; font-weight:700; color:var(--rh-text); margin-bottom:8px;">Aucune entreprise sélectionnée</h2>
  <p style="color:var(--rh-muted); font-size:14px;">Sélectionnez une entreprise dans le menu déroulant en haut pour gérer son personnel.</p>
</div>
@else

<!-- ── Statistiques ─────────────────────────────────────── -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#DBEAFE; color:#1D4ED8;"><i class="fas fa-users"></i></div>
    <div>
      <div class="stat-num">{{ $employees->count() }}</div>
      <div class="stat-label">Employés actifs</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#FEF3C7; color:#D97706;"><i class="fas fa-clock"></i></div>
    <div>
      <div class="stat-num">{{ $leaves->where('statut', 'pending')->count() }}</div>
      <div class="stat-label">Congés en attente</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#D1FAE5; color:#059669;"><i class="fas fa-check-circle"></i></div>
    <div>
      <div class="stat-num">{{ $leaves->where('statut', 'approved')->count() }}</div>
      <div class="stat-label">Congés approuvés</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#F3E8FF; color:#7C3AED;"><i class="fas fa-file-contract"></i></div>
    <div>
      <div class="stat-num">{{ $employees->where('type_contrat', 'CDI')->count() }}</div>
      <div class="stat-label">CDI</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#FEE2E2; color:#DC2626;"><i class="fas fa-user-slash"></i></div>
    <div>
      <div class="stat-num">{{ $employees->where('status', 'inactif')->count() }}</div>
      <div class="stat-label">Inactifs / Partis</div>
    </div>
  </div>
</div>

<!-- ── Onglets ──────────────────────────────────────────── -->
<div class="rh-tabs">
  <button class="rh-tab-btn active" onclick="switchTab('tab-employees', this)">
    <i class="fas fa-id-card"></i> Employés ({{ $employees->count() }})
  </button>
  <button class="rh-tab-btn" onclick="switchTab('tab-leaves', this)">
    <i class="fas fa-plane-departure"></i> Congés & Absences ({{ $leaves->count() }})
  </button>
</div>

<!-- ═══════════ ONGLET EMPLOYÉS ═══════════ -->
<div id="tab-employees" class="rh-tab-content active">
  <div class="rh-card">
    <div class="rh-card-header">
      <span class="rh-card-title">Fiche personnel</span>
      <div style="display:flex; gap:10px; align-items:center;">
        <div class="search-bar" style="min-width:240px;">
          <i class="fas fa-search"></i>
          <input type="text" id="empSearch" onkeyup="filterEmployees()" placeholder="Rechercher un employé…">
        </div>
        <select id="empFilter" onchange="filterEmployees()" style="padding:8px 12px; border:1px solid var(--rh-border); border-radius:8px; font-size:13px; color:var(--rh-text);">
          <option value="">Tous les statuts</option>
          <option value="actif">Actifs</option>
          <option value="inactif">Inactifs</option>
          <option value="suspendu">Suspendus</option>
        </select>
      </div>
    </div>

    @if($employees->isEmpty())
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-users"></i></div>
        <div class="empty-title">Aucun employé enregistré</div>
        <div class="empty-text">Commencez par ajouter votre premier employé pour cette entreprise.</div>
        <button class="btn-rh btn-primary" onclick="openModal('employeeModal')">
          <i class="fas fa-user-plus"></i> Ajouter un employé
        </button>
      </div>
    @else
      <div class="emp-grid" id="employeeGrid">
        @foreach($employees as $emp)
        <div class="emp-card employee-item"
             data-name="{{ strtolower($emp->nom . ' ' . $emp->prenom) }}"
             data-status="{{ $emp->status }}">
          <div style="display:flex; align-items:flex-start; gap:14px;">
            <div class="emp-avatar">{{ strtoupper(substr($emp->prenom, 0, 1)) }}{{ strtoupper(substr($emp->nom, 0, 1)) }}</div>
            <div style="flex:1; min-width:0;">
              <div class="emp-name">{{ $emp->prenom }} {{ $emp->nom }}</div>
              <div class="emp-poste">{{ $emp->poste ?: 'Poste non défini' }}</div>
              @if($emp->departement)
                <div class="emp-poste" style="margin-top:2px;"><i class="fas fa-sitemap" style="width:14px; color:#94A3B8;"></i> {{ $emp->departement }}</div>
              @endif
              <div style="display:flex; gap:6px; margin-top:8px; flex-wrap:wrap;">
                <span class="emp-badge {{ $emp->status === 'actif' ? 'badge-actif' : ($emp->status === 'suspendu' ? 'badge-suspendu' : 'badge-inactif') }}">
                  {{ ucfirst($emp->status) }}
                </span>
                @if($emp->type_contrat)
                <span class="emp-badge {{ strtolower($emp->type_contrat) === 'cdi' ? 'badge-cdi' : (strtolower($emp->type_contrat) === 'cdd' ? 'badge-cdd' : 'badge-stage') }}">
                  {{ strtoupper($emp->type_contrat) }}
                </span>
                @endif
              </div>
            </div>
          </div>
          <div style="margin-top:14px; padding-top:12px; border-top:1px solid #F1F5F9; display:flex; gap:12px; font-size:12px; color:var(--rh-muted);">
            @if($emp->phone)
              <span><i class="fas fa-phone" style="margin-right:4px;"></i>{{ $emp->phone }}</span>
            @endif
            @if($emp->date_embauche)
              <span><i class="fas fa-calendar" style="margin-right:4px;"></i>{{ $emp->date_embauche->format('d/m/Y') }}</span>
            @endif
          </div>
          <div style="display:flex; gap:8px; margin-top:12px;">
            <button class="btn-rh btn-secondary" style="flex:1; justify-content:center; font-size:12px; padding:8px;"
                    onclick="openEditEmployee({{ json_encode($emp) }})">
              <i class="fas fa-edit"></i> Modifier
            </button>
            <a href="{{ route('gel-secretary.services.hr.leave') }}" class="btn-rh btn-secondary" style="flex:1; justify-content:center; font-size:12px; padding:8px; text-decoration:none;"
               onclick="event.preventDefault(); prefillLeave('{{ $emp->id }}', '{{ $emp->prenom }} {{ $emp->nom }}')">
              <i class="fas fa-plane"></i> Congé
            </a>
          </div>
        </div>
        @endforeach
      </div>
    @endif
  </div>
</div>

<!-- ═══════════ ONGLET CONGÉS ═══════════ -->
<div id="tab-leaves" class="rh-tab-content">
  <div class="rh-card">
    <div class="rh-card-header">
      <span class="rh-card-title">Demandes de congé & absences</span>
      <div style="display:flex; gap:10px;">
        <select id="leaveFilter" onchange="filterLeaves()" style="padding:8px 12px; border:1px solid var(--rh-border); border-radius:8px; font-size:13px;">
          <option value="">Tous les statuts</option>
          <option value="pending">En attente</option>
          <option value="approved">Approuvés</option>
          <option value="rejected">Refusés</option>
          <option value="cancelled">Annulés</option>
        </select>
      </div>
    </div>
    @if($leaves->isEmpty())
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
        <div class="empty-title">Aucune demande de congé</div>
        <div class="empty-text">Saisissez les demandes de congé et absences de votre personnel.</div>
        <button class="btn-rh btn-primary" onclick="openModal('leaveModal')">
          <i class="fas fa-plus"></i> Saisir un congé
        </button>
      </div>
    @else
      <div style="overflow-x:auto;">
        <table class="rh-table">
          <thead>
            <tr>
              <th>Employé</th>
              <th>Type</th>
              <th>Période</th>
              <th>Durée</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="leavesBody">
            @foreach($leaves as $leave)
            <tr class="leave-item" data-status="{{ $leave->statut }}">
              <td>
                <div style="font-weight:600;">{{ $leave->employee?->prenom }} {{ $leave->employee?->nom }}</div>
                <div style="font-size:11px; color:var(--rh-muted);">{{ $leave->employee?->poste }}</div>
              </td>
              <td>
                @php
                  $typeLabels = ['conge'=>'Congé annuel','maladie'=>'Maladie','maternite'=>'Maternité','paternite'=>'Paternité','formation'=>'Formation','autre'=>'Autre'];
                @endphp
                <span style="font-weight:500;">{{ $typeLabels[$leave->type] ?? ucfirst($leave->type) }}</span>
              </td>
              <td>
                <div style="font-size:13px;">{{ $leave->date_debut->format('d/m/Y') }}</div>
                <div style="font-size:11px; color:var(--rh-muted);">→ {{ $leave->date_fin->format('d/m/Y') }}</div>
              </td>
              <td>
                <span style="font-weight:600; color:var(--rh-primary);">{{ $leave->duree_jours }}j</span>
              </td>
              <td>
                @php
                  $statusClass = ['pending'=>'leave-badge-pending','approved'=>'leave-badge-approved','rejected'=>'leave-badge-rejected','cancelled'=>'leave-badge-cancelled'];
                  $statusLabel = ['pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Refusé','cancelled'=>'Annulé'];
                @endphp
                <span class="emp-badge {{ $statusClass[$leave->statut] ?? '' }}">
                  <span class="status-dot dot-{{ $leave->statut }}"></span>
                  {{ $statusLabel[$leave->statut] ?? $leave->statut }}
                </span>
              </td>
              <td>
                @if($leave->statut === 'pending')
                <div style="display:flex; gap:6px;">
                  <form method="POST" action="{{ route('gel-secretary.services.hr.leave.status', $leave->id) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="statut" value="approved">
                    <button type="submit" class="btn-rh btn-secondary" style="padding:6px 12px; font-size:12px; color:#059669;">
                      <i class="fas fa-check"></i>
                    </button>
                  </form>
                  <form method="POST" action="{{ route('gel-secretary.services.hr.leave.status', $leave->id) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="statut" value="rejected">
                    <button type="submit" class="btn-rh btn-danger" style="padding:6px 12px; font-size:12px;">
                      <i class="fas fa-times"></i>
                    </button>
                  </form>
                </div>
                @else
                  <span style="font-size:12px; color:var(--rh-muted);">—</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endif

<!-- ═══════════ MODAL NOUVEL EMPLOYÉ ═══════════ -->
<div id="employeeModal" class="rh-modal">
  <div class="rh-modal-box">
    <div class="rh-modal-header">
      <div class="rh-modal-title"><i class="fas fa-user-plus" style="color:var(--rh-primary); margin-right:10px;"></i>Nouvel employé</div>
      <button onclick="closeModal('employeeModal')" style="background:none; border:none; font-size:22px; color:#94A3B8; cursor:pointer;">&times;</button>
    </div>
    <form method="POST" action="{{ route('gel-secretary.services.hr.employee.store') }}" id="employeeForm">
      @csrf
      <div class="rh-modal-body">

        <div class="form-section">
          <div class="form-section-title"><i class="fas fa-user"></i> Identité</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Civilité</label>
              <select name="civilite">
                <option value="">—</option>
                <option value="M.">M.</option>
                <option value="Mme">Mme</option>
              </select>
            </div>
            <div class="form-group">
              <label>Matricule</label>
              <input type="text" name="matricule" placeholder="EMP-001">
            </div>
            <div class="form-group">
              <label>Nom *</label>
              <input type="text" name="nom" required placeholder="DUPONT">
            </div>
            <div class="form-group">
              <label>Prénom *</label>
              <input type="text" name="prenom" required placeholder="Jean">
            </div>
            <div class="form-group">
              <label>Date de naissance</label>
              <input type="date" name="date_naissance">
            </div>
            <div class="form-group">
              <label>Nationalité</label>
              <input type="text" name="nationalite" placeholder="Béninoise">
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title"><i class="fas fa-briefcase"></i> Poste & Contrat</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Poste / Fonction</label>
              <input type="text" name="poste" placeholder="Comptable">
            </div>
            <div class="form-group">
              <label>Département</label>
              <input type="text" name="departement" placeholder="Finances">
            </div>
            <div class="form-group">
              <label>Type de contrat</label>
              <select name="type_contrat">
                <option value="">— Choisir —</option>
                <option value="CDI">CDI</option>
                <option value="CDD">CDD</option>
                <option value="Stage">Stage</option>
                <option value="Intérim">Intérim</option>
                <option value="Consultant">Consultant</option>
              </select>
            </div>
            <div class="form-group">
              <label>Date d'embauche</label>
              <input type="date" name="date_embauche">
            </div>
            <div class="form-group">
              <label>Salaire de base (FCFA)</label>
              <input type="number" name="salaire_base" placeholder="150000" step="500">
            </div>
            <div class="form-group">
              <label>Statut</label>
              <select name="status">
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
                <option value="suspendu">Suspendu</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title"><i class="fas fa-phone"></i> Contact</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="jean.dupont@example.com">
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <input type="text" name="phone" placeholder="+229 97 00 00 00">
            </div>
            <div class="form-group form-grid full">
              <label>Adresse</label>
              <input type="text" name="adresse" placeholder="Cotonou, Bénin">
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-title"><i class="fas fa-id-badge"></i> Numéros légaux & Banque</div>
          <div class="form-grid">
            <div class="form-group">
              <label>N° CNSS</label>
              <input type="text" name="cnss_number">
            </div>
            <div class="form-group">
              <label>N° IFU</label>
              <input type="text" name="ifu_number">
            </div>
            <div class="form-group">
              <label>Banque</label>
              <input type="text" name="banque">
            </div>
            <div class="form-group">
              <label>IBAN / RIB</label>
              <input type="text" name="iban">
            </div>
          </div>
        </div>

      </div>
      <div class="rh-modal-footer">
        <button type="button" class="btn-rh btn-secondary" onclick="closeModal('employeeModal')">Annuler</button>
        <button type="submit" class="btn-rh btn-primary"><i class="fas fa-save"></i> Enregistrer l'employé</button>
      </div>
    </form>
  </div>
</div>

<!-- ═══════════ MODAL MODIFIER EMPLOYÉ ═══════════ -->
<div id="editEmployeeModal" class="rh-modal">
  <div class="rh-modal-box">
    <div class="rh-modal-header">
      <div class="rh-modal-title"><i class="fas fa-user-edit" style="color:var(--rh-primary); margin-right:10px;"></i>Modifier l'employé</div>
      <button onclick="closeModal('editEmployeeModal')" style="background:none; border:none; font-size:22px; color:#94A3B8; cursor:pointer;">&times;</button>
    </div>
    <form method="POST" id="editEmployeeForm">
      @csrf @method('PUT')
      <div class="rh-modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Civilité</label>
            <select name="civilite" id="edit_civilite">
              <option value="">—</option>
              <option value="M.">M.</option>
              <option value="Mme">Mme</option>
            </select>
          </div>
          <div class="form-group">
            <label>Matricule</label>
            <input type="text" name="matricule" id="edit_matricule">
          </div>
          <div class="form-group">
            <label>Nom *</label>
            <input type="text" name="nom" id="edit_nom" required>
          </div>
          <div class="form-group">
            <label>Prénom *</label>
            <input type="text" name="prenom" id="edit_prenom" required>
          </div>
          <div class="form-group">
            <label>Poste</label>
            <input type="text" name="poste" id="edit_poste">
          </div>
          <div class="form-group">
            <label>Département</label>
            <input type="text" name="departement" id="edit_departement">
          </div>
          <div class="form-group">
            <label>Type de contrat</label>
            <select name="type_contrat" id="edit_type_contrat">
              <option value="">—</option>
              <option value="CDI">CDI</option>
              <option value="CDD">CDD</option>
              <option value="Stage">Stage</option>
              <option value="Intérim">Intérim</option>
              <option value="Consultant">Consultant</option>
            </select>
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select name="status" id="edit_status">
              <option value="actif">Actif</option>
              <option value="inactif">Inactif</option>
              <option value="suspendu">Suspendu</option>
            </select>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="edit_email">
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="phone" id="edit_phone">
          </div>
          <div class="form-group">
            <label>Salaire de base (FCFA)</label>
            <input type="number" name="salaire_base" id="edit_salaire_base" step="500">
          </div>
          <div class="form-group">
            <label>Date d'embauche</label>
            <input type="date" name="date_embauche" id="edit_date_embauche">
          </div>
        </div>
      </div>
      <div class="rh-modal-footer">
        <button type="button" class="btn-rh btn-secondary" onclick="closeModal('editEmployeeModal')">Annuler</button>
        <button type="submit" class="btn-rh btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
      </div>
    </form>
  </div>
</div>

<!-- ═══════════ MODAL CONGÉ ═══════════ -->
<div id="leaveModal" class="rh-modal">
  <div class="rh-modal-box" style="max-width:520px;">
    <div class="rh-modal-header">
      <div class="rh-modal-title"><i class="fas fa-plane-departure" style="color:#8B5CF6; margin-right:10px;"></i>Demande de congé</div>
      <button onclick="closeModal('leaveModal')" style="background:none; border:none; font-size:22px; color:#94A3B8; cursor:pointer;">&times;</button>
    </div>
    <form method="POST" action="{{ route('gel-secretary.services.hr.leave.store') }}">
      @csrf
      <div class="rh-modal-body">
        <div style="display:grid; gap:16px;">
          <div class="form-group">
            <label>Employé *</label>
            <select name="employee_id" id="leaveEmployeeSelect" required>
              <option value="">— Sélectionner un employé —</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->prenom }} {{ $emp->nom }} @if($emp->poste)— {{ $emp->poste }}@endif</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Type d'absence *</label>
            <select name="type" required>
              <option value="conge">Congé annuel</option>
              <option value="maladie">Maladie</option>
              <option value="maternite">Maternité</option>
              <option value="paternite">Paternité</option>
              <option value="formation">Formation</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label>Date de début *</label>
              <input type="date" name="date_debut" required>
            </div>
            <div class="form-group">
              <label>Date de fin *</label>
              <input type="date" name="date_fin" required>
            </div>
          </div>
          <div class="form-group">
            <label>Motif / Remarques</label>
            <textarea name="motif" rows="3" placeholder="Motif de l'absence…" style="resize:vertical;"></textarea>
          </div>
        </div>
      </div>
      <div class="rh-modal-footer">
        <button type="button" class="btn-rh btn-secondary" onclick="closeModal('leaveModal')">Annuler</button>
        <button type="submit" class="btn-rh btn-primary"><i class="fas fa-paper-plane"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  const m = document.getElementById(id);
  if(m) { m.style.display='flex'; document.body.style.overflow='hidden'; }
}
function closeModal(id) {
  const m = document.getElementById(id);
  if(m) { m.style.display='none'; document.body.style.overflow=''; }
}

// Close on backdrop click
document.querySelectorAll('.rh-modal').forEach(m => {
  m.addEventListener('click', function(e) {
    if(e.target === m) closeModal(m.id);
  });
});

// Tab switching
function switchTab(tabId, btn) {
  document.querySelectorAll('.rh-tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.rh-tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(tabId).classList.add('active');
  btn.classList.add('active');
}

// Filter employees
function filterEmployees() {
  const search = document.getElementById('empSearch').value.toLowerCase();
  const status = document.getElementById('empFilter').value;
  document.querySelectorAll('.employee-item').forEach(card => {
    const nameMatch = card.dataset.name.includes(search);
    const statusMatch = !status || card.dataset.status === status;
    card.style.display = nameMatch && statusMatch ? '' : 'none';
  });
}

// Filter leaves
function filterLeaves() {
  const status = document.getElementById('leaveFilter').value;
  document.querySelectorAll('.leave-item').forEach(row => {
    row.style.display = !status || row.dataset.status === status ? '' : 'none';
  });
}

// Edit employee
function openEditEmployee(emp) {
  document.getElementById('edit_nom').value = emp.nom || '';
  document.getElementById('edit_prenom').value = emp.prenom || '';
  document.getElementById('edit_matricule').value = emp.matricule || '';
  document.getElementById('edit_civilite').value = emp.civilite || '';
  document.getElementById('edit_poste').value = emp.poste || '';
  document.getElementById('edit_departement').value = emp.departement || '';
  document.getElementById('edit_type_contrat').value = emp.type_contrat || '';
  document.getElementById('edit_status').value = emp.status || 'actif';
  document.getElementById('edit_email').value = emp.email || '';
  document.getElementById('edit_phone').value = emp.phone || '';
  document.getElementById('edit_salaire_base').value = emp.salaire_base || '';
  document.getElementById('edit_date_embauche').value = emp.date_embauche || '';
  document.getElementById('editEmployeeForm').action = `/gel-secretary/services/hr/employee/${emp.id}`;
  openModal('editEmployeeModal');
}

// Prefill leave with employee
function prefillLeave(empId, empName) {
  const sel = document.getElementById('leaveEmployeeSelect');
  if(sel) sel.value = empId;
  openModal('leaveModal');
}
</script>
@endsection
