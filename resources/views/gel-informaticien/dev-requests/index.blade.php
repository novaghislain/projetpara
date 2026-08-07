@extends('layouts.gel-informaticien')

@section('title', 'Demandes de Développement Web/App')

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Demandes de Développement Web/App</h1>
            <p style="font-size: 12px; color: var(--sec-text-muted);">Projets de digitalisation (sites web, applications métiers) à deviser et développer.</p>
        </div>
    </div>

    <div class="pro-panel animate-fade delay-1 mb-4" style="border-top: 4px solid var(--sec-info);">
        <div class="sec-card-header d-flex justify-content-between align-items-center" style="padding:16px 20px; border-bottom:1px solid var(--sec-border); flex-wrap: wrap; gap: 10px;">
            <h5 class="mb-0" style="font-weight:600; font-size:15px; color:var(--sec-info);"><i class="fas fa-code me-2"></i> File de Projets de Digitalisation</h5>
            
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('gel-informaticien.dev-requests.index') }}" method="GET" class="d-flex" style="max-width: 250px;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Rechercher un projet...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary ms-1"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="btn-group">
                    <a href="{{ route('gel-informaticien.dev-requests.index') }}" class="btn btn-sm {{ !$status ? 'btn-info text-white' : 'btn-outline-info' }}">Toutes</a>
                    <a href="{{ route('gel-informaticien.dev-requests.index', ['status' => 'recue']) }}" class="btn btn-sm {{ $status == 'recue' ? 'btn-info text-white' : 'btn-outline-info' }}">Reçues</a>
                    <a href="{{ route('gel-informaticien.dev-requests.index', ['status' => 'devis_en_cours']) }}" class="btn btn-sm {{ $status == 'devis_en_cours' ? 'btn-info text-white' : 'btn-outline-info' }}">En Devis</a>
                    <a href="{{ route('gel-informaticien.dev-requests.index', ['status' => 'en_developpement']) }}" class="btn btn-sm {{ $status == 'en_developpement' ? 'btn-info text-white' : 'btn-outline-info' }}">En Développement</a>
                </div>
            </div>
        </div>

        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="sec-table" style="margin-bottom:0;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th>Réf</th>
                            <th>Client demandeur</th>
                            <th>Type de projet</th>
                            <th>Statut</th>
                            <th>Lien Devis</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td style="font-weight:600;">#DEV-{{ $req->id }}</td>
                                <td><span class="fw-bold">{{ $req->client->company_name ?? 'Client Inconnu' }}</span></td>
                                <td>{{ $req->subject }}</td>
                                <td>
                                    @if($req->status == 'recue')
                                        <span class="sec-badge sec-badge-info">Nouvelle demande</span>
                                    @elseif($req->status == 'devis_en_cours')
                                        <span class="sec-badge sec-badge-warning">Devis en préparation</span>
                                    @elseif($req->status == 'en_developpement')
                                        <span class="sec-badge sec-badge-info" style="background:#DBEAFE;color:#2563EB;">En développement</span>
                                    @elseif($req->status == 'livre')
                                        <span class="sec-badge sec-badge-success">Projet Livré</span>
                                    @else
                                        <span class="sec-badge sec-badge-muted">{{ $req->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->devis_url)
                                        <a href="{{ $req->devis_url }}" target="_blank" class="text-info" style="font-weight:600;"><i class="fas fa-file-invoice"></i> Voir Devis</a>
                                    @else
                                        <span class="text-muted fst-italic small">Aucun devis lié</span>
                                    @endif
                                </td>
                                <td style="color:var(--sec-text-muted); font-size:12px;">{{ $req->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('gel-informaticien.dev-requests.show', $req->id) }}" class="btn btn-sm" style="background:#e0f2fe; color:#0369a1; font-weight:600;">
                                        <i class="fas fa-edit"></i> Gérer
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <h5>Aucune demande de projet digital</h5>
                                    <p>Les demandes des clients apparaîtront ici.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($requests->hasPages())
            <div class="p-3 border-top">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
