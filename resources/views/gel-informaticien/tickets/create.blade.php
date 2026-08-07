@extends('layouts.gel-informaticien')

@section('title', 'Nouveau Ticket IT')

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Nouveau Ticket d'Assistance</h1>
            <a href="{{ route('gel-informaticien.tickets.index') }}" class="btn btn-sm btn-light mt-2"><i class="fas fa-arrow-left"></i> Retour aux tickets</a>
        </div>
    </div>

    <div class="row g-4 animate-fade delay-1">
        <div class="col-lg-8">
            <div class="pro-panel mb-4" style="border-top: 4px solid var(--sec-warning);">
                <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:var(--sec-warning);"><i class="fas fa-ticket-alt me-2"></i> Création de Ticket IT</h5>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <form action="{{ route('gel-informaticien.tickets.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Client (Optionnel)</label>
                            <select name="client_id" class="form-select">
                                <option value="">-- Aucun / Ticket interne --</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Sujet / Résumé du problème</label>
                            <input type="text" name="subject" class="form-control" required placeholder="Ex: Panne de connexion VPN">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Niveau de priorité</label>
                            <select name="priority" class="form-select" required>
                                <option value="basse">Basse</option>
                                <option value="normale" selected>Normale</option>
                                <option value="haute">Haute</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Détails de la demande</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Description complète..."></textarea>
                        </div>

                        <button type="submit" class="sec-btn" style="background:var(--sec-primary); color:white; font-weight:600;"><i class="fas fa-save me-1"></i> Créer le ticket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
