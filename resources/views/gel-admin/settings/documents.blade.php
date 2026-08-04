@extends('layouts.gel-admin')

@section('title', 'Documents Légaux')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Documents Légaux</h1>
        <p class="admin-page-sub">Gérez les documents officiels de votre entreprise (Statuts, KBIS, etc.).</p>
    </div>
    <button class="admin-btn admin-btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
        <i class="fas fa-upload"></i> Ajouter un document
    </button>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Liste des documents</div>
    </div>
    <div class="admin-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 px-4 py-3">Type</th>
                    <th class="border-0 px-4 py-3">Titre</th>
                    <th class="border-0 px-4 py-3">Date d'ajout</th>
                    <th class="border-0 px-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td class="px-4">
                            <span class="badge bg-secondary">{{ $doc->type }}</span>
                        </td>
                        <td class="px-4 fw-medium">{{ $doc->titre }}</td>
                        <td class="px-4 text-muted">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 text-end">
                            <a href="{{ asset('storage/' . $doc->chemin) }}" target="_blank" class="btn btn-sm btn-light">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('gel-admin.profile.documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce document ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Aucun document ajouté.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gel-admin.profile.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Ajouter un document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type de document</label>
                        <select name="type" class="form-select" required>
                            <option value="Statuts">Statuts de l'entreprise</option>
                            <option value="KBIS">Extrait KBIS / RCCM</option>
                            <option value="IFU">Numéro d'Identifiant Fiscal</option>
                            <option value="Contrat">Contrat d'abonnement</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre (Description)</label>
                        <input type="text" name="titre" class="form-control" placeholder="Ex: Statuts mis à jour 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fichier (PDF, JPG, PNG)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Uploader le document</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
