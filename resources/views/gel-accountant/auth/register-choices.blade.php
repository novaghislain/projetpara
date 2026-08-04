<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GEL Accountant — Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="GEL SABINET Accountant — Choisissez votre mode d'accès : rejoindre une entreprise ou créer votre espace comptable indépendant.">
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
    .auth-container { max-width: 820px; width: 100%; }
    .brand { text-align: center; margin-bottom: 40px; }
    .brand h1 { font-weight: 700; color: #163A5E; font-size: 28px; }
    .brand h1 span { color: #10B981; }
    .brand p { color: #6B7280; font-size: 15px; margin-top: 5px; }
    .choice-card {
      background: #ffffff;
      border: 2px solid transparent;
      border-radius: 12px;
      padding: 30px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      height: 100%;
      text-decoration: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .choice-card:hover {
      border-color: #10B981;
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
    }
    .choice-icon {
      width: 70px; height: 70px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 28px; margin-bottom: 20px;
    }
    .choice-title { font-size: 18px; font-weight: 700; color: #163A5E; margin-bottom: 12px; }
    .choice-desc { font-size: 14px; color: #6B7280; line-height: 1.5; margin-bottom: 20px; flex-grow: 1; }
    .choice-btn {
      display: inline-block; padding: 10px 24px;
      background: #F3F4F6; color: #163A5E;
      border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.2s;
    }
    .choice-card.primary .choice-btn { background: #10B981; color: white; }
    .choice-card:hover .choice-btn { background: #163A5E; color: white; }
    .choice-card.primary:hover .choice-btn { background: #059669; color: white; }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="brand">
    <h1>GEL <span>Accountant</span></h1>
    <p>Bienvenue ! Comment souhaitez-vous utiliser le portail comptable ?</p>
  </div>

  <div class="row g-4">
    <!-- Carte A : Invitation par une entreprise (Modèle 1) -->
    <div class="col-md-6">
      <a href="/login" id="choice-invitation" class="choice-card">
        <div class="choice-icon" style="background:#EFF6FF; color:#3B82F6;">
          <i class="fas fa-building"></i>
        </div>
        <div class="choice-title">Je rejoins une entreprise</div>
        <div class="choice-desc">
          Une entreprise ou un cabinet m'a invité(e) par e-mail pour gérer sa comptabilité sur GEL. Je possède un lien d'invitation ou je souhaite me connecter à mon compte existant.
        </div>
        <div class="choice-btn">Se connecter avec mon invitation</div>
      </a>
    </div>

    <!-- Carte B : Comptable indépendant (Modèle 3B) -->
    <div class="col-md-6">
      <a href="{{ route('gel-accountant.register.autonomous') }}" id="choice-autonome" class="choice-card primary">
        <div class="choice-icon" style="background:#D1FAE5; color:#10B981;">
          <i class="fas fa-calculator"></i>
        </div>
        <div class="choice-title">J'utilise GEL-ACCOUNTANT pour moi-même</div>
        <div class="choice-desc">
          Je suis comptable indépendant(e) et je gère mes propres clients (plusieurs entreprises). Je souhaite organiser leur comptabilité depuis un espace personnel centralisé.
        </div>
        <div class="choice-btn">Essayer gratuitement (30 jours)</div>
      </a>
    </div>
  </div>

  <div class="text-center mt-4">
    <p class="text-muted" style="font-size: 13px;">
      Déjà un compte ? <a href="/login" class="text-decoration-none fw-semibold" style="color:#10B981;">Se connecter</a>
    </p>
  </div>
</div>

</body>
</html>
