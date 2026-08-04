/*
 * CpaLogin.vue - Page de connexion pour le portail CPA GEL Cabinet.
 *
 * Role     : Point d'entree authentifie de la plateforme GEL Cabinet.
 *            Interface a deux panneaux : formulaire a gauche, visuel a droite.
 *            Inclut une section "Comptes de demonstration" pour faciliter les tests.
 *            Propose aussi une modale "Mot de passe oublie" avec envoi de lien.
 * Props    : Aucune (composant autonome).
 * Emits    : Aucun (soumission par formulaire HTML natif pour eviter les erreurs CSRF).
 * Store    : Aucun (soumission directe via formulaire cache).
 *
 * Fonctionnalites :
 * - Connexion email + mot de passe avec option "Se souvenir de moi"
 * - Soumission via formulaire HTML cache (approche Laravel standard, evite les erreurs 419 CSRF)
 * - Affichage/masquage du mot de passe (icone oeil)
 * - Section "Comptes de demonstration" : Admin, Client, Comptable, Entreprise
 * - Badge "Espace securise -- Connexion chiffree SSL" rassurant
 * - Modale "Mot de passe oublie" : saisie email + envoi POST /forgot-password
 * - Pied de page avec liens CGU et Confidentialite
 * - Panneau droit hero avec statistiques (500+ clients, 2500+ declarations, 99.9% dispo)
 * - Temoignage client dans le panneau droit
 *
 * Flux type :
 *   1. L'utilisateur saisit email + mot de passe (ou clique sur un compte demo)
 *   2. Clic "Se connecter" : validation cote client (champs non vides)
 *   3. Creation d'un formulaire HTML cache soumis en POST vers /login
 *   4. Le navigateur suit la redirection Laravel (succes ou echec)
 */

<script setup>
import { ref, onMounted } from 'vue';

// ─── Etat du formulaire de connexion ───
const form = ref({
    email: '',
    password: '',
    remember: false,   // Option "Se souvenir de moi"
});

// ─── Etats reactifs ───
const loading = ref(false);         // Indicateur de chargement (desactive le bouton)
const error = ref('');              // Message d'erreur a afficher
const showPassword = ref(false);    // Afficher/masquer le mot de passe (icone oeil)
const showForgotModal = ref(false); // Affichage de la modale "Mot de passe oublie"
const forgotEmail = ref('');        // Email saisi dans la modale de reinitialisation
const forgotLoading = ref(false);   // Etat d'envoi du lien de reinitialisation
const forgotSuccess = ref(false);   // Succes de l'envoi du lien
const forgotError = ref('');        // Erreur lors de l'envoi du lien

/**
 * login - Authentifie l'utilisateur via soumission d'un formulaire HTML cache.
 *
 * Particularite : au lieu d'un appel fetch/ajax, cette fonction cree un formulaire
 * HTML <form> avec des champs caches et le soumet. C'est l'approche recommandee par
 * Laravel pour eviter les erreurs 419 (CSRF token mismatch) qui surviennent souvent
 * avec les requetes AJAX sur les sessions expirees.
 *
 * Apres le submit(), le navigateur quitte la page actuelle et suit la redirection
 * retournee par le serveur Laravel (/dashboard en cas de succes, /login en cas d'echec).
 */
const login = async () => {
    loading.value = true;
    error.value = '';

    // Validation cote client avant soumission
    if (!form.value.email.trim()) {
        error.value = "L'adresse email est requise";
        loading.value = false;
        return;
    }
    if (!form.value.password) {
        error.value = 'Le mot de passe est requis';
        loading.value = false;
        return;
    }

    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;

        // Creation d'un formulaire cache soumis en POST vers /login
        // Cette approche evite les erreurs 419 CSRF des requetes AJAX
        const formEl = document.createElement('form');
        formEl.method = 'POST';
        formEl.action = '/login';

        const fields = {
            _token: csrf,
            email: form.value.email,
            password: form.value.password,
            remember: form.value.remember ? 'on' : '',
        };
        for (const [name, val] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = val;
            formEl.appendChild(input);
        }

        document.body.appendChild(formEl);
        formEl.submit();
        // Apres cette ligne, le navigateur quitte la page (redirection Laravel)
    } catch (e) {
        error.value = 'Erreur reseau. Veuillez reessayer.';
    } finally {
        loading.value = false;
    }
};

/**
 * sendResetLink - Envoie une demande de reinitialisation de mot de passe.
 * Appel POST /forgot-password avec l'email saisi dans la modale.
 * En cas de succes, affiche un message de confirmation dans la modale.
 */
const sendResetLink = async () => {
    forgotLoading.value = true;
    forgotError.value = '';
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ email: forgotEmail.value }),
        });
        if (res.ok) {
            forgotSuccess.value = true;
        } else {
            const data = await res.json();
            forgotError.value = data.message || "Impossible d'envoyer le lien";
        }
    } catch (e) {
        forgotError.value = 'Erreur reseau';
    } finally {
        forgotLoading.value = false;
    }
};

/** closeForgotModal - Reinitialise et ferme la modale de mot de passe oublie */
const closeForgotModal = () => {
    showForgotModal.value = false;
    forgotEmail.value = '';
    forgotSuccess.value = false;
    forgotError.value = '';
};

onMounted(() => {
    document.title = "Connexion | GEL Cabinet";
});
</script>

<template>
    <div class="cpa-auth-page">
        <!-- Left Panel: Form -->
        <div class="cpa-auth-form-panel">
            <div class="cpa-auth-form-inner">
                <!-- Logo -->
                <div class="cpa-auth-logo">
                    <div class="cpa-auth-logo-icon">
                        <i class="bi-gem"></i>
                    </div>
                    <span class="cpa-auth-logo-text">GEL Cabinet</span>
                </div>

                <h1 class="cpa-auth-title">Bienvenue !</h1>
                <p class="cpa-auth-subtitle">Connectez-vous à votre espace client sécurisé</p>

                <!-- Secure badge -->
                <div class="cpa-login-secure-badge">
                    <i class="bi-shield-lock-fill"></i>
                    <span>Espace sécurisé — Connexion chiffrée SSL</span>
                </div>

                <!-- Error Alert -->
                <div v-if="error" class="cpa-auth-alert cpa-auth-alert-danger">
                    <i class="bi-exclamation-triangle-fill me-2"></i>
                    {{ error }}
                    <button class="cpa-auth-alert-close" @click="error = ''">
                        <i class="bi-x-lg"></i>
                    </button>
                </div>

                <!-- Email -->
                <div class="cpa-auth-field">
                    <label class="cpa-auth-label">Adresse email</label>
                    <div class="cpa-auth-input-wrap">
                        <i class="bi-envelope cpa-auth-input-icon"></i>
                        <input v-model="form.email" type="email" class="cpa-auth-input"
                               placeholder="votre@email.com" @keyup.enter="login" />
                    </div>
                </div>

                <!-- Password -->
                <div class="cpa-auth-field">
                    <div class="cpa-auth-label-row">
                        <label class="cpa-auth-label">Mot de passe</label>
                        <button class="cpa-auth-forgot-btn" @click="showForgotModal = true" type="button">
                            Mot de passe oublié ?
                        </button>
                    </div>
                    <div class="cpa-auth-input-wrap">
                        <i class="bi-lock cpa-auth-input-icon"></i>
                        <input v-model="form.password"
                               :type="showPassword ? 'text' : 'password'"
                               class="cpa-auth-input"
                               placeholder="••••••••"
                               @keyup.enter="login" />
                        <button class="cpa-auth-toggle-pw" @click="showPassword = !showPassword" type="button">
                            <i :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <label class="cpa-auth-checkbox cpa-auth-remember">
                    <input type="checkbox" v-model="form.remember" />
                    <span class="cpa-auth-checkmark"></span>
                    <span>Se souvenir de moi</span>
                </label>

                <!-- Login Button -->
                <button class="cpa-auth-btn cpa-auth-btn-primary" @click="login" :disabled="loading">
                    <span v-if="loading" class="cpa-auth-spinner"></span>
                    {{ loading ? 'Connexion...' : 'Se connecter' }}
                    <i v-if="!loading" class="bi-arrow-right ms-2"></i>
                </button>

                <!-- Divider -->
                <div class="cpa-auth-divider">
                    <span>ou</span>
                </div>

                <!-- Quick access info -->
                <div class="cpa-login-demo-accounts">
                    <div class="cpa-login-demo-header">
                        <i class="bi-info-circle"></i>
                        <strong>Comptes de démonstration</strong>
                    </div>
                    <div class="cpa-login-demo-grid">
                        <div class="cpa-login-demo-card" @click="form.email = 'admin@monprojet.com'; form.password = 'Admin2025!'">
                            <i class="bi-shield-fill"></i>
                            <div>
                                <strong>Admin</strong>
                                <small>admin@monprojet.com</small>
                            </div>
                        </div>
                        <div class="cpa-login-demo-card" @click="form.email = 'client1@test.com'; form.password = 'Client2025!'">
                            <i class="bi-person-fill"></i>
                            <div>
                                <strong>Client</strong>
                                <small>client1@test.com</small>
                            </div>
                        </div>
                        <div class="cpa-login-demo-card" @click="form.email = 'comptable@monprojet.com'; form.password = 'Comptable2025!'">
                            <i class="bi-calculator-fill"></i>
                            <div>
                                <strong>Comptable</strong>
                                <small>comptable@monprojet.com</small>
                            </div>
                        </div>
                        <div class="cpa-login-demo-card" @click="form.email = 'entreprise@test.com'; form.password = 'Entreprise2025!'">
                            <i class="bi-building-fill"></i>
                            <div>
                                <strong>Entreprise</strong>
                                <small>entreprise@test.com</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Register link -->
                <div class="cpa-auth-footer-link">
                    Vous n'avez pas encore de compte ?
                    <a href="/cpa-register" class="cpa-auth-link">Créer un compte</a>
                </div>

                <!-- Footer -->
                <div class="cpa-auth-footer">
                    <span>© {{ new Date().getFullYear() }} GEL Cabinet — Propulsé par IA</span>
                    <span class="cpa-auth-footer-dot">·</span>
                    <a href="/conditions-generales">CGU</a>
                    <span class="cpa-auth-footer-dot">·</span>
                    <a href="/politique-confidentialite">Confidentialité</a>
                </div>
            </div>
        </div>

        <!-- Right Panel: Hero Visual -->
        <div class="cpa-auth-hero-panel">
            <div class="cpa-auth-hero-overlay"></div>
            <div class="cpa-auth-hero-content">
                <div class="cpa-auth-hero-badge">
                    <i class="bi-stars"></i>
                    Propulsé par Intelligence Artificielle
                </div>
                <h2 class="cpa-auth-hero-title">
                    Gérez vos impôts <br />en toute simplicité
                </h2>
                <p class="cpa-auth-hero-desc">
                    Déclarations fiscales, états financiers, comptabilité d'entreprise — tout dans un seul espace sécurisé, adapté aux réalités du Bénin.
                </p>

                <!-- Stats cards -->
                <div class="cpa-auth-hero-stats">
                    <div class="cpa-auth-hero-stat">
                        <div class="cpa-auth-hero-stat-value">500+</div>
                        <div class="cpa-auth-hero-stat-label">Clients actifs</div>
                    </div>
                    <div class="cpa-auth-hero-stat">
                        <div class="cpa-auth-hero-stat-value">2500+</div>
                        <div class="cpa-auth-hero-stat-label">Déclarations</div>
                    </div>
                    <div class="cpa-auth-hero-stat">
                        <div class="cpa-auth-hero-stat-value">99.9%</div>
                        <div class="cpa-auth-hero-stat-label">Disponibilité</div>
                    </div>
                </div>

                <!-- Testimonial -->
                <div class="cpa-auth-hero-testimonial">
                    <p>"GEL Cabinet a transformé la gestion de mes déclarations fiscales. Simple, rapide et fiable."</p>
                    <div class="cpa-auth-hero-testimonial-author">
                        <div class="cpa-auth-hero-testimonial-avatar">A</div>
                        <div>
                            <strong>Amadou K.</strong>
                            <small>Entrepreneur, Cotonou</small>
                        </div>
                    </div>
                </div>

                <!-- Animated shapes -->
                <div class="cpa-auth-hero-shape cpa-auth-shape-1"></div>
                <div class="cpa-auth-hero-shape cpa-auth-shape-2"></div>
                <div class="cpa-auth-hero-shape cpa-auth-shape-3"></div>
            </div>
        </div>

        <!-- Forgot Password Modal -->
        <Teleport to="body">
            <div v-if="showForgotModal" class="cpa-modal-overlay" @click.self="closeForgotModal">
                <div class="cpa-modal">
                    <div class="cpa-modal-header">
                        <h3>Réinitialiser le mot de passe</h3>
                        <button class="cpa-modal-close" @click="closeForgotModal">
                            <i class="bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="cpa-modal-body">
                        <div v-if="forgotSuccess" class="cpa-modal-success">
                            <i class="bi-check-circle-fill"></i>
                            <p>Un email de réinitialisation a été envoyé à <strong>{{ forgotEmail }}</strong></p>
                        </div>
                        <div v-else>
                            <p class="cpa-modal-desc">
                                Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                            </p>
                            <div v-if="forgotError" class="cpa-auth-alert cpa-auth-alert-danger" style="margin-bottom: 14px;">
                                {{ forgotError }}
                            </div>
                            <div class="cpa-auth-field">
                                <div class="cpa-auth-input-wrap">
                                    <i class="bi-envelope cpa-auth-input-icon"></i>
                                    <input v-model="forgotEmail" type="email" class="cpa-auth-input"
                                           placeholder="votre@email.com" @keyup.enter="sendResetLink" />
                                </div>
                            </div>
                            <button class="cpa-auth-btn cpa-auth-btn-primary" @click="sendResetLink" :disabled="forgotLoading">
                                <span v-if="forgotLoading" class="cpa-auth-spinner"></span>
                                {{ forgotLoading ? 'Envoi...' : 'Envoyer le lien' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style>
/* ─── Login-specific styles (Auth shared styles in CpaRegister.vue) ─── */

.cpa-auth-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.cpa-auth-forgot-btn {
    background: none;
    border: none;
    color: #FF7900;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    transition: color 0.15s;
}
.cpa-auth-forgot-btn:hover {
    color: #e06700;
    text-decoration: underline;
}

.cpa-auth-remember {
    margin: 12px 0 4px 0;
}

.cpa-login-secure-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 500;
    color: #166534;
    margin-bottom: 22px;
}
.cpa-login-secure-badge i {
    color: #22c55e;
    font-size: 15px;
}

/* ── Divider ── */
.cpa-auth-divider {
    display: flex;
    align-items: center;
    margin: 22px 0;
    gap: 14px;
}
.cpa-auth-divider::before,
.cpa-auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}
.cpa-auth-divider span {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

/* ── Demo Accounts ── */
.cpa-login-demo-accounts {
    margin-bottom: 20px;
}
.cpa-login-demo-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 10px;
}
.cpa-login-demo-header i {
    color: #FF7900;
}
.cpa-login-demo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.cpa-login-demo-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.cpa-login-demo-card:hover {
    border-color: #FF7900;
    background: rgba(255, 121, 0, 0.03);
}
.cpa-login-demo-card i {
    font-size: 18px;
    color: #163A5E;
}
.cpa-login-demo-card strong {
    display: block;
    font-size: 12px;
    color: #374151;
}
.cpa-login-demo-card small {
    font-size: 10px;
    color: #9ca3af;
}

/* ── Hero stats ── */
.cpa-auth-hero-stats {
    display: flex;
    gap: 24px;
    margin-bottom: 32px;
}
.cpa-auth-hero-stat {
    text-align: center;
}
.cpa-auth-hero-stat-value {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 28px;
    color: #FF7900;
}
.cpa-auth-hero-stat-label {
    font-size: 12px;
    opacity: 0.7;
    margin-top: 2px;
}

/* ── Testimonial ── */
.cpa-auth-hero-testimonial {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 20px;
    backdrop-filter: blur(8px);
}
.cpa-auth-hero-testimonial p {
    font-size: 14px;
    line-height: 1.6;
    font-style: italic;
    opacity: 0.9;
    margin-bottom: 14px;
}
.cpa-auth-hero-testimonial-author {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cpa-auth-hero-testimonial-avatar {
    width: 36px;
    height: 36px;
    background: #FF7900;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}
.cpa-auth-hero-testimonial-author strong {
    display: block;
    font-size: 13px;
}
.cpa-auth-hero-testimonial-author small {
    font-size: 11px;
    opacity: 0.7;
}

/* ── Modal ── */
.cpa-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: cpa-fadeIn 0.2s ease;
}
.cpa-modal {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    animation: cpa-scaleIn 0.25s ease;
}
.cpa-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #eef0f4;
}
.cpa-modal-header h3 {
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #163A5E;
    margin: 0;
}
.cpa-modal-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    transition: color 0.15s;
}
.cpa-modal-close:hover {
    color: #374151;
}
.cpa-modal-body {
    padding: 24px;
}
.cpa-modal-desc {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 18px;
}
.cpa-modal-success {
    text-align: center;
    padding: 20px 0;
}
.cpa-modal-success i {
    font-size: 48px;
    color: #22c55e;
    display: block;
    margin-bottom: 14px;
}
.cpa-modal-success p {
    font-size: 14px;
    color: #374151;
}
@keyframes cpa-fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes cpa-scaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 640px) {
    .cpa-login-demo-grid {
        grid-template-columns: 1fr;
    }
    .cpa-auth-hero-stats {
        flex-direction: column;
        gap: 12px;
    }
}
</style>
