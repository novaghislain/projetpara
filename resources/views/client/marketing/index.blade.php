@extends('layouts.portal')
@section('title', 'Marketing & Communication')

@section('content')
<div class="portal-page-header portal-mb-6" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="portal-title">Marketing & Communication</h1>
        <p class="portal-subtitle">Suivi de vos campagnes publicitaires et de vos briefs créatifs.</p>
    </div>
    <button class="portal-btn portal-btn-primary" onclick="document.getElementById('newBriefModal').showModal()">
        <i class="fas fa-plus"></i> Nouveau Brief
    </button>
</div>

@if(session('success'))
    <div class="portal-alert portal-alert-success">{{ session('success') }}</div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
    <!-- Colonne Principale: Campagnes & Visuels à valider -->
    <div>
        <div class="portal-card" style="background:white; border:1px solid var(--portal-border); border-radius:12px; padding:20px; margin-bottom:24px;">
            <h2 style="font-size:16px; margin-bottom:15px; font-weight:700;">Visuels en attente de validation</h2>
            @if($pendingContents->count() > 0)
                <ul style="list-style:none; padding:0; margin:0;">
                    @foreach($pendingContents as $content)
                        <li style="padding:15px; border:1px solid #E5E7EB; border-radius:8px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-weight:600; font-size:14px;">{{ $content->title }}</div>
                                <div style="font-size:12px; color:var(--portal-text-secondary);">Campagne : {{ $content->campaign->name }}</div>
                            </div>
                            <a href="{{ route('client.marketing.show_campaign', ['slug' => $slug, 'id' => $content->campaign_id]) }}" class="portal-btn portal-btn-secondary" style="padding:6px 12px; font-size:12px;">Examiner</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div style="text-align:center; padding:30px 0; color:var(--portal-text-secondary); font-size:14px;">
                    <i class="fas fa-check-circle" style="font-size:24px; color:#10B981; margin-bottom:10px;"></i><br>
                    Tout est à jour. Aucun visuel en attente.
                </div>
            @endif
        </div>

        <div class="portal-card" style="background:white; border:1px solid var(--portal-border); border-radius:12px; padding:20px;">
            <h2 style="font-size:16px; margin-bottom:15px; font-weight:700;">Vos Campagnes Actives</h2>
            @if($campaigns->count() > 0)
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding-bottom:10px; border-bottom:1px solid var(--portal-border); color:var(--portal-text-secondary);">Nom</th>
                            <th style="text-align:left; padding-bottom:10px; border-bottom:1px solid var(--portal-border); color:var(--portal-text-secondary);">Type</th>
                            <th style="text-align:right; padding-bottom:10px; border-bottom:1px solid var(--portal-border); color:var(--portal-text-secondary);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $campaign)
                            <tr>
                                <td style="padding:12px 0; border-bottom:1px solid #F3F4F6; font-weight:600;">{{ $campaign->name }}</td>
                                <td style="padding:12px 0; border-bottom:1px solid #F3F4F6;">{{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</td>
                                <td style="padding:12px 0; border-bottom:1px solid #F3F4F6; text-align:right;">
                                    <a href="{{ route('client.marketing.show_campaign', ['slug' => $slug, 'id' => $campaign->id]) }}" class="portal-btn portal-btn-secondary" style="padding:4px 10px; font-size:12px;">Détails</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color:var(--portal-text-secondary); font-size:14px; text-align:center; padding:20px 0;">Vous n'avez pas de campagnes en cours.</p>
            @endif
        </div>
    </div>

    <!-- Colonne Secondaire: Demandes (Briefs) -->
    <div>
        <div class="portal-card" style="background:white; border:1px solid var(--portal-border); border-radius:12px; padding:20px;">
            <h2 style="font-size:16px; margin-bottom:15px; font-weight:700;">Vos Demandes (Briefs)</h2>
            @if($briefs->count() > 0)
                <ul style="list-style:none; padding:0; margin:0;">
                    @foreach($briefs as $brief)
                        <li style="padding:12px 0; border-bottom:1px solid #F3F4F6;">
                            <div style="font-weight:600; font-size:13px;">{{ ucfirst(str_replace('_', ' ', $brief->type)) }}</div>
                            <div style="display:flex; justify-content:space-between; margin-top:5px; font-size:12px;">
                                <span style="color:var(--portal-text-secondary);">{{ $brief->created_at->format('d/m/Y') }}</span>
                                @if($brief->status == 'pending')
                                    <span style="color:#F59E0B; font-weight:600;">En attente</span>
                                @elseif($brief->status == 'accepted')
                                    <span style="color:#10B981; font-weight:600;">Accepté</span>
                                @else
                                    <span style="color:#EF4444; font-weight:600;">Rejeté</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p style="color:var(--portal-text-secondary); font-size:13px; text-align:center;">Aucune demande récente.</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal Nouveau Brief -->
<dialog id="newBriefModal" style="padding: 24px; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-width: 500px; width: 100%; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="font-size: 18px; font-weight: 600; margin: 0;">Soumettre un projet</h3>
        <button onclick="document.getElementById('newBriefModal').close()" style="background: none; border: none; cursor: pointer; font-size: 18px; color: #666;"><i class="fas fa-times"></i></button>
    </div>
    <form action="{{ route('client.marketing.store_brief', ['slug' => $slug]) }}" method="POST">
        @csrf
        <div class="portal-form-group">
            <label class="portal-label">Type de projet</label>
            <select name="type" class="portal-input" required>
                <option value="social_media">Animation Réseaux Sociaux</option>
                <option value="ads">Campagne Publicitaire</option>
                <option value="seo">Optimisation SEO</option>
                <option value="branding">Création Graphique (Logo, etc.)</option>
                <option value="other">Autre</option>
            </select>
        </div>
        <div class="portal-form-group">
            <label class="portal-label">Description (Objectifs, Idées)</label>
            <textarea name="description" class="portal-input" rows="4" required placeholder="Décrivez vos besoins..."></textarea>
        </div>
        <div class="portal-form-group">
            <label class="portal-label">Budget Approximatif (F CFA) - Optionnel</label>
            <input type="number" name="budget_estimation" class="portal-input">
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button" class="portal-btn portal-btn-secondary" onclick="document.getElementById('newBriefModal').close()">Annuler</button>
            <button type="submit" class="portal-btn portal-btn-primary"><i class="fas fa-paper-plane"></i> Envoyer</button>
        </div>
    </form>
</dialog>
@endsection
