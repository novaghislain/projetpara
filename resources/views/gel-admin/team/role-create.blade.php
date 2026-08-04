@extends('layouts.gel-admin')

@section('title', 'Créer un Rôle')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Créer un Rôle</h1>
        <p class="admin-page-sub">Définissez un nouveau profil d'accès personnalisé.</p>
    </div>
    <a href="{{ route('gel-admin.team.roles.index') }}" class="admin-btn admin-btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="{{ route('gel-admin.team.roles.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-bold">Nom du rôle</label>
                <input type="text" name="name" class="form-control" placeholder="Ex: Comptable Senior" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Permissions</label>
                <p class="text-muted small">Cochez les permissions à attribuer à ce rôle.</p>
                <div class="row">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 bg-light p-3 h-100">
                                <div class="fw-bold mb-2 text-primary">{{ ucfirst($module) }}</div>
                                @foreach($modulePermissions as $perm)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}">
                                        <label class="form-check-label" for="perm_{{ $perm->id }}">
                                            {{ $perm->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save me-2"></i> Enregistrer le rôle</button>
        </form>
    </div>
</div>
@endsection
