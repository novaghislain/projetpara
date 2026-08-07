<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accès Expiré — GEL SABINET</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #0f1117; color: #e2e8f0;
           min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 24px; }
    .box { max-width: 420px; }
    .icon { font-size: 56px; color: #ef4444; margin-bottom: 20px; }
    h1 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
    p  { font-size: 14px; color: #64748b; line-height: 1.7; margin-bottom: 24px; }
    a  { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px;
         background: #1a1d27; border: 1px solid rgba(255,255,255,.08); border-radius: 8px;
         color: #e2e8f0; font-size: 13px; font-weight: 600; text-decoration: none; }
    a:hover { background: #22263a; }
  </style>
</head>
<body>
  <div class="box">
    <div class="icon"><i class="fas fa-lock"></i></div>
    <h1>Accès Expiré ou Révoqué</h1>
    <p>
      Votre accès à ce dossier a expiré ou a été révoqué par l'administrateur.<br>
      Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur de l'entreprise concernée.
    </p>
    <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Retour à la page de connexion</a>
  </div>
</body>
</html>
