@extends('layouts.gel-informaticien')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Gestion des Utilisateurs</h1>
    <div class="sec-page-sub">Gérez les comptes, rôles et accès de tout le personnel.</div>
  </div>
  <button class="sec-btn sec-btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
    <i class="fa-solid fa-user-plus"></i>
    Nouvel Utilisateur
  </button>
</div>

@if(session('success'))
<div class="alert alert-success bg-white border-0 border-start border-success border-4 shadow-sm py-2 px-3 mb-4 d-flex align-items-center" style="font-size: 13px;">
    <i class="fa-solid fa-circle-check text-success me-2 fs-5"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger bg-white border-0 border-start border-danger border-4 shadow-sm py-2 px-3 mb-4 d-flex align-items-center" style="font-size: 13px;">
    <i class="fa-solid fa-circle-exclamation text-danger me-2 fs-5"></i>
    {{ session('error') }}
</div>
@endif

<div class="sec-card mb-4">
  <div class="sec-card-header">
    <div class="sec-card-title">Liste des Utilisateurs</div>
  </div>
  <div class="sec-card-body p-0">
    <div class="table-responsive">
      <table class="sec-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Type de Compte</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="sec-initials">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <span class="fw-bold">{{ $user->name }}</span>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td>
              <span class="sec-badge sec-badge-info">{{ ucfirst($user->role) }}</span>
            </td>
            <td>{{ ucfirst($user->account_type) }}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-light border me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Modifier">
                <i class="fa-solid fa-pen text-muted"></i>
              </button>
              @if($user->id !== auth()->id())
              <form action="{{ route('gel-informaticien.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-light border text-danger" title="Supprimer">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
              @endif
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                  <h5 class="modal-title fs-5 fw-bold text-dark">Modifier l'utilisateur</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="{{ route('gel-informaticien.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="sec-form-group mb-3">
                      <label>Nom complet</label>
                      <input type="text" name="name" class="sec-form-control" value="{{ $user->name }}" required>
                    </div>
                    <div class="sec-form-group mb-3">
                      <label>Adresse Email</label>
                      <input type="email" name="email" class="sec-form-control" value="{{ $user->email }}" required>
                    </div>
                    <div class="sec-form-group mb-3">
                      <label>Mot de passe (laisser vide si inchangé)</label>
                      <input type="password" name="password" class="sec-form-control" placeholder="Nouveau mot de passe">
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <div class="sec-form-group mb-3">
                          <label>Type de compte</label>
                          <select name="account_type" class="sec-form-select" required>
                            <option value="informaticien" {{ $user->account_type == 'informaticien' ? 'selected' : '' }}>Informaticien (Super Admin)</option>
                            <option value="communication" {{ $user->account_type == 'communication' ? 'selected' : '' }}>Communication & Média</option>
                            <option value="comptable" {{ $user->account_type == 'comptable' ? 'selected' : '' }}>Comptable</option>
                            <option value="secretaire" {{ $user->account_type == 'secretaire' ? 'selected' : '' }}>Secrétaire</option>
                            <option value="client" {{ $user->account_type == 'client' ? 'selected' : '' }}>Client</option>
                            <option value="staff" {{ $user->account_type == 'staff' ? 'selected' : '' }}>Staff</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="sec-form-group mb-3">
                          <label>Rôle</label>
                          <select name="role" class="sec-form-select" required>
                            <option value="informaticien" {{ $user->role == 'informaticien' ? 'selected' : '' }}>Informaticien</option>
                            <option value="communication" {{ $user->role == 'communication' ? 'selected' : '' }}>Communication</option>
                            <option value="comptable" {{ $user->role == 'comptable' ? 'selected' : '' }}>Comptable</option>
                            <option value="secretaire" {{ $user->role == 'secretaire' ? 'selected' : '' }}>Secrétaire</option>
                            <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="collaborator" {{ $user->role == 'collaborator' ? 'selected' : '' }}>Collaborator</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="mt-4 text-end">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                      <button type="submit" class="sec-btn sec-btn-primary ms-2">Enregistrer</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fs-5 fw-bold text-dark">Nouvel Utilisateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('gel-informaticien.users.store') }}" method="POST">
          @csrf
          <div class="sec-form-group mb-3">
            <label>Nom complet</label>
            <input type="text" name="name" class="sec-form-control" required>
          </div>
          <div class="sec-form-group mb-3">
            <label>Adresse Email</label>
            <input type="email" name="email" class="sec-form-control" required>
          </div>
          <div class="sec-form-group mb-3">
            <label>Mot de passe</label>
            <input type="password" name="password" class="sec-form-control" required minlength="8">
          </div>
          <div class="row">
            <div class="col-6">
              <div class="sec-form-group mb-3">
                <label>Type de compte</label>
                <select name="account_type" class="sec-form-select" required>
                  <option value="informaticien">Informaticien (Super Admin)</option>
                  <option value="communication">Communication & Média</option>
                  <option value="comptable">Comptable</option>
                  <option value="secretaire">Secrétaire</option>
                  <option value="client">Client</option>
                  <option value="staff">Staff</option>
                </select>
              </div>
            </div>
            <div class="col-6">
              <div class="sec-form-group mb-3">
                <label>Rôle</label>
                <select name="role" class="sec-form-select" required>
                  <option value="informaticien">Informaticien</option>
                  <option value="communication">Communication</option>
                  <option value="comptable">Comptable</option>
                  <option value="secretaire">Secrétaire</option>
                  <option value="super_admin">Super Admin</option>
                  <option value="collaborator">Collaborator</option>
                </select>
              </div>
            </div>
          </div>
          <div class="mt-4 text-end">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="sec-btn sec-btn-primary ms-2">Créer l'utilisateur</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
