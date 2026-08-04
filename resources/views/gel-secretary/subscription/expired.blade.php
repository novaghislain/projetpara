<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GEL Secrétariat — Abonnement Expiré</title>
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
    .expired-container {
      max-width: 500px;
      width: 100%;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 25px rgba(0,0,0,0.08);
      overflow: hidden;
      text-align: center;
    }
    .expired-header {
      background: #EF4444;
      color: white;
      padding: 40px 30px;
    }
    .expired-header i {
      font-size: 48px;
      margin-bottom: 15px;
    }
    .expired-header h1 {
      font-size: 24px;
      font-weight: 700;
      margin: 0;
    }
    .expired-body { padding: 40px 30px; }
    .expired-body p {
      font-size: 15px;
      color: #6B7280;
      line-height: 1.6;
      margin-bottom: 25px;
    }
    .expired-body p strong {
      color: #163A5E;
    }
    .price-tag {
      font-size: 32px;
      font-weight: 800;
      color: #163A5E;
      margin-bottom: 5px;
    }
    .price-tag span {
      font-size: 14px;
      font-weight: 500;
      color: #6B7280;
    }
    .btn-pay {
      background: #FF7900;
      color: white;
      border: none;
      padding: 14px 20px;
      font-weight: 700;
      font-size: 15px;
      border-radius: 8px;
      width: 100%;
      transition: background 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 15px;
    }
    .btn-pay:hover { background: #E66A00; color: white; }
    .momo-icons img {
      height: 24px;
      margin: 0 5px;
      border-radius: 4px;
    }
    .logout-link {
      color: #6B7280;
      font-size: 13px;
      text-decoration: underline;
    }
    .logout-link:hover { color: #163A5E; }
  </style>
</head>
<body>

<div class="expired-container">
  <div class="expired-header">
    <i class="fas fa-lock"></i>
    <h1>Votre période d'essai est terminée</h1>
  </div>
  
  <div class="expired-body">
    <p>
      Bonjour <strong>{{ $user->name }}</strong>, votre essai gratuit de 30 jours pour l'espace personnel autonome GEL Secrétariat a expiré. 
      <br><br>
      Vos données sont <strong>conservées et sécurisées</strong>. Pour retrouver l'accès à votre agenda, vos tâches et documents, veuillez activer votre abonnement.
    </p>

    <div style="background: #F8FAFC; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
      <div class="price-tag">
        10 000 FCFA <span>/ mois</span>
      </div>
      <div style="font-size: 12px; color: #10B981; font-weight: 600;">
        <i class="fas fa-check-circle"></i> Sans engagement
      </div>
    </div>

    <!-- TODO: Intégrer l'API Mobile Money -->
    <button class="btn btn-pay" onclick="alert('Intégration Mobile Money à valider avec l\'admin')">
      <i class="fas fa-mobile-alt"></i> Payer par Mobile Money
    </button>
    <div class="momo-icons mb-4">
      <!-- Faux emplacements pour logos MTN MoMo / Moov -->
      <span style="background:#FFCC00; color:#000; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;">MTN MoMo</span>
      <span style="background:#005A9E; color:#FFF; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;">Moov Money</span>
    </div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-link logout-link p-0 border-0">Se déconnecter pour le moment</button>
    </form>
  </div>
</div>

</body>
</html>
