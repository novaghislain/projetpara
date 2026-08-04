@extends('layouts.portal')

@section('title', 'Soumettre un Ticket')

@section('content')

<div class="portal-page-header portal-mb-6">
    <h1 class="portal-title"><i class="fas fa-file-alt" style="color:var(--portal-accent); margin-right:8px;"></i> Soumettre un Ticket</h1>
    <p class="portal-subtitle">Rédigez une demande spécifique pour votre cabinet comptable.</p>
</div>

<div class="portal-card" style="max-width: 700px; margin: 0 auto; padding: 40px;">
    <form action="{{ route('portal.tickets.store', ['slug' => request()->route('slug')]) }}" method="POST">
        @csrf
        
        <div class="portal-form-group">
            <label for="sujet" class="portal-label">Sujet de la demande *</label>
            <input type="text" id="sujet" name="sujet" class="portal-input" placeholder="Ex: Demande d'attestation de régularité fiscale" required>
            @error('sujet') <span style="color: var(--portal-danger); font-size: 12px;">{{ $message }}</span> @enderror
        </div>
        
        <div class="portal-form-group">
            <label for="priorite" class="portal-label">Niveau d'urgence</label>
            <select id="priorite" name="priorite" class="portal-input">
                <option value="basse">Normale</option>
                <option value="moyenne">Importante</option>
                <option value="haute">Urgente</option>
            </select>
            @error('priorite') <span style="color: var(--portal-danger); font-size: 12px;">{{ $message }}</span> @enderror
        </div>
        
        <div class="portal-form-group">
            <label for="description" class="portal-label">Description détaillée *</label>
            <textarea id="description" name="description" class="portal-input" rows="8" placeholder="Expliquez votre besoin en détail..." required></textarea>
            @error('description') <span style="color: var(--portal-danger); font-size: 12px;">{{ $message }}</span> @enderror
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
            <a href="{{ route('portal.messages', ['slug' => request()->route('slug')]) }}" class="portal-btn portal-btn-secondary">Annuler</a>
            <button type="submit" class="portal-btn portal-btn-primary">
                <i class="fas fa-paper-plane"></i> Envoyer le ticket
            </button>
        </div>
    </form>
</div>

@endsection
