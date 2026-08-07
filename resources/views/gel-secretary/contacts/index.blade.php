@extends('layouts.gel-secretary')

@section('title', 'Annuaire Contacts — Secrétariat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-address-book" style="color:var(--sec-primary); margin-right:8px;"></i>Annuaire des Contacts
    </h1>
    <p class="sec-page-sub">
      @if($activeClient)
        Contacts de l'entreprise : <strong>{{ $activeClient->nom_entreprise }}</strong>
      @else
        Veuillez sélectionner une entreprise active dans la barre supérieure.
      @endif
    </p>
  </div>
  @if($activeClient)
  <button class="sec-btn sec-btn-primary" onclick="document.getElementById('contactModal').style.display='flex'">
    <i class="fas fa-plus"></i> Nouveau contact
  </button>
  @endif
</div>

@if($activeClient)
<div class="sec-card" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
  <div class="sec-card-header">
    <div class="sec-card-title"><i class="fas fa-users" style="color:var(--sec-primary);margin-right:6px;"></i>Liste des contacts de l'entreprise ({{ $contacts->count() }})</div>
  </div>

  <div style="overflow-x:auto;">
    <table class="sec-table">
        <thead>
            <tr style="background:#f9fafb;">
                <th style="padding:14px 18px;">Nom & Prénom</th>
                <th style="padding:14px 18px;">Poste / Fonction</th>
                <th style="padding:14px 18px;">Téléphone</th>
                <th style="padding:14px 18px;">Email</th>
                <th style="padding:14px 18px; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:14px 18px; font-weight:700; color:#1e293b;">
                        {{ $contact->name }}
                        @if(isset($contact->type) && $contact->type === 'portal')
                            <span style="margin-left: 8px; font-size: 10px; background: var(--sec-primary); color: white; padding: 2px 6px; border-radius: 4px; vertical-align: middle;">PORTAIL</span>
                        @endif
                    </td>
                    <td style="padding:14px 18px; color:#475569;">
                        {{ $contact->position ?: '—' }}
                    </td>
                    <td style="padding:14px 18px; font-family:monospace; color:#334155;">
                        {{ $contact->phone ?: '—' }}
                    </td>
                    <td style="padding:14px 18px; color:#64748b; font-family:monospace;">
                        {{ $contact->email ?: '—' }}
                    </td>
                    <td style="padding:14px 18px; text-align:right;">
                        <form action="{{ isset($contact->type) && $contact->type === 'portal' ? '#' : route('gel-secretary.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Supprimer ce contact ?');" style="display:flex; gap:8px; justify-content:flex-end;">
                            <a href="{{ isset($contact->type) && $contact->type === 'portal' ? '#' : route('gel-secretary.contacts.show', $contact->id) }}" class="sec-btn sec-btn-sm" style="background:#F0FDFA; color:var(--sec-primary); border:1px solid #CCFBF1; text-decoration:none;" title="Voir Fiche 360°">
                                <i class="fas fa-eye"></i> Voir Fiche
                            </a>
                            @csrf
                            @if(!isset($contact->type) || $contact->type !== 'portal')
                                @method('DELETE')
                            @endif
                            @if(!isset($contact->type) || $contact->type !== 'portal')
                                <button type="submit" class="sec-btn sec-btn-sm" style="background:#fef2f2; color:#ef4444; border:1px solid #fca5a5;" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </button>
                            @else
                                <button type="button" class="sec-btn sec-btn-sm" style="background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0; cursor:not-allowed;" title="Non modifiable ici">
                                    <i class="fas fa-lock"></i>
                                </button>
                            @endif
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
                        <i class="fas fa-address-book" style="font-size:32px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
                        Aucun contact enregistré pour cette entreprise.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- ─── MODAL NOUVEAU CONTACT ─── --}}
<div id="contactModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:460px; max-width:90%; border:1px solid var(--sec-border); box-shadow:var(--sec-shadow); overflow:hidden;">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin:0;">Nouveau contact</h3>
            <button type="button" onclick="document.getElementById('contactModal').style.display='none'" style="background:none; border:none; font-size:18px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('gel-secretary.contacts.store') }}" method="POST" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            @csrf

            <div class="sec-form-group">
                <label>Nom & Prénom *</label>
                <input type="text" name="name" required placeholder="Ex: Jean Dupont" class="sec-form-control">
            </div>

            <div class="sec-form-group">
                <label>Poste / Fonction</label>
                <input type="text" name="position" placeholder="Ex: Directeur Général, Comptable" class="sec-form-control">
            </div>

            <div class="sec-form-group">
                <label>Téléphone</label>
                <input type="text" name="phone" placeholder="Ex: +225 07070707" class="sec-form-control">
            </div>

            <div class="sec-form-group">
                <label>Adresse email</label>
                <input type="email" name="email" placeholder="Ex: jean.dupont@entreprise.com" class="sec-form-control">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
                <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('contactModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Ajouter le contact</button>
            </div>
        </form>
    </div>
</div>
@else
<div class="sec-card" style="padding:40px; text-align:center; color:var(--sec-text-muted);">
  <i class="fas fa-building" style="font-size:48px; margin-bottom:12px; color:#cbd5e1;"></i>
  <p style="font-size:14px; font-weight:600;">Aucune entreprise active n'est actuellement sélectionnée.</p>
  <p style="font-size:12px;">Veuillez utiliser le sélecteur situé dans l'en-tête pour choisir l'entreprise dont vous souhaitez gérer les contacts.</p>
</div>
@endif
@endsection
