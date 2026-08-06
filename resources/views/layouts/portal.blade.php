<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace Client')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --portal-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --portal-bg: #F9FAFB;
            --portal-card-bg: #FFFFFF;
            --portal-text-primary: #111827;
            --portal-text-secondary: #4B5563;
            --portal-text-muted: #9CA3AF;
            --portal-border: #E5E7EB;
            --portal-accent: #0F172A;
            --portal-accent-hover: #1E293B;
            --portal-focus: rgba(15, 23, 42, 0.2);
            --portal-success: #059669;
            --portal-danger: #DC2626;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: var(--portal-font);
            background-color: var(--portal-bg);
            color: var(--portal-text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .portal-navbar {
            background-color: var(--portal-card-bg);
            border-bottom: 1px solid var(--portal-border);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .portal-logo {
            font-size: 18px;
            font-weight: 700;
            color: var(--portal-text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .portal-logo img {
            max-height: 32px;
        }

        .portal-nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .portal-nav-link {
            color: var(--portal-text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.15s;
        }
        
        .portal-nav-link:hover, .portal-nav-link.active {
            color: var(--portal-text-primary);
        }

        /* User Menu */
        .portal-user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }
        
        .portal-avatar {
            width: 36px;
            height: 36px;
            background-color: var(--portal-accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* Layout Main */
        .portal-main {
            flex: 1;
            padding: 40px 32px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Typography */
        h1.portal-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        p.portal-subtitle {
            font-size: 15px;
            color: var(--portal-text-secondary);
            margin-bottom: 32px;
        }

        /* Card */
        .portal-card {
            background: var(--portal-card-bg);
            border: 1px solid var(--portal-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 24px;
            margin-bottom: 24px;
        }

        /* Forms */
        .portal-form-group {
            margin-bottom: 20px;
        }

        .portal-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--portal-text-primary);
        }

        .portal-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--portal-border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: var(--portal-text-primary);
            transition: all 0.2s;
        }

        .portal-input:focus {
            outline: none;
            border-color: var(--portal-accent);
            box-shadow: 0 0 0 3px var(--portal-focus);
        }

        /* Buttons */
        .portal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .portal-btn-primary {
            background-color: var(--portal-accent);
            color: white;
        }
        
        .portal-btn-primary:hover {
            background-color: var(--portal-accent-hover);
        }

        .portal-btn-secondary {
            background-color: white;
            color: var(--portal-text-primary);
            border: 1px solid var(--portal-border);
        }
        
        .portal-btn-secondary:hover {
            background-color: var(--portal-bg);
        }

        /* Alerts */
        .portal-alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 24px;
        }
        
        .portal-alert-danger {
            background-color: #FEF2F2;
            color: var(--portal-danger);
            border: 1px solid #FCA5A5;
        }
        
        .portal-alert-success {
            background-color: #ECFDF5;
            color: var(--portal-success);
            border: 1px solid #6EE7B7;
        }

        /* Utilities */
        .portal-text-center { text-align: center; }
        .portal-mt-4 { margin-top: 1rem; }
        .portal-mb-6 { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    @hasSection('auth-layout')
        {{-- Layout spécifique pour login/register --}}
        @yield('content')
    @else
        {{-- Layout standard avec navbar --}}
        <header class="portal-navbar">
            <a href="{{ route('portal.dashboard', ['slug' => $slug]) }}" class="portal-logo">
                @if(isset($client) && $client->logo)
                    <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->company_name }}">
                @endif
                {{ $client->company_name ?? 'Espace Client' }}
            </a>
            
            <nav class="portal-nav-links">
                <a href="{{ route('portal.dashboard', ['slug' => $slug]) }}" class="portal-nav-link active">Tableau de bord</a>
                <a href="{{ route('portal.invoices', ['slug' => $slug]) }}" class="portal-nav-link">Factures</a>
                <a href="#" class="portal-nav-link">Documents</a>
                <a href="{{ route('portal.messages', ['slug' => $slug]) }}" class="portal-nav-link">Messagerie</a>
                <a href="#" onclick="document.getElementById('itSupportModal').showModal()" class="portal-nav-link text-danger" style="font-weight: 600;"><i class="fas fa-life-ring"></i> Support Technique</a>
            </nav>
            
            <div class="portal-user-menu">
                <a href="{{ route('portal.profile', ['slug' => $slug]) }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;">
                    <div class="portal-avatar">
                        {{ strtoupper(substr(auth('portal')->user()->first_name, 0, 1) . substr(auth('portal')->user()->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:14px; font-weight:600;">{{ auth('portal')->user()->first_name }} {{ auth('portal')->user()->last_name }}</div>
                        <form action="{{ route('portal.logout', ['slug' => $slug]) }}" method="POST" style="display: inline;" onsubmit="event.stopPropagation();">
                            @csrf
                            <button type="submit" style="background:none; border:none; color:var(--portal-text-secondary); font-size:12px; cursor:pointer;">Déconnexion</button>
                        </form>
                    </div>
                </a>
            </div>
        </header>

        <main class="portal-main">
            @yield('content')
        </main>
        
        <!-- Modal Support Technique -->
        <dialog id="itSupportModal" style="padding: 24px; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-width: 500px; width: 100%; margin: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 18px; font-weight: 600; margin: 0;">Support Technique GEL SABINET</h3>
                <button onclick="document.getElementById('itSupportModal').close()" style="background: none; border: none; cursor: pointer; font-size: 18px; color: #666;"><i class="fas fa-times"></i></button>
            </div>
            <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Veuillez décrire le problème technique rencontré. Notre équipe informatique prendra en charge votre demande.</p>
            
            <form action="{{ route('portal.it-support.store', ['slug' => $slug]) }}" method="POST">
                @csrf
                <div class="portal-form-group">
                    <label class="portal-label">Sujet / Problème</label>
                    <input type="text" name="subject" class="portal-input" required placeholder="Ex: Impossible de télécharger une facture">
                </div>
                <div class="portal-form-group">
                    <label class="portal-label">Description détaillée</label>
                    <textarea name="message" class="portal-input" rows="4" required placeholder="Décrivez les étapes pour reproduire le problème..."></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                    <button type="button" class="portal-btn portal-btn-secondary" onclick="document.getElementById('itSupportModal').close()">Annuler</button>
                    <button type="submit" class="portal-btn portal-btn-primary"><i class="fas fa-paper-plane"></i> Envoyer le ticket</button>
                </div>
            </form>
        </dialog>
    @endif
</body>
</html>
