<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invitation Consultant — GEL SABINET</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #0f1117; color: #e2e8f0; min-height: 100vh;
           display: flex; align-items: center; justify-content: center; padding: 24px; }
    .card {
      background: #1a1d27; border: 1px solid rgba(255,255,255,.07);
      border-radius: 16px; padding: 40px; max-width: 520px; width: 100%;
    }
    .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; }
    .logo-icon { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg,#7c5cfc,#a78bfa);
                 display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
    .logo-text { font-size: 16px; font-weight: 700; }
    .logo-sub  { font-size: 12px; color: #64748b; }
    h1 { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
    .subtitle { font-size: 13px; color: #64748b; margin-bottom: 24px; line-height: 1.6; }
    .mission-box {
      background: #22263a; border: 1px solid rgba(124,92,252,.25); border-radius: 10px;
      padding: 16px; margin-bottom: 24px;
    }
    .mission-box .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a78bfa; margin-bottom: 4px; }
    .mission-box .value { font-size: 14px; font-weight: 600; color: #e2e8f0; }
    .mission-box .sub   { font-size: 12px; color: #64748b; margin-top: 2px; }
    label { font-size: 12px; font-weight: 600; display: block; margin-bottom: 5px; }
    input {
      width: 100%; padding: 10px 14px; background: #22263a;
      border: 1px solid rgba(255,255,255,.07); border-radius: 8px;
      color: #e2e8f0; font-size: 13px; margin-bottom: 14px;
      font-family: inherit;
    }
    input:focus { outline: none; border-color: #7c5cfc; }
    .btn {
      width: 100%; padding: 12px; background: linear-gradient(135deg,#7c5cfc,#a78bfa);
      border: none; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 600;
      cursor: pointer; font-family: inherit; margin-top: 4px;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn:hover { opacity: .85; }
    .note { font-size: 11px; color: #64748b; text-align: center; margin-top: 16px; }
    .already { font-size: 12px; color: #a78bfa; margin-bottom: 14px; background: rgba(124,92,252,.1);
               padding: 10px 14px; border-radius: 8px; display: flex; align-items:center; gap:8px; }
    .error-msg { font-size: 12px; color: #fca5a5; background: rgba(239,68,68,.1);
                 border: 1px solid rgba(239,68,68,.2); padding: 10px 14px; border-radius: 8px; margin-bottom: 14px; }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">
      <div class="logo-icon"><i class="fas fa-user-tie"></i></div>
      <div>
        <div class="logo-text">GEL SABINET</div>
        <div class="logo-sub">Portail Consultant</div>
      </div>
    </div>

    <h1>Vous avez été invité(e)</h1>
    <p class="subtitle">
      Vous avez reçu une invitation pour intervenir en tant que consultant sur le dossier suivant.
      Cet accès est <strong>limité et temporaire</strong>.
    </p>

    <!-- Mission Box -->
    <div class="mission-box">
      <div class="label">Dossier</div>
      <div class="value">{{ $invitation->mission->title }}</div>
      <div class="sub">
        <i class="fas fa-building" style="margin-right:4px;"></i>
        {{ $invitation->mission->entreprise?->nom ?? 'GEL SABINET' }}
      </div>
      <div style="margin-top:10px;">
        <div class="label">Spécialité</div>
        <div class="value">{{ $invitation->mission->specialty }}</div>
      </div>
      <div style="margin-top:10px;">
        <div class="label">Votre accès expire le</div>
        <div class="value" style="color:#f59e0b;">{{ $invitation->mission->end_date->format('d/m/Y') }}</div>
      </div>
    </div>

    @if($errors->any())
      <div class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    @if($userExists)
      <div class="already">
        <i class="fas fa-check-circle" style="color:#10b981;"></i>
        Un compte existe déjà avec l'adresse <strong>{{ $invitation->email }}</strong>. Vous serez connecté automatiquement.
      </div>
      <form action="{{ route('consultant.invitation.accept', $invitation->token) }}" method="POST">
        @csrf
        <button type="submit" class="btn">
          <i class="fas fa-door-open"></i> Accepter et accéder au dossier
        </button>
      </form>
    @else
      <p style="font-size:13px; color:#64748b; margin-bottom:16px;">Créez votre compte pour accepter cette invitation :</p>
      <form action="{{ route('consultant.invitation.accept', $invitation->token) }}" method="POST">
        @csrf
        <label>Votre nom complet *</label>
        <input type="text" name="name" required placeholder="Prénom Nom" value="{{ old('name') }}">
        <label>Mot de passe *</label>
        <input type="password" name="password" required placeholder="Minimum 8 caractères">
        <label>Confirmer le mot de passe *</label>
        <input type="password" name="password_confirmation" required placeholder="Répétez le mot de passe">
        <button type="submit" class="btn">
          <i class="fas fa-user-plus"></i> Créer mon compte et accéder au dossier
        </button>
      </form>
    @endif

    <p class="note">Ce lien est à usage unique et expire le {{ $invitation->expires_at->format('d/m/Y à H:i') }}.</p>
  </div>
</body>
</html>
