@extends('layouts.gel-secretary')
@section('title', 'Journal des Appels')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-phone-alt" style="color:var(--sec-primary); margin-right:8px;"></i>Journal des Appels
    </h1>
    <p class="sec-page-sub">Historique des appels pour {{ $activeClient ? $activeClient->company_name : 'tous les clients' }}</p>
  </div>
  <button type="button" class="sec-btn sec-btn-primary" onclick="document.getElementById('modalAddCall').style.display='flex'">
    <i class="fas fa-plus"></i> Nouvel appel
  </button>
</div>

@if(session('success'))
<div class="alert alert-success animate-fade delay-1" style="font-size:13px; font-weight:600; padding:10px 16px; margin-bottom: 20px;">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="sec-card animate-fade delay-2" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
  <div class="sec-card-header">
    <div class="sec-card-title"><i class="fas fa-list" style="color:var(--sec-primary);margin-right:6px;"></i> Liste des appels</div>
  </div>
  <div style="overflow-x:auto;">
    <table class="sec-table">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="padding:14px 18px;">Type</th>
          <th style="padding:14px 18px;">Contact / Entreprise</th>
          <th style="padding:14px 18px;">Date & Heure</th>
          <th style="padding:14px 18px;">Statut</th>
          <th style="padding:14px 18px;">Notes / Résumé</th>
        </tr>
      </thead>
      <tbody>
        @forelse($calls as $call)
          <tr style="border-bottom:1px solid #f3f4f6;">
            <td style="padding:14px 18px;">
              @if($call->direction == 'entrant')
                <div style="color:#16A34A;font-weight:700;"><i class="fas fa-arrow-down"></i> Entrant</div>
              @else
                <div style="color:#2563EB;font-weight:700;"><i class="fas fa-arrow-up"></i> Sortant</div>
              @endif
            </td>
            <td style="padding:14px 18px;">
              <div style="font-weight:700;color:#1e293b;">{{ $call->contact_name ?? 'Inconnu' }}</div>
              <div style="font-size:11px;color:#475569;">{{ $call->client->company_name ?? '—' }} {{ $call->phone ? '('.$call->phone.')' : '' }}</div>
            </td>
            <td style="padding:14px 18px;">
              <div style="font-weight:600;color:#334155;">{{ \Carbon\Carbon::parse($call->called_at)->format('d/m/Y') }}</div>
              <div style="font-size:11px;color:#64748b;">{{ \Carbon\Carbon::parse($call->called_at)->format('H:i') }}</div>
            </td>
            <td style="padding:14px 18px;">
              @if($call->statut == 'abouti')
                <span style="background:#DCFCE7;color:#16A34A;padding:4px 8px;border-radius:12px;font-size:12px;">Abouti</span>
              @elseif($call->statut == 'non_abouti')
                <span style="background:#FEF2F2;color:#DC2626;padding:4px 8px;border-radius:12px;font-size:12px;">Non abouti</span>
              @elseif($call->statut == 'messagerie')
                <span style="background:#F3F4F6;color:#4B5563;padding:4px 8px;border-radius:12px;font-size:12px;">Message vocal</span>
              @endif
            </td>
            <td style="padding:14px 18px; font-size:12px; color:#64748b;">
              {{ Str::limit($call->notes, 60) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
              <i class="fas fa-phone-slash" style="font-size:32px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
              Aucun appel consigné pour cette entreprise.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Ajout -->
<div id="modalAddCall" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:700px; max-width:95%; max-height:90vh; overflow-y:auto; border:1px solid var(--sec-border); box-shadow:var(--sec-shadow);">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0;">Consigner un appel</h3>
            <button type="button" onclick="document.getElementById('modalAddCall').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('gel-secretary.calls.store') }}" method="POST" style="padding:20px;">
            @csrf
            
            <div class="row g-3" style="display:flex; flex-wrap:wrap; margin:-10px;">
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Sens de l'appel *</label>
                        <div style="display:flex;gap:12px;">
                            <label style="flex:1;border:1px solid var(--sec-border);border-radius:6px;padding:8px;text-align:center;cursor:pointer;background:#f9fafb;">
                                <input type="radio" name="direction" value="entrant" checked> <i class="fas fa-arrow-down" style="color:#16A34A;"></i> Entrant
                            </label>
                            <label style="flex:1;border:1px solid var(--sec-border);border-radius:6px;padding:8px;text-align:center;cursor:pointer;background:#f9fafb;">
                                <input type="radio" name="direction" value="sortant"> <i class="fas fa-arrow-up" style="color:#2563EB;"></i> Sortant
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Entreprise concernée *</label>
                        <select name="client_id" class="sec-form-control" required>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($activeClient && $activeClient->id == $c->id) ? 'selected' : '' }}>{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Nom du contact</label>
                        <input type="text" name="contact_name" class="sec-form-control">
                    </div>
                </div>
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Téléphone</label>
                        <input type="text" name="phone" class="sec-form-control">
                    </div>
                </div>
                
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Date & Heure *</label>
                        <input type="datetime-local" name="called_at" class="sec-form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Statut *</label>
                        <select name="statut" class="sec-form-control" required>
                            <option value="abouti">Abouti</option>
                            <option value="non_abouti">Non abouti (Pas de réponse)</option>
                            <option value="messagerie">Message vocal laissé</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div class="sec-form-group">
                        <label>Notes / Compte rendu</label>
                        <textarea name="notes" class="sec-form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div style="background:#F8FAFC;padding:12px;border-radius:6px;border:1px solid var(--sec-border);">
                        <label style="display:block; font-weight:600; font-size:13px; color:var(--sec-text); cursor:pointer;">
                            <input type="checkbox" name="a_rappeler" value="1" onchange="document.getElementById('div_rappel').style.display = this.checked ? 'block' : 'none'" style="margin-right:6px;"> 
                            Planifier un rappel automatique
                        </label>
                        <div id="div_rappel" style="display:none;margin-top:12px;">
                            <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--sec-text-muted);">Date & Heure du rappel</label>
                            <input type="datetime-local" name="date_rappel" class="sec-form-control">
                            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Un rendez-vous sera automatiquement ajouté à votre agenda.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px; padding-top:16px; border-top:1px solid var(--sec-border);">
                <button type="button" class="sec-btn" onclick="document.getElementById('modalAddCall').style.display='none'" style="background:#f1f5f9; color:#475569;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Enregistrer l'appel</button>
            </div>
        </form>
    </div>
</div>
@endsection
