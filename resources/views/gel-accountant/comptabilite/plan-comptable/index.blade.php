@extends('layouts.gel-accountant')

@section('title', 'Plan comptable - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Plan comptable</h1>
        <p class="gel-page-subtitle">Plan comptable SYSCOHADA — {{ $comptes->count() ?? 0 }} comptes</p>
    </div>
    <div style="display:flex;gap:8px;">
        <div class="gel-filter-group">
            <select class="gel-filter-select" id="classeFilter" onchange="filterByClasse(this.value)">
                <option value="">Toutes les classes</option>
                @foreach(range(1,8) as $c)
                    <option value="{{ $c }}" {{ request('classe') == $c ? 'selected' : '' }}>Classe {{ $c }}</option>
                @endforeach
            </select>
        </div>
        <button class="gel-btn gel-btn-primary" onclick="openPanel('Nouveau compte', `
            <form method="POST" action="{{ route('gel-accountant.comptabilite.plan-comptable.store') }}" id="newCompteForm">
                @csrf
                <div class="gel-form-group">
                    <label>Code *</label>
                    <input type="text" name="code" class="gel-form-control" required placeholder="Ex: 601">
                </div>
                <div class="gel-form-group">
                    <label>Intitulé *</label>
                    <input type="text" name="intitule" class="gel-form-control" required>
                </div>
                <div class="gel-form-group">
                    <label>Classe</label>
                    <select name="classe" class="gel-form-select">
                        @foreach(range(1,8) as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gel-form-group">
                    <label>Type</label>
                    <select name="type" class="gel-form-select">
                        <option value="">—</option>
                        <option value="actif">Actif</option>
                        <option value="passif">Passif</option>
                        <option value="charge">Charge</option>
                        <option value="produit">Produit</option>
                    </select>
                </div>
                <div class="gel-form-group">
                    <label>Code parent</label>
                    <input type="text" name="code_parent" class="gel-form-control" placeholder="Ex: 60">
                </div>
            </form>
        `, '<button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'newCompteForm\').submit()">Créer</button>')">
            <i class="bi bi-plus-circle"></i> Nouveau compte
        </button>
    </div>
</div>

<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @if($comptes->count() > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th style="width:100px;">Code</th>
                        <th>Intitulé</th>
                        <th style="width:80px;">Classe</th>
                        <th style="width:100px;">Type</th>
                        <th style="width:80px;">Niveau</th>
                        <th style="width:80px;">SYSCOHADA</th>
                        <th style="width:80px;">Actif</th>
                        <th style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comptes as $compte)
                        <tr>
                            <td><strong>{{ $compte->code }}</strong></td>
                            <td>
                                {{ $compte->intitule }}
                                @if($compte->code_parent)
                                    <br><small style="color:var(--gel-text-muted);">Parent: {{ $compte->code_parent }}</small>
                                @endif
                            </td>
                            <td>{{ $compte->classe }}</td>
                            <td>{{ $compte->type ?? '—' }}</td>
                            <td>{{ $compte->niveau }}</td>
                            <td>
                                @if($compte->syscohada)
                                    <span class="gel-badge gel-badge-info">OHADA</span>
                                @else
                                    <span class="gel-badge gel-badge-warning">Perso</span>
                                @endif
                            </td>
                            <td>
                                @if($compte->actif)
                                    <span style="color:var(--gel-success);"><i class="bi bi-check-circle-fill"></i></span>
                                @else
                                    <span style="color:var(--gel-danger);"><i class="bi bi-x-circle-fill"></i></span>
                                @endif
                            </td>
                            <td>
                                <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="openPanel('Modifier {{ $compte->code }}', \`
                                    <form method="POST" action="{{ route('gel-accountant.comptabilite.plan-comptable.update', $compte->id) }}" id="editCompteForm{{ $compte->id }}">
                                        @csrf @method('PUT')
                                        <div class="gel-form-group">
                                            <label>Code</label>
                                            <input type="text" name="code" class="gel-form-control" value="{{ $compte->code }}">
                                        </div>
                                        <div class="gel-form-group">
                                            <label>Intitulé</label>
                                            <input type="text" name="intitule" class="gel-form-control" value="{{ $compte->intitule }}">
                                        </div>
                                        <div class="gel-form-group">
                                            <label>Type</label>
                                            <select name="type" class="gel-form-select">
                                                <option value="">—</option>
                                                <option value="actif" {{ $compte->type === 'actif' ? 'selected' : '' }}>Actif</option>
                                                <option value="passif" {{ $compte->type === 'passif' ? 'selected' : '' }}>Passif</option>
                                                <option value="charge" {{ $compte->type === 'charge' ? 'selected' : '' }}>Charge</option>
                                                <option value="produit" {{ $compte->type === 'produit' ? 'selected' : '' }}>Produit</option>
                                            </select>
                                        </div>
                                        <div class="gel-form-group">
                                            <label>Actif</label>
                                            <label class="gel-toggle">
                                                <input type="checkbox" name="actif" value="1" {{ $compte->actif ? 'checked' : '' }}>
                                                <span class="gel-toggle-slider"></span>
                                                <span>Compte actif</span>
                                            </label>
                                        </div>
                                    </form>
                                \`, '<button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'editCompteForm{{ $compte->id }}\').submit()">Enregistrer</button>')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="gel-empty">
                <i class="bi bi-list-columns"></i>
                <h3>Plan comptable vide</h3>
                <p>Importez le plan SYSCOHADA ou créez vos comptes manuellement.</p>
            </div>
        @endif
    </div>
</div>

<script>
    function filterByClasse(classe) {
        const url = new URL(window.location);
        if (classe) url.searchParams.set('classe', classe);
        else url.searchParams.delete('classe');
        window.location.href = url.toString();
    }
</script>
@endsection
