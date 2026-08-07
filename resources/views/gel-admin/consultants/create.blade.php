@extends('layouts.gel-admin')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <a href="{{ route('gel-admin.consultants.index') }}" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 class="h4 fw-bold mb-0">Inviter un Consultant</h1>
    <p class="text-muted small mt-1">Le consultant recevra un email avec un lien d'accès à usage unique (valable 72h).</p>
  </div>
</div>

@if($errors->any())
  <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
@endif

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <form action="{{ route('gel-admin.consultants.store') }}" method="POST">
          @csrf

          <div class="mb-3">
            <label class="form-label fw-semibold">Email du consultant *</label>
            <input type="email" name="email" class="form-control" required
                   placeholder="consultant@exemple.com" value="{{ old('email') }}">
            <div class="form-text">Si le consultant n'a pas de compte, il devra en créer un en suivant le lien.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Intitulé du dossier *</label>
            <input type="text" name="title" class="form-control" required
                   placeholder="Ex : Révision du contrat de bail commercial" value="{{ old('title') }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Spécialité requise *</label>
            <select name="specialty" class="form-select" required>
              <option value="">-- Choisissez une spécialité --</option>
              <option value="Juriste"       {{ old('specialty') === 'Juriste' ? 'selected' : '' }}>Juriste</option>
              <option value="Fiscaliste"    {{ old('specialty') === 'Fiscaliste' ? 'selected' : '' }}>Fiscaliste</option>
              <option value="Expert RH"     {{ old('specialty') === 'Expert RH' ? 'selected' : '' }}>Expert RH</option>
              <option value="Informaticien" {{ old('specialty') === 'Informaticien' ? 'selected' : '' }}>Informaticien</option>
              <option value="Gestion"       {{ old('specialty') === 'Gestion' ? 'selected' : '' }}>Gestion / Conseil</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Périmètre précis confié *</label>
            <textarea name="description" class="form-control" rows="4" required
              placeholder="Décrivez précisément ce que le consultant peut consulter et faire. Ex : Révision et analyse du contrat de bail numéro X. Le consultant n'a accès qu'à ce dossier, pas à la comptabilité générale.">{{ old('description') }}</textarea>
          </div>

          <div class="row">
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label fw-semibold">Date de début</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}">
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label fw-semibold">Date de fin d'accès *</label>
                <input type="date" name="end_date" class="form-control" required
                       value="{{ old('end_date') }}"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                <div class="form-text text-danger fw-semibold">L'accès sera révoqué automatiquement à cette date.</div>
              </div>
            </div>
          </div>

          <div class="alert alert-warning border-0 py-2 px-3" style="font-size:12px;">
            <i class="fas fa-shield-alt me-1"></i>
            <strong>Rappel sécurité :</strong> Le consultant n'aura accès qu'au périmètre décrit ci-dessus.
            Il ne pourra pas consulter la comptabilité, le secrétariat ou d'autres dossiers non listés.
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('gel-admin.consultants.index') }}" class="btn btn-light">Annuler</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane me-1"></i> Envoyer l'invitation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
