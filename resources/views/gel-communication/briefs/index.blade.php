@extends(auth()->user()->account_type === 'informaticien' ? 'layouts.gel-informaticien' : 'layouts.gel-communication')

@section('title', 'Briefs Clients')

@section('content')
<div class="p-4">
    <div class="sec-page-header">
        <div>
            <h1 class="sec-page-title">Briefs Clients</h1>
            <div class="sec-page-sub">Gérez les demandes de prestations marketing entrantes.</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('gel-communication.briefs.index', ['status' => 'pending']) }}" class="sec-btn {{ $status == 'pending' ? 'sec-btn-primary' : 'sec-btn-secondary' }}">En attente</a>
        <a href="{{ route('gel-communication.briefs.index', ['status' => 'accepted']) }}" class="sec-btn {{ $status == 'accepted' ? 'sec-btn-primary' : 'sec-btn-secondary' }}">Acceptés</a>
        <a href="{{ route('gel-communication.briefs.index', ['status' => 'rejected']) }}" class="sec-btn {{ $status == 'rejected' ? 'sec-btn-primary' : 'sec-btn-secondary' }}">Rejetés</a>
    </div>

    <div class="sec-card">
        <div class="sec-card-body p-0">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Type de Prestation</th>
                        <th>Budget Estimé</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($briefs as $brief)
                        <tr>
                            <td>{{ $brief->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-weight:600;">{{ $brief->client->company_name ?? 'Inconnu' }}</td>
                            <td><span class="sec-badge sec-badge-muted">{{ ucfirst(str_replace('_', ' ', $brief->type)) }}</span></td>
                            <td style="color:var(--sec-text-muted);">
                                {{ $brief->budget_estimation ? number_format($brief->budget_estimation, 0, ',', ' ') . ' F' : 'Non précisé' }}
                            </td>
                            <td>
                                @if($brief->status == 'pending')
                                    <span class="sec-badge sec-badge-warning">À traiter</span>
                                @elseif($brief->status == 'accepted')
                                    <span class="sec-badge sec-badge-success">Accepté</span>
                                @else
                                    <span class="sec-badge sec-badge-danger">Rejeté</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('gel-communication.briefs.show', $brief->id) }}" class="sec-btn sec-btn-sm sec-btn-secondary">Traiter</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3" style="color:var(--sec-border);"></i><br>
                                Aucun brief dans cette catégorie
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($briefs->hasPages())
        <div class="p-3 border-top">
            {{ $briefs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
