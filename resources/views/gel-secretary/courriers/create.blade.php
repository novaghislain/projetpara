@extends('layouts.gel-secretary')
@section('title', 'Nouveau Courrier')

@push('styles')
<style>
/* ==========================================================================
   COURRIERS CREATE - BENTO GRID DESIGN
   ========================================================================== */
.form-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.bento-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
}

.card-header { font-size: 18px; font-weight: 800; color: #1E293B; margin-bottom: 24px; display:flex; align-items:center; gap:12px;}

.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 13px; font-weight: 700; color: #64748B; margin-bottom: 8px; text-transform:uppercase; letter-spacing:0.5px;}
.form-control {
    width: 100%; background: #F8FAFC; border: 1px solid #E2E8F0;
    border-radius: 12px; padding: 12px 16px; outline: none;
    font-size: 14px; font-family: inherit; transition: all 0.2s;
}
.form-control:focus { background: white; border-color: #0D9488; box-shadow: 0 0 0 4px rgba(13,148,136,0.1); }
textarea.form-control { resize: vertical; min-height:100px; }

.row { display: flex; gap: 20px; }
.col { flex: 1; }

.btn-submit { background: #0D9488; color: white; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: all 0.2s; display:inline-flex; align-items:center; gap:8px;}
.btn-submit:hover { background: #0F766E; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.3);}
.btn-back { background: white; color: #1E293B; border: 1px solid #E2E8F0; padding: 14px 28px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration:none; display:inline-flex; align-items:center; gap:8px;}
.btn-back:hover { background: #F8FAFC; }

.stagger-1 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.2s;}
.stagger-3 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.3s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px;" class="stagger-1">
    <div>
        <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">Nouveau Courrier</h1>
        <div style="font-size:14px; color:#64748B; margin-top:4px;">Enregistrez un courrier entrant, sortant ou interne au registre.</div>
    </div>
    <div>
        <a href="{{ route('gel-secretary.courriers.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Registre</a>
    </div>
</div>

<form action="{{ route('gel-secretary.courriers.store') }}" method="POST">
    @csrf

    <div class="form-grid">
        <!-- Informations Générales -->
        <div class="bento-card stagger-2" style="grid-column: span 8;">
            <div class="card-header"><i class="fas fa-info-circle" style="color:#0D9488;"></i> Informations Générales</div>
            
            <div class="row">
                <div class="col form-group">
                    <label class="form-label">Type de Courrier <span style="color:#EF4444;">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="entrant">Courrier Arrivée (Entrant)</option>
                        <option value="sortant">Courrier Départ (Sortant)</option>
                        <option value="interne">Courrier Interne (Décharge)</option>
                    </select>
                </div>
                <div class="col form-group">
                    <label class="form-label">Entreprise (Client) <span style="color:#EF4444;">*</span></label>
                    <select name="client_id" class="form-control" required>
                        @php
                            $clients = \App\Models\Gel\Client::orderBy('nom_entreprise')->get();
                            $activeClientId = session('active_client_id') ?? auth()->user()->active_client_id;
                        @endphp
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $activeClientId == $client->id ? 'selected' : '' }}>
                                {{ $client->nom_entreprise }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Objet du courrier <span style="color:#EF4444;">*</span></label>
                <input type="text" name="objet" class="form-control" required placeholder="Ex: Convocation Assemblée Générale">
            </div>

            <div class="row">
                <div class="col form-group">
                    <label class="form-label">Expéditeur</label>
                    <input type="text" name="expediteur" class="form-control" placeholder="Nom de l'expéditeur">
                </div>
                <div class="col form-group">
                    <label class="form-label">Destinataire</label>
                    <input type="text" name="destinataire" class="form-control" placeholder="Nom du destinataire">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Contenu ou Résumé</label>
                <textarea name="contenu" class="form-control" placeholder="Saisissez un résumé ou les points clés du courrier..."></textarea>
            </div>
        </div>

        <!-- Détails de Suivi -->
        <div class="bento-card stagger-3" style="grid-column: span 4;">
            <div class="card-header"><i class="fas fa-tasks" style="color:#3B82F6;"></i> Suivi & Registre</div>
            
            <div class="form-group">
                <label class="form-label">Numéro d'ordre / Réf. Externe</label>
                <input type="text" name="numero_ordre" class="form-control" placeholder="N° d'enregistrement">
            </div>

            <div class="form-group">
                <label class="form-label">Date du Courrier</label>
                <input type="date" name="date_courrier" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Date de Réception / Envoi</label>
                <input type="date" name="date_reception" class="form-control">
            </div>

            <div class="row">
                <div class="col form-group">
                    <label class="form-label">Urgence</label>
                    <select name="urgence" class="form-control">
                        <option value="normal">Normale</option>
                        <option value="urgent">Urgente</option>
                        <option value="tres_urgent">Très Urgente</option>
                    </select>
                </div>
                <div class="col form-group">
                    <label class="form-label">Pièces jointes</label>
                    <input type="number" name="nombre_pieces" class="form-control" value="0" min="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">N° Archives</label>
                <input type="text" name="numero_archives" class="form-control" placeholder="Emplacement d'archivage">
            </div>
        </div>

        <!-- Actions -->
        <div class="stagger-3" style="grid-column: span 12; display:flex; justify-content:flex-end; gap:16px;">
            <a href="{{ route('gel-secretary.courriers.index') }}" class="btn-back">Annuler</a>
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer le courrier</button>
        </div>
    </div>
</form>

@endsection
