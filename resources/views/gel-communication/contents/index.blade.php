@extends('layouts.gel-communication')

@section('title', 'Visuels & Validations')

@section('content')
<div class="p-4">
    <div class="sec-page-header">
        <div>
            <h1 class="sec-page-title">Visuels & Validations</h1>
            <div class="sec-page-sub">Gérez les retours clients sur les livrables.</div>
        </div>
    </div>

    <div class="sec-card">
        <div class="sec-card-body p-0">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Campagne</th>
                        <th>Titre Visuel</th>
                        <th>Statut</th>
                        <th>Retour Client</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $contents = \App\Models\Gel\MarketingContent::with('campaign.client')->orderBy('created_at', 'desc')->paginate(15);
                    @endphp
                    @forelse($contents as $content)
                        <tr>
                            <td>{{ $content->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-weight:600;">
                                <a href="{{ route('gel-communication.campaigns.show', $content->campaign_id) }}" style="color:var(--sec-primary);">
                                    {{ $content->campaign->name }}
                                </a>
                                <br><small class="text-muted">{{ $content->campaign->client->company_name ?? '' }}</small>
                            </td>
                            <td>{{ $content->title }}</td>
                            <td>
                                @if($content->status == 'pending_client')
                                    <span class="sec-badge sec-badge-warning">Attente client</span>
                                @elseif($content->status == 'approved')
                                    <span class="sec-badge sec-badge-success">Validé</span>
                                @elseif($content->status == 'rejected')
                                    <span class="sec-badge sec-badge-danger">Rejeté</span>
                                @else
                                    <span class="sec-badge sec-badge-muted">{{ $content->status }}</span>
                                @endif
                            </td>
                            <td style="font-style:italic; font-size:12px; color:var(--sec-text-muted);">
                                {{ $content->client_feedback ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-image fa-3x mb-3" style="color:var(--sec-border);"></i><br>
                                Aucun visuel.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contents->hasPages())
        <div class="p-3 border-top">
            {{ $contents->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
