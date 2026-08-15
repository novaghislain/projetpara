<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GEL Juridique — Inscription Autonome</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #F1F3F6;
      color: #1a2938;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .auth-container {
      max-width: 600px;
      width: 100%;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      overflow: hidden;
    }
    .auth-header {
      background: #163A5E;
      color: white;
      padding: 30px;
      text-align: center;
    }
    .auth-header h1 {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .auth-header h1 span { color: #FF7900; }
    .auth-header p { font-size: 14px; opacity: 0.8; margin: 0; }
    .auth-body { padding: 30px; }
    .form-group label {
      font-size: 13px;
      font-weight: 600;
      color: #163A5E;
      margin-bottom: 6px;
    }
    .form-control, .form-select {
      font-size: 14px;
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #E5E7EB;
    }
    .form-control:focus, .form-select:focus {
      border-color: #FF7900;
      box-shadow: 0 0 0 3px rgba(255,121,0,0.1);
    }
    .btn-primary {
      background: #FF7900;
      border: none;
      padding: 12px;
      font-weight: 600;
      border-radius: 8px;
      transition: background 0.2s;
    }
    .btn-primary:hover { background: #E66A00; }
    .intent-radio { display: none; }
    .intent-label {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border: 1px solid #E5E7EB;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .intent-label:hover { background: #F8FAFC; }
    .intent-radio:checked + .intent-label {
      border-color: #FF7900;
      background: #FFF3E0;
      color: #E66A00;
      font-weight: 600;
    }
    .intent-radio:checked + .intent-label i { color: #FF7900; }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="auth-header">
    <h1>GEL <span>Juridique</span></h1>
    <p>Création de votre espace personnel autonome</p>
  </div>
  <div class="auth-body">
    <form action="{{ route('gel-legal.register.autonomous.submit') }}" method="POST">
      @csrf
      
      @if($errors->any())
        <div class="alert alert-danger" style="font-size: 13px;">
          <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      <h5 style="font-size:15px; font-weight:700; color:#163A5E; margin-bottom:15px;">Étape 1 : Vos informations</h5>
      
      <div class="row g-3 mb-3">
        <div class="col-md-6 form-group">
          <label>Nom complet</label>
          <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6 form-group">
          <label>E-mail</label>
          <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6 form-group">
          <label>Mot de passe</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-md-6 form-group">
          <label>Confirmer le mot de passe</label>
          <input type="password" name="password_confirmation" class="form-control" required>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6 form-group">
          <label>Nom de votre entreprise (Optionnel)</label>
          <input type="text" name="personal_company_name" class="form-control" value="{{ old('personal_company_name') }}" placeholder="Ex: Cabinet ABC">
          <small class="text-muted" style="font-size: 11px;">Affiché à titre informatif uniquement.</small>
        </div>
        <div class="col-md-6 form-group">
          <label>Secteur d'activité</label>
          <select name="personal_industry" class="form-select">
            <option value="">Sélectionner...</option>
            <option value="Juridique">Juridique</option>
            <option value="Médical">Médical</option>
            <option value="Consulting">Consulting</option>
            <option value="Autre">Autre</option>
          </select>
        </div>
      </div>

      <hr style="opacity: 0.1; margin: 25px 0;">

      <h5 style="font-size:15px; font-weight:700; color:#163A5E; margin-bottom:15px;">Étape 2 : Qu'aimeriez-vous organiser en premier ?</h5>
      
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <input type="radio" name="primary_intent" id="intent-tasks" value="tasks" class="intent-radio" checked>
          <label for="intent-tasks" class="intent-label">
            <i class="fas fa-tasks text-muted"></i> Mes Tâches
          </label>
        </div>
        <div class="col-md-6">
          <input type="radio" name="primary_intent" id="intent-agenda" value="agenda" class="intent-radio">
          <label for="intent-agenda" class="intent-label">
            <i class="fas fa-calendar-alt text-muted"></i> Mon Agenda
          </label>
        </div>
        <div class="col-md-6">
          <input type="radio" name="primary_intent" id="intent-contacts" value="contacts" class="intent-radio">
          <label for="intent-contacts" class="intent-label">
            <i class="fas fa-address-book text-muted"></i> Mes Contacts
          </label>
        </div>
        <div class="col-md-6">
          <input type="radio" name="primary_intent" id="intent-documents" value="documents" class="intent-radio">
          <label for="intent-documents" class="intent-label">
            <i class="fas fa-folder-open text-muted"></i> Mes Documents
          </label>
        </div>
      </div>

      <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
        <label class="form-check-label text-muted" for="terms" style="font-size: 12px;">
          J'accepte les conditions d'utilisation et je comprends que je bénéficie de 30 jours d'essai gratuit sans engagement.
        </label>
      </div>

      <button type="submit" class="btn btn-primary w-100">Démarrer mon essai gratuit (30 j)</button>
      
      <div class="text-center mt-3" style="font-size: 13px;">
        <a href="{{ route('gel-legal.register.choices') }}" style="color: #6B7280; text-decoration: none;"><i class="fas fa-arrow-left"></i> Retour au choix</a>
      </div>

    </form>
  </div>
</div>

</body>
</html>
