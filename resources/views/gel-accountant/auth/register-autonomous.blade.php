<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GEL Accountant — Inscription Autonome (Comptable Indépendant)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Créez votre espace comptable indépendant GEL SABINET. Essai gratuit 30 jours.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #F1F3F6;
      color: #1a2938;
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 20px;
    }
    .auth-container {
      max-width: 600px; width: 100%;
      background: #ffffff; border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden;
    }
    .auth-header { background: #163A5E; color: white; padding: 30px; text-align: center; }
    .auth-header h1 { font-size: 24px; font-weight: 700; margin-bottom: 5px; }
    .auth-header h1 span { color: #10B981; }
    .auth-header p { font-size: 14px; opacity: 0.8; margin: 0; }
    .auth-body { padding: 30px; }
    .form-group label { font-size: 13px; font-weight: 600; color: #163A5E; margin-bottom: 6px; }
    .form-control, .form-select {
      font-size: 14px; padding: 10px 14px; border-radius: 8px; border: 1px solid #E5E7EB;
    }
    .form-control:focus, .form-select:focus {
      border-color: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    .btn-primary {
      background: #10B981; border: none; padding: 12px;
      border-radius: 8px; font-weight: 600; font-size: 15px;
      width: 100%; cursor: pointer; transition: background 0.2s;
    }
    .btn-primary:hover { background: #059669; }
    .trial-badge {
      background: #D1FAE5; color: #065F46;
      border-radius: 8px; padding: 12px 16px;
      font-size: 13px; margin-bottom: 24px;
      display: flex; align-items: center; gap: 10px;
    }
    .plan-info {
      background: #F0F9FF; border: 1px solid #BAE6FD;
      border-radius: 8px; padding: 14px;
      font-size: 13px; color: #0C4A6E; margin-bottom: 24px;
    }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="auth-header">
    <h1>GEL <span>Accountant</span></h1>
    <p>Créez votre espace comptable indépendant</p>
  </div>

  <div class="auth-body">

    <div class="trial-badge">
      <i class="fas fa-gift fa-lg"></i>
      <div>
        <strong>30 jours d'essai gratuit</strong><br>
        Aucune carte bancaire requise. Accès complet pendant 30 jours.
      </div>
    </div>

    @if($plans->isEmpty())
    <div class="plan-info">
      <i class="fas fa-info-circle me-2"></i>
      Les forfaits pour comptable indépendant seront disponibles prochainement.
      Votre essai gratuit de 30 jours commence dès aujourd'hui — sans engagement.
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('gel-accountant.register.autonomous.store') }}" novalidate>
      @csrf

      <div class="form-group mb-3">
        <label for="name">Votre nom complet <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name') }}" placeholder="Ex. Jean-Baptiste KOFFI" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group mb-3">
        <label for="email">Adresse e-mail professionnelle <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" placeholder="votre@email.com" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group mb-3">
        <label for="password">Mot de passe <span class="text-danger">*</span></label>
        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Minimum 8 caractères" required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group mb-3">
        <label for="password_confirmation">Confirmer le mot de passe <span class="text-danger">*</span></label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
               placeholder="Répétez votre mot de passe" required>
      </div>

      <div class="form-group mb-3">
        <label for="personal_company_name">Nom de votre cabinet / activité (optionnel)</label>
        <input type="text" id="personal_company_name" name="personal_company_name" class="form-control"
               value="{{ old('personal_company_name') }}" placeholder="Ex. Cabinet KOFFI Comptabilité">
      </div>

      <div class="form-group mb-4">
        <label for="specialite">Votre spécialité principale (optionnel)</label>
        <select id="specialite" name="specialite" class="form-select">
          <option value="">-- Sélectionner une spécialité --</option>
          <option value="comptabilite_generale" {{ old('specialite') === 'comptabilite_generale' ? 'selected' : '' }}>Comptabilité générale</option>
          <option value="fiscalite" {{ old('specialite') === 'fiscalite' ? 'selected' : '' }}>Fiscalité & Déclarations</option>
          <option value="audit" {{ old('specialite') === 'audit' ? 'selected' : '' }}>Audit & Contrôle</option>
          <option value="paie" {{ old('specialite') === 'paie' ? 'selected' : '' }}>Paie & RH</option>
          <option value="conseil" {{ old('specialite') === 'conseil' ? 'selected' : '' }}>Conseil & Expertise</option>
          <option value="autre" {{ old('specialite') === 'autre' ? 'selected' : '' }}>Autre</option>
        </select>
      </div>

      {{-- Sélection du plan (si disponibles) - profile_type = 'comptable_independant' uniquement --}}
      @if($plans->isNotEmpty())
      <div class="form-group mb-4">
        <label>Forfait souhaité après l'essai gratuit</label>
        @foreach($plans as $plan)
        <div class="form-check border rounded p-3 mb-2">
          <input class="form-check-input" type="radio" name="plan_id" id="plan_{{ $plan->id }}" value="{{ $plan->id }}"
                 {{ old('plan_id') == $plan->id ? 'checked' : '' }}>
          <label class="form-check-label w-100" for="plan_{{ $plan->id }}">
            <strong>{{ $plan->name }}</strong>
            @if($plan->price > 0)
            — <span class="text-success">{{ number_format($plan->price, 0, ',', ' ') }} FCFA / mois</span>
            @else
            — <span class="text-muted">Tarif à définir</span>
            @endif
            @if($plan->description)
            <br><small class="text-muted">{{ $plan->description }}</small>
            @endif
          </label>
        </div>
        @endforeach
        <small class="text-muted">Aucun paiement n'est requis pendant votre essai de 30 jours.</small>
      </div>
      @else
      <input type="hidden" name="plan_id" value="">
      @endif

      <div class="form-check mb-4">
        <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
               id="terms" name="terms" required>
        <label class="form-check-label" for="terms" style="font-size: 13px;">
          J'accepte les <a href="#" class="text-decoration-none" style="color:#10B981;">Conditions d'utilisation</a>
          et la <a href="#" class="text-decoration-none" style="color:#10B981;">Politique de confidentialité</a>.
        </label>
        @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn btn-primary" id="submit-register">
        <i class="fas fa-rocket me-2"></i> Créer mon espace comptable gratuit
      </button>

      <div class="text-center mt-3">
        <a href="{{ route('gel-accountant.register.choices') }}" class="text-muted text-decoration-none" style="font-size: 13px;">
          ← Retour au choix d'inscription
        </a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
