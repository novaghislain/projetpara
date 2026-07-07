@extends('layouts.gel')

@section('title', 'Mon Cabinet - GEL Cabinet')

@section('styles')
<style>
    .settings-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gel-border);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .settings-card .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gel-border);
        background: #fafbfc;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .settings-card .card-body { padding: 1.5rem; }
    .module-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--gel-border);
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }
    .module-item:hover { border-color: var(--gel-accent-2); }
    .module-item .module-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .module-switch {
        width: 44px;
        height: 24px;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }
    .module-switch.active { background: var(--gel-accent-2); }
    .module-switch.inactive { background: #d1d5db; }
    .module-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        transition: transform 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .module-switch.active::after { transform: translateX(20px); }
    .logo-preview {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid var(--gel-border);
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title">Mon Cabinet</h1>
        <p class="page-subtitle">Gérez les informations et la configuration de votre cabinet</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        {{-- Informations générales --}}
        <div class="settings-card">
            <div class="card-header">
                <span><i class="bi bi-building me-1"></i>Informations générales</span>
                <span class="badge bg-{{ $cabinet->is_active ? 'success' : 'secondary' }}">{{ $cabinet->is_active ? 'Actif' : 'Inactif' }}</span>
            </div>
            <div class="card-body">
                <form id="cabinetForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw500">Nom du cabinet</label>
                            <input type="text" class="form-control" id="cabinetNom" value="{{ $cabinet->nom }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Email</label>
                            <input type="email" class="form-control" id="cabinetEmail" value="{{ $cabinet->email }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Téléphone</label>
                            <input type="tel" class="form-control" id="cabinetTelephone" value="{{ $cabinet->telephone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Site web</label>
                            <input type="url" class="form-control" id="cabinetSite" value="{{ $cabinet->site_web }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw500">IFU</label>
                            <input type="text" class="form-control" id="cabinetIfu" value="{{ $cabinet->ifu }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw500">RCCM</label>
                            <input type="text" class="form-control" id="cabinetRccm" value="{{ $cabinet->rccm }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw500">Ville</label>
                            <input type="text" class="form-control" id="cabinetVille" value="{{ $cabinet->ville }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw500">Adresse</label>
                            <input type="text" class="form-control" id="cabinetAdresse" value="{{ $cabinet->adresse }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw500">Description</label>
                            <textarea class="form-control" id="cabinetDescription" rows="3">{{ $cabinet->description }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw500">Logo</label>
                            <input type="file" class="form-control" id="cabinetLogo" accept="image/png,image/jpeg,image/webp">
                            @if($cabinet->logo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$cabinet->logo) }}" alt="Logo" class="logo-preview">
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" style="background:var(--gel-accent-2);border:none;">
                            <i class="bi bi-check-circle me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
                <div id="cabinetResult" class="mt-2"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        {{-- Modules actifs --}}
        <div class="settings-card">
            <div class="card-header">
                <span><i class="bi bi-puzzle me-1"></i>Modules</span>
                <span class="badge" style="background:var(--gel-accent-2);">{{ $modules->count() }} modules</span>
            </div>
            <div class="card-body" id="modulesContainer">
                @forelse($modules as $module)
                <div class="module-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="module-icon" style="background:rgba(99,91,255,0.1);color:var(--gel-accent-2);">
                            <i class="bi {{ $module->icone ?? 'bi-box' }}"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;">{{ $module->label ?? $module->module }}</div>
                            <div style="font-size:0.75rem;color:var(--gel-text-muted);">
                                @if($module->date_activation)
                                Activé le {{ $module->date_activation->format('d/m/Y') }}
                                @endif
                                @if($module->max_users)
                                &bull; {{ $module->max_users }} utilisateurs max
                                @endif
                            </div>
                        </div>
                    </div>
                    <button class="module-switch {{ $module->is_active ? 'active' : 'inactive' }}"
                            onclick="toggleModule({{ $module->id }}, this)"
                            title="{{ $module->is_active ? 'Désactiver' : 'Activer' }}">
                    </button>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-box d-block mb-2" style="font-size:2rem;"></i>
                    Aucun module installé
                </div>
                @endforelse
            </div>
        </div>

        {{-- Limites & Configuration --}}
        <div class="settings-card">
            <div class="card-header">
                <span><i class="bi bi-sliders me-1"></i>Limites & Configuration</span>
            </div>
            <div class="card-body">
                <form id="limitsForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw500">Nombre max d'utilisateurs</label>
                        <input type="number" class="form-control" id="maxUsers"
                               value="{{ $cabinet->limits['max_users'] ?? 50 }}" min="1" max="10000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw500">Stockage max (Mo)</label>
                        <input type="number" class="form-control" id="maxStorage"
                               value="{{ $cabinet->limits['max_storage_mb'] ?? 1000 }}" min="1" max="100000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw500">Nombre max de clients</label>
                        <input type="number" class="form-control" id="maxClients"
                               value="{{ $cabinet->limits['max_clients'] ?? 500 }}" min="1" max="100000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw500">Fuseau horaire</label>
                        <select class="form-select" id="configTimezone">
                            <option value="Africa/Porto-Novo" {{ ($cabinet->config['timezone'] ?? '') == 'Africa/Porto-Novo' ? 'selected' : '' }}>Africa/Porto-Novo (UTC+1)</option>
                            <option value="Africa/Abidjan" {{ ($cabinet->config['timezone'] ?? '') == 'Africa/Abidjan' ? 'selected' : '' }}>Africa/Abidjan (UTC+0)</option>
                            <option value="Africa/Douala" {{ ($cabinet->config['timezone'] ?? '') == 'Africa/Douala' ? 'selected' : '' }}>Africa/Douala (UTC+1)</option>
                            <option value="Europe/Paris" {{ ($cabinet->config['timezone'] ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (UTC+1)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-save me-1"></i>Sauvegarder la configuration
                    </button>
                </form>
                <div id="limitsResult" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ─── Sauvegarder le cabinet ───
    document.getElementById('cabinetForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('nom', document.getElementById('cabinetNom').value);
        formData.append('email', document.getElementById('cabinetEmail').value);
        formData.append('telephone', document.getElementById('cabinetTelephone').value);
        formData.append('site_web', document.getElementById('cabinetSite').value);
        formData.append('adresse', document.getElementById('cabinetAdresse').value);
        formData.append('ville', document.getElementById('cabinetVille').value);
        formData.append('ifu', document.getElementById('cabinetIfu').value);
        formData.append('rccm', document.getElementById('cabinetRccm').value);
        formData.append('description', document.getElementById('cabinetDescription').value);
        const logoFile = document.getElementById('cabinetLogo').files[0];
        if (logoFile) formData.append('logo', logoFile);

        fetch('/api/admin/cabinet', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('cabinetResult');
            if (data.success) {
                div.innerHTML = '<div class="alert alert-success mb-0 py-2" style="font-size:0.85rem;"><i class="bi bi-check-circle me-1"></i>Cabinet mis à jour avec succès</div>';
            } else {
                div.innerHTML = `<div class="alert alert-danger mb-0 py-2" style="font-size:0.85rem;">${data.message || 'Erreur'}</div>`;
            }
            setTimeout(() => div.innerHTML = '', 3000);
        })
        .catch(() => {
            document.getElementById('cabinetResult').innerHTML = '<div class="alert alert-danger mb-0 py-2" style="font-size:0.85rem;">Erreur de connexion</div>';
        });
    });

    // ─── Toggle module ───
    function toggleModule(moduleId, btn) {
        const wasActive = btn.classList.contains('active');
        btn.disabled = true;
        fetch(`/api/admin/cabinet/modules/${moduleId}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active');
                btn.classList.toggle('inactive');
                btn.title = btn.classList.contains('active') ? 'Désactiver' : 'Activer';
            }
        })
        .finally(() => { btn.disabled = false; });
    }

    // ─── Sauvegarder les limites ───
    document.getElementById('limitsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('/api/admin/cabinet/limits', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                limits: {
                    max_users: parseInt(document.getElementById('maxUsers').value),
                    max_storage_mb: parseInt(document.getElementById('maxStorage').value),
                    max_clients: parseInt(document.getElementById('maxClients').value),
                },
                config: {
                    timezone: document.getElementById('configTimezone').value,
                },
            }),
        })
        .then(r => r.json())
        .then(data => {
            const div = document.getElementById('limitsResult');
            if (data.success) {
                div.innerHTML = '<div class="alert alert-success mb-0 py-2" style="font-size:0.85rem;"><i class="bi bi-check-circle me-1"></i>Configuration sauvegardée</div>';
            } else {
                div.innerHTML = '<div class="alert alert-danger mb-0 py-2" style="font-size:0.85rem;">Erreur</div>';
            }
            setTimeout(() => div.innerHTML = '', 3000);
        });
    });
</script>
@endsection
