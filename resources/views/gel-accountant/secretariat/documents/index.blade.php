@extends('gel-accountant.layouts.app')

@section('title', 'Explorateur de Documents - GED')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">Gestion Électronique des Documents (GED)</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- Zone d'importation -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Importer un nouveau document</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('gel-accountant.secretariat.documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3 text-center p-4 border border-primary rounded" style="border-style: dashed !important; background-color: #f8f9fc;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <p class="mb-2">Glissez votre fichier ici ou cliquez pour sélectionner</p>
                            <input type="file" name="file" id="file" class="form-control-file" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="category">Catégorie</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">Aucune</option>
                                <option value="Facture">Facture</option>
                                <option value="Contrat">Contrat</option>
                                <option value="Identité">Pièce d'Identité</option>
                                <option value="Bancaire">Relevé Bancaire</option>
                                <option value="Juridique">Acte Juridique</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description (Optionnel)</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-upload"></i> Uploader le document
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des documents -->
        <div class="col-md-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tous les documents</h6>
                    <form action="{{ route('gel-accountant.secretariat.documents.index') }}" method="GET" class="form-inline">
                        <select name="categorie" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">Toutes les catégories</option>
                            <option value="Facture" {{ request('categorie') == 'Facture' ? 'selected' : '' }}>Facture</option>
                            <option value="Contrat" {{ request('categorie') == 'Contrat' ? 'selected' : '' }}>Contrat</option>
                            <option value="Identité" {{ request('categorie') == 'Identité' ? 'selected' : '' }}>Identité</option>
                            <option value="Bancaire" {{ request('categorie') == 'Bancaire' ? 'selected' : '' }}>Bancaire</option>
                            <option value="Juridique" {{ request('categorie') == 'Juridique' ? 'selected' : '' }}>Juridique</option>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nom du fichier</th>
                                    <th>Catégorie</th>
                                    <th>Taille</th>
                                    <th>Date d'import</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td>
                                            @php
                                                $icon = 'fa-file';
                                                if (str_contains($doc->mime_type, 'pdf')) $icon = 'fa-file-pdf text-danger';
                                                elseif (str_contains($doc->mime_type, 'image')) $icon = 'fa-file-image text-info';
                                                elseif (str_contains($doc->mime_type, 'word')) $icon = 'fa-file-word text-primary';
                                                elseif (str_contains($doc->mime_type, 'excel') || str_contains($doc->mime_type, 'spreadsheet')) $icon = 'fa-file-excel text-success';
                                            @endphp
                                            <i class="fas {{ $icon }} mr-2"></i>
                                            {{ $doc->name }}
                                        </td>
                                        <td><span class="badge bg-secondary text-white">{{ $doc->category ?? 'Non classé' }}</span></td>
                                        <td>{{ number_format($doc->file_size / 1024, 2) }} KB</td>
                                        <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('gel-accountant.secretariat.documents.download', $doc->id) }}" class="btn btn-sm btn-success" title="Télécharger">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form action="{{ route('gel-accountant.secretariat.documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun document trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
