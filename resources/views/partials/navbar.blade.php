<nav class="gel-navbar" id="gelNavbar">
    <div class="container-fluid">
        <a href="/" class="gel-brand">
            <div class="gel-brand-logo">GEL</div>
            <div class="gel-brand-text">
                <span class="gel-brand-name">GEL Cabinet</span>
                <span class="gel-brand-sub">Gestion Multi-Pôles</span>
            </div>
        </a>

        <ul class="gel-nav-center" id="gelNavCenter">
            <li class="gel-nav-item">
                <a href="/nos-modules" class="gel-nav-link">
                    Nos Modules
                    <i class="bi-chevron-down chevron"></i>
                </a>
                <ul class="gel-dropdown">
                    <li><a href="/nos-modules#module-crm"><span class="drop-icon"><i class="bi-people"></i></span> CRM Clients</a></li>
                    <li><a href="/nos-modules#module-ged"><span class="drop-icon"><i class="bi-folder2-open"></i></span> GED — Documents</a></li>
                    <li><a href="/nos-modules#module-poles"><span class="drop-icon"><i class="bi-diagram-3"></i></span> Pôles & Missions</a></li>
                    <li><hr class="gel-dropdown-divider"></li>
                    <li><a href="/nos-modules#module-compta"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
                    <li><a href="/nos-modules#module-erp"><span class="drop-icon"><i class="bi-box-seam"></i></span> ERP Intégré</a></li>
                </ul>
            </li>
            <li class="gel-nav-item">
                <a href="/services" class="gel-nav-link">
                    Services
                    <i class="bi-chevron-down chevron"></i>
                </a>
                <ul class="gel-dropdown">
                    <li><a href="/services/administration"><span class="drop-icon"><i class="bi-shield-lock"></i></span> Administration</a></li>
                    <li><a href="/services/consultation"><span class="drop-icon"><i class="bi-chat-dots"></i></span> Consultation</a></li>
                    <li><a href="/services/fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Fiscal</a></li>
                    <li><a href="/services/it"><span class="drop-icon"><i class="bi-laptop"></i></span> IT</a></li>
                    <li><a href="/services/social-paie"><span class="drop-icon"><i class="bi-people"></i></span> Social & Paie</a></li>
                    <li><a href="/services/juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Juridique</a></li>
                    <li><hr class="gel-dropdown-divider"></li>
                    <li><a href="/services/erp"><span class="drop-icon"><i class="bi-cpu"></i></span> Logiciel Comptabilité</a></li>
                </ul>
            </li>
            <li class="gel-nav-item">
                <a href="#" class="gel-nav-link">
                    À propos
                    <i class="bi-chevron-down chevron"></i>
                </a>
                <ul class="gel-dropdown">
                    <li><a href="{{ route('notre-cabinet') }}"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
                    <li><a href="{{ route('notre-equipe') }}"><span class="drop-icon"><i class="bi-people-fill"></i></span> Notre Équipe</a></li>
                    <li><a href="{{ route('carrieres') }}"><span class="drop-icon"><i class="bi-briefcase-fill"></i></span> Carrières</a></li>
                </ul>
            </li>
            <li class="gel-nav-item">
                <a href="#" class="gel-nav-link">
                    Ressources
                    <i class="bi-chevron-down chevron"></i>
                </a>
                <ul class="gel-dropdown">
                    <li><a href="/blogue"><span class="drop-icon"><i class="bi-pencil-square"></i></span> Blogue</a></li>
                    <li><a href="/documentation"><span class="drop-icon"><i class="bi-file-text"></i></span> Documentation</a></li>
                    <li><a href="/faq"><span class="drop-icon"><i class="bi-question-circle"></i></span> FAQ</a></li>
                    <li><hr class="gel-dropdown-divider"></li>
                    <li><a href="/centre-aide"><span class="drop-icon"><i class="bi-headset"></i></span> Centre d'aide</a></li>
                </ul>
            </li>
            <li class="gel-nav-item"><a href="/tarifs" class="gel-nav-link">Tarifs</a></li>
            <li class="gel-nav-item"><a href="/contact" class="gel-nav-link">Contact</a></li>
        </ul>

        <div class="gel-nav-right">
            @auth
                @if(auth()->user()->role === 'client')
                    <a href="{{ route('client.orders.index') }}" class="gel-btn-nav gel-btn-nav-outline">
                        <i class="bi-speedometer2"></i> Mon Espace
                    </a>
                @elseif(auth()->user()->client_id)
                    <a href="{{ route('company.dashboard') }}" class="gel-btn-nav gel-btn-nav-outline">
                        <i class="bi-speedometer2"></i> Portail
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="gel-btn-nav gel-btn-nav-outline">
                        <i class="bi-speedometer2"></i> Tableau de bord
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="gel-btn-nav gel-btn-nav-outline" style="color:#dc2626; border-color:#fca5a5;">
                        <i class="bi-box-arrow-right"></i>
                    </button>
                </form>
            @else
                <a href="/register" class="gel-btn-nav gel-btn-nav-outline">
                    <i class="bi-person-plus"></i> S'inscrire
                </a>
                <a href="/login" class="gel-btn-nav gel-btn-nav-primary" id="nav-login-btn">
                    <i class="bi-box-arrow-in-right"></i> Connexion
                </a>
            @endauth
            <button class="gel-toggler" id="gelToggler" aria-label="Menu">
                <i class="bi-list" id="togglerIcon"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu -->
<div class="gel-mobile-menu" id="gelMobileMenu">
    <a href="/" class="gel-mobile-link"><i class="bi-house text-orange me-2"></i>Accueil</a>
    <a href="/nos-modules" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
    <a href="/services" class="gel-mobile-link"><i class="bi-grid-3x3-gap text-orange me-2"></i>Services</a>
    <div style="padding-left:36px;font-size:12px;color:rgba(255,255,255,0.4);margin-bottom:4px;">
        <a href="/services/administration" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">Administration</a>
        <a href="/services/consultation" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">Consultation</a>
        <a href="/services/fiscal" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">Fiscal</a>
        <a href="/services/it" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">IT</a>
        <a href="/services/social-paie" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">Social & Paie</a>
        <a href="/services/juridique" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">Juridique</a>
        <a href="/services/erp" style="color:inherit;text-decoration:none;display:block;padding:4px 0;">Logiciel Comptabilité</a>
    </div>
    <a href="/blogue" class="gel-mobile-link"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
    <a href="/documentation" class="gel-mobile-link"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
    <a href="/faq" class="gel-mobile-link"><i class="bi-question-circle text-orange me-2"></i>FAQ</a>
    <a href="/centre-aide" class="gel-mobile-link"><i class="bi-headset text-orange me-2"></i>Centre d'aide</a>
    <a href="{{ route('notre-cabinet') }}" class="gel-mobile-link"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
    <a href="{{ route('notre-equipe') }}" class="gel-mobile-link"><i class="bi-people-fill text-orange me-2"></i>Notre Équipe</a>
    <a href="{{ route('carrieres') }}" class="gel-mobile-link"><i class="bi-briefcase-fill text-orange me-2"></i>Carrières</a>
    <a href="/tarifs" class="gel-mobile-link"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
    <a href="/contact" class="gel-mobile-link"><i class="bi-envelope text-orange me-2"></i>Contact</a>
    <a href="/login" class="gel-mobile-link"><i class="bi-box-arrow-in-right text-orange me-2"></i>Connexion</a>
    <a href="/register" class="gel-mobile-link"><i class="bi-person-plus text-orange me-2"></i>S'inscrire</a>
</div>
