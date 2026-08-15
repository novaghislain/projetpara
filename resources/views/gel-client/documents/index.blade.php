@extends('layouts.gel-client')

@section('title', 'Boîte à Documents')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Boîte à Documents</h1>
            <p class="text-muted mt-1">Transmettez et consultez les documents échangés avec le cabinet.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('uploadDocForm').style.display='block';">
                <i class="fas fa-upload me-2"></i> Transmettre un document
            </button>
        </div>
    </div>

    <!-- Formulaire d'upload caché par défaut -->
    <div id="uploadDocForm" class="card shadow-sm border-0 rounded-3 mb-4" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Nouveau Document</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('gel-client.documents.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type de document</label>
                        <select class="form-select" name="type">
                            <option value="facture_achat">Facture d'achat / Reçu</option>
                            <option value="facture_vente">Facture de vente</option>
                            <option value="releve_bancaire">Relevé bancaire</option>
                            <option value="social">Document social / Paie</option>
                            <option value="fiscal">Document fiscal</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fichier</label>
                        <input class="form-control" type="file" name="file" required>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-light me-2" onclick="document.getElementById('uploadDocForm').style.display='none';">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer au cabinet</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nom du fichier</th>
                            <th>Date d'ajout</th>
                            <th>Type</th>
                            <th>Statut (Cabinet)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                        <tr>
                            <td>
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                {{ $document->file_name ?? 'Document' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $document->type ?? 'Général' }}</span></td>
                            <td>
                                @if($document->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> En attente</span>
                                @elseif($document->status == 'processed')
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Traité</span>
                                @else
                                    <span class="badge bg-secondary">{{ $document->status }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-sm btn-light" title="Télécharger"><i class="fas fa-download"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <div class="mb-3"><i class="fas fa-folder-open fa-3x text-light"></i></div>
                                Aucun document dans la boîte.
                            </td>
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
@endsection
