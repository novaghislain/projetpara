@extends('layouts.gel-admin')

@section('title', 'Modifier le Rôle')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Modifier le Rôle</h1>
        <p class="admin-page-sub">Modifier les accès du rôle {{ $role->name }}.</p>
    </div>
    <a href="{{ route('gel-admin.team.roles.index') }}" class="admin-btn admin-btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form action="{{ route('gel-admin.team.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="form-label fw-bold">Nom du rôle</label>
                <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Permissions</label>
                <div class="row">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 bg-light p-3 h-100">
                                <div class="fw-bold mb-2 text-primary">{{ ucfirst($module) }}</div>
                                @foreach($modulePermissions as $perm)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}>
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

            <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save me-2"></i> Mettre à jour le rôle</button>
        </form>
    </div>
</div>
@endsection
