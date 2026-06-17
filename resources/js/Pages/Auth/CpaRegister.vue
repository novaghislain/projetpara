<script setup>
import { ref, computed, watch, onMounted } from 'vue';

// ─── Form State ───
const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    phone_prefix: '+229',
    country: 'BJ',
    city: '',
    password: '',
    password_confirmation: '',
    accept_terms: false,
    account_type: 'particulier',
});

const step = ref(1);
const totalSteps = 3;
const loading = ref(false);
const success = ref(false);
const error = ref('');
const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const fieldErrors = ref({});

// ─── Countries ───
const countries = [
    { code: 'BJ', name: 'Bénin', prefix: '+229', flag: '🇧🇯' },
    { code: 'TG', name: 'Togo', prefix: '+228', flag: '🇹🇬' },
    { code: 'CI', name: "Côte d'Ivoire", prefix: '+225', flag: '🇨🇮' },
    { code: 'SN', name: 'Sénégal', prefix: '+221', flag: '🇸🇳' },
    { code: 'BF', name: 'Burkina Faso', prefix: '+226', flag: '🇧🇫' },
    { code: 'ML', name: 'Mali', prefix: '+223', flag: '🇲🇱' },
    { code: 'NE', name: 'Niger', prefix: '+227', flag: '🇳🇪' },
    { code: 'GN', name: 'Guinée', prefix: '+224', flag: '🇬🇳' },
    { code: 'GA', name: 'Gabon', prefix: '+241', flag: '🇬🇦' },
    { code: 'CM', name: 'Cameroun', prefix: '+237', flag: '🇨🇲' },
    { code: 'CD', name: 'RD Congo', prefix: '+243', flag: '🇨🇩' },
    { code: 'CG', name: 'Congo', prefix: '+242', flag: '🇨🇬' },
    { code: 'FR', name: 'France', prefix: '+33', flag: '🇫🇷' },
    { code: 'CA', name: 'Canada', prefix: '+1', flag: '🇨🇦' },
    { code: 'US', name: 'États-Unis', prefix: '+1', flag: '🇺🇸' },
];

// Benin cities
const beninCities = [
    'Cotonou', 'Porto-Novo', 'Parakou', 'Djougou', 'Bohicon',
    'Abomey-Calavi', 'Kandi', 'Lokossa', 'Natitingou', 'Ouidah',
    'Savè', 'Abomey', 'Nikki', 'Malanville', 'Savalou',
    'Comè', 'Tchaourou', 'Allada', 'Dogbo-Tota', 'Pobè',
];

const accountTypes = [
    { value: 'particulier', label: 'Particulier', icon: 'bi-person', desc: 'Déclarations personnelles' },
    { value: 'entreprise', label: 'Entreprise', icon: 'bi-building', desc: 'Comptabilité & fiscal' },
    { value: 'association', label: 'Association / ONG', icon: 'bi-people', desc: 'Gestion associative' },
];

// ─── Watchers ───
watch(() => form.value.country, (val) => {
    const c = countries.find(x => x.code === val);
    if (c) form.value.phone_prefix = c.prefix;
});

// ─── Validation ───
const passwordStrength = computed(() => {
    const p = form.value.password;
    if (!p) return { score: 0, label: '', color: '' };
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[a-z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    const levels = [
        { score: 0, label: '', color: '' },
        { score: 1, label: 'Très faible', color: '#dc3545' },
        { score: 2, label: 'Faible', color: '#fd7e14' },
        { score: 3, label: 'Moyen', color: '#ffc107' },
        { score: 4, label: 'Fort', color: '#198754' },
        { score: 5, label: 'Très fort', color: '#0d6efd' },
    ];
    return levels[score] || levels[0];
});

const validateStep = (stepNum) => {
    fieldErrors.value = {};
    if (stepNum === 1) {
        if (!form.value.first_name.trim()) fieldErrors.value.first_name = 'Le prénom est requis';
        if (!form.value.last_name.trim()) fieldErrors.value.last_name = 'Le nom est requis';
        if (!form.value.email.trim()) fieldErrors.value.email = "L'email est requis";
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) fieldErrors.value.email = "Format d'email invalide";
        if (!form.value.phone.trim()) fieldErrors.value.phone = 'Le téléphone est requis';
    }
    if (stepNum === 2) {
        if (!form.value.country) fieldErrors.value.country = 'Le pays est requis';
        if (!form.value.city.trim()) fieldErrors.value.city = 'La ville est requise';
    }
    if (stepNum === 3) {
        if (!form.value.password) fieldErrors.value.password = 'Le mot de passe est requis';
        else if (form.value.password.length < 8) fieldErrors.value.password = 'Minimum 8 caractères';
        if (form.value.password !== form.value.password_confirmation) fieldErrors.value.password_confirmation = 'Les mots de passe ne correspondent pas';
        if (!form.value.accept_terms) fieldErrors.value.accept_terms = 'Vous devez accepter les conditions';
    }
    return Object.keys(fieldErrors.value).length === 0;
};

const nextStep = () => {
    if (validateStep(step.value)) {
        step.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    step.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// ─── Submit ───
const register = async () => {
    if (!validateStep(3)) return;
    loading.value = true;
    error.value = '';
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const payload = {
            name: `${form.value.first_name} ${form.value.last_name}`,
            first_name: form.value.first_name,
            last_name: form.value.last_name,
            email: form.value.email,
            phone: `${form.value.phone_prefix}${form.value.phone}`,
            country: form.value.country,
            city: form.value.city,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            account_type: form.value.account_type,
        };
        const res = await fetch('/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        });
        if (res.ok) {
            success.value = true;
            setTimeout(() => {
                window.location.href = '/cpa-dashboard';
            }, 2000);
        } else {
            const data = await res.json();
            if (data.errors) {
                fieldErrors.value = {};
                Object.keys(data.errors).forEach(k => {
                    fieldErrors.value[k] = data.errors[k][0];
                });
                // Go back to relevant step
                if (data.errors.email || data.errors.first_name || data.errors.last_name || data.errors.phone) step.value = 1;
                else if (data.errors.country || data.errors.city) step.value = 2;
                else step.value = 3;
            }
            error.value = data.message || "Erreur lors de l'inscription";
        }
    } catch (e) {
        error.value = "Erreur réseau. Vérifiez votre connexion.";
    } finally {
        loading.value = false;
    }
};

// ─── Animations ───
const stepClasses = computed(() => ({
    'cpa-reg-step-enter': true,
}));

onMounted(() => {
    document.title = "Créer un compte | GEL Cabinet";
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

                <!-- Success State -->
                <div v-if="success" class="cpa-reg-success">
                    <div class="cpa-reg-success-icon">
                        <i class="bi-check-circle-fill"></i>
                    </div>
                    <h2>Compte créé avec succès !</h2>
                    <p>Bienvenue dans votre espace GEL Cabinet. Redirection en cours...</p>
                    <div class="cpa-reg-loader">
                        <div class="cpa-reg-loader-bar"></div>
                    </div>
                </div>

                <!-- Registration Form -->
                <div v-else>
                    <h1 class="cpa-auth-title">Créer un compte</h1>
                    <p class="cpa-auth-subtitle">Rejoignez GEL Cabinet pour gérer vos déclarations fiscales et comptables</p>

                    <!-- Progress Steps -->
                    <div class="cpa-reg-progress">
                        <div v-for="s in totalSteps" :key="s" class="cpa-reg-progress-step"
                             :class="{ 'cpa-reg-step-active': s === step, 'cpa-reg-step-done': s < step }">
                            <div class="cpa-reg-step-circle">
                                <i v-if="s < step" class="bi-check2"></i>
                                <span v-else>{{ s }}</span>
                            </div>
                            <span class="cpa-reg-step-label">
                                {{ s === 1 ? 'Identité' : s === 2 ? 'Localisation' : 'Sécurité' }}
                            </span>
                        </div>
                        <div class="cpa-reg-progress-line">
                            <div class="cpa-reg-progress-fill" :style="{ width: ((step - 1) / (totalSteps - 1)) * 100 + '%' }"></div>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="error" class="cpa-auth-alert cpa-auth-alert-danger">
                        <i class="bi-exclamation-triangle-fill me-2"></i>
                        {{ error }}
                        <button class="cpa-auth-alert-close" @click="error = ''">
                            <i class="bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Step 1: Identity -->
                    <div v-show="step === 1" class="cpa-reg-step-enter">
                        <!-- Account Type Selection -->
                        <div class="cpa-reg-type-selector">
                            <div v-for="t in accountTypes" :key="t.value"
                                 class="cpa-reg-type-card"
                                 :class="{ 'cpa-reg-type-active': form.account_type === t.value }"
                                 @click="form.account_type = t.value">
                                <i :class="t.icon"></i>
                                <strong>{{ t.label }}</strong>
                                <small>{{ t.desc }}</small>
                            </div>
                        </div>

                        <div class="cpa-auth-row">
                            <div class="cpa-auth-field">
                                <label class="cpa-auth-label">Prénom <span class="cpa-required">*</span></label>
                                <div class="cpa-auth-input-wrap" :class="{ 'cpa-input-error': fieldErrors.first_name }">
                                    <i class="bi-person cpa-auth-input-icon"></i>
                                    <input v-model="form.first_name" type="text" class="cpa-auth-input"
                                           placeholder="Votre prénom" @input="delete fieldErrors.first_name" />
                                </div>
                                <span v-if="fieldErrors.first_name" class="cpa-field-error">{{ fieldErrors.first_name }}</span>
                            </div>
                            <div class="cpa-auth-field">
                                <label class="cpa-auth-label">Nom <span class="cpa-required">*</span></label>
                                <div class="cpa-auth-input-wrap" :class="{ 'cpa-input-error': fieldErrors.last_name }">
                                    <i class="bi-person cpa-auth-input-icon"></i>
                                    <input v-model="form.last_name" type="text" class="cpa-auth-input"
                                           placeholder="Votre nom" @input="delete fieldErrors.last_name" />
                                </div>
                                <span v-if="fieldErrors.last_name" class="cpa-field-error">{{ fieldErrors.last_name }}</span>
                            </div>
                        </div>

                        <div class="cpa-auth-field">
                            <label class="cpa-auth-label">Adresse email <span class="cpa-required">*</span></label>
                            <div class="cpa-auth-input-wrap" :class="{ 'cpa-input-error': fieldErrors.email }">
                                <i class="bi-envelope cpa-auth-input-icon"></i>
                                <input v-model="form.email" type="email" class="cpa-auth-input"
                                       placeholder="votre@email.com" @input="delete fieldErrors.email" />
                            </div>
                            <span v-if="fieldErrors.email" class="cpa-field-error">{{ fieldErrors.email }}</span>
                        </div>

                        <div class="cpa-auth-field">
                            <label class="cpa-auth-label">Téléphone <span class="cpa-required">*</span></label>
                            <div class="cpa-auth-phone-wrap" :class="{ 'cpa-input-error': fieldErrors.phone }">
                                <select v-model="form.phone_prefix" class="cpa-auth-phone-prefix">
                                    <option v-for="c in countries" :key="c.code" :value="c.prefix">
                                        {{ c.flag }} {{ c.prefix }}
                                    </option>
                                </select>
                                <input v-model="form.phone" type="tel" class="cpa-auth-input cpa-auth-phone-input"
                                       placeholder="96 00 00 00" @input="delete fieldErrors.phone" />
                            </div>
                            <span v-if="fieldErrors.phone" class="cpa-field-error">{{ fieldErrors.phone }}</span>
                        </div>

                        <button class="cpa-auth-btn cpa-auth-btn-primary" @click="nextStep">
                            Continuer
                            <i class="bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <!-- Step 2: Location -->
                    <div v-show="step === 2" class="cpa-reg-step-enter">
                        <div class="cpa-auth-field">
                            <label class="cpa-auth-label">Pays <span class="cpa-required">*</span></label>
                            <div class="cpa-auth-input-wrap cpa-auth-select-wrap" :class="{ 'cpa-input-error': fieldErrors.country }">
                                <i class="bi-globe cpa-auth-input-icon"></i>
                                <select v-model="form.country" class="cpa-auth-input cpa-auth-select" @change="delete fieldErrors.country">
                                    <option value="" disabled>Sélectionner un pays</option>
                                    <option v-for="c in countries" :key="c.code" :value="c.code">
                                        {{ c.flag }} {{ c.name }}
                                    </option>
                                </select>
                            </div>
                            <span v-if="fieldErrors.country" class="cpa-field-error">{{ fieldErrors.country }}</span>
                        </div>

                        <div class="cpa-auth-field">
                            <label class="cpa-auth-label">Ville <span class="cpa-required">*</span></label>
                            <div class="cpa-auth-input-wrap" :class="{ 'cpa-input-error': fieldErrors.city }">
                                <i class="bi-geo-alt cpa-auth-input-icon"></i>
                                <select v-if="form.country === 'BJ'" v-model="form.city" class="cpa-auth-input cpa-auth-select" @change="delete fieldErrors.city">
                                    <option value="" disabled>Choisir une ville</option>
                                    <option v-for="city in beninCities" :key="city" :value="city">{{ city }}</option>
                                </select>
                                <input v-else v-model="form.city" type="text" class="cpa-auth-input"
                                       placeholder="Votre ville" @input="delete fieldErrors.city" />
                            </div>
                            <span v-if="fieldErrors.city" class="cpa-field-error">{{ fieldErrors.city }}</span>
                        </div>

                        <!-- Bénin-specific info box -->
                        <div v-if="form.country === 'BJ'" class="cpa-benin-info">
                            <div class="cpa-benin-info-header">
                                <span class="cpa-benin-flag">🇧🇯</span>
                                <strong>Services disponibles au Bénin</strong>
                            </div>
                            <ul class="cpa-benin-list">
                                <li><i class="bi-check-circle-fill"></i> Déclarations fiscales (IFU, AIB, TVA)</li>
                                <li><i class="bi-check-circle-fill"></i> États financiers SYSCOHADA</li>
                                <li><i class="bi-check-circle-fill"></i> Comptabilité d'entreprise</li>
                                <li><i class="bi-check-circle-fill"></i> Gestion de la paie (CNSS, IPTS)</li>
                                <li><i class="bi-check-circle-fill"></i> Conseil stratégique & fiscal</li>
                            </ul>
                        </div>

                        <div class="cpa-auth-btn-group">
                            <button class="cpa-auth-btn cpa-auth-btn-outline" @click="prevStep">
                                <i class="bi-arrow-left me-2"></i> Retour
                            </button>
                            <button class="cpa-auth-btn cpa-auth-btn-primary" @click="nextStep">
                                Continuer <i class="bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Security -->
                    <div v-show="step === 3" class="cpa-reg-step-enter">
                        <div class="cpa-auth-field">
                            <label class="cpa-auth-label">Mot de passe <span class="cpa-required">*</span></label>
                            <div class="cpa-auth-input-wrap" :class="{ 'cpa-input-error': fieldErrors.password }">
                                <i class="bi-lock cpa-auth-input-icon"></i>
                                <input v-model="form.password"
                                       :type="showPassword ? 'text' : 'password'"
                                       class="cpa-auth-input"
                                       placeholder="Minimum 8 caractères"
                                       @input="delete fieldErrors.password" />
                                <button class="cpa-auth-toggle-pw" @click="showPassword = !showPassword" type="button">
                                    <i :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </button>
                            </div>
                            <span v-if="fieldErrors.password" class="cpa-field-error">{{ fieldErrors.password }}</span>
                            <!-- Password strength -->
                            <div v-if="form.password" class="cpa-pw-strength">
                                <div class="cpa-pw-strength-bar">
                                    <div class="cpa-pw-strength-fill"
                                         :style="{ width: (passwordStrength.score / 5 * 100) + '%', background: passwordStrength.color }"></div>
                                </div>
                                <span class="cpa-pw-strength-label" :style="{ color: passwordStrength.color }">
                                    {{ passwordStrength.label }}
                                </span>
                            </div>
                        </div>

                        <div class="cpa-auth-field">
                            <label class="cpa-auth-label">Confirmer le mot de passe <span class="cpa-required">*</span></label>
                            <div class="cpa-auth-input-wrap" :class="{ 'cpa-input-error': fieldErrors.password_confirmation }">
                                <i class="bi-lock cpa-auth-input-icon"></i>
                                <input v-model="form.password_confirmation"
                                       :type="showPasswordConfirm ? 'text' : 'password'"
                                       class="cpa-auth-input"
                                       placeholder="Retaper le mot de passe"
                                       @input="delete fieldErrors.password_confirmation" />
                                <button class="cpa-auth-toggle-pw" @click="showPasswordConfirm = !showPasswordConfirm" type="button">
                                    <i :class="showPasswordConfirm ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </button>
                            </div>
                            <span v-if="fieldErrors.password_confirmation" class="cpa-field-error">{{ fieldErrors.password_confirmation }}</span>
                        </div>

                        <!-- Terms -->
                        <label class="cpa-auth-checkbox" :class="{ 'cpa-input-error-text': fieldErrors.accept_terms }">
                            <input type="checkbox" v-model="form.accept_terms" @change="delete fieldErrors.accept_terms" />
                            <span class="cpa-auth-checkmark"></span>
                            <span>J'accepte les <a href="/conditions-generales" class="cpa-auth-link">conditions générales</a>
                            et la <a href="/politique-confidentialite" class="cpa-auth-link">politique de confidentialité</a></span>
                        </label>
                        <span v-if="fieldErrors.accept_terms" class="cpa-field-error">{{ fieldErrors.accept_terms }}</span>

                        <div class="cpa-auth-btn-group">
                            <button class="cpa-auth-btn cpa-auth-btn-outline" @click="prevStep">
                                <i class="bi-arrow-left me-2"></i> Retour
                            </button>
                            <button class="cpa-auth-btn cpa-auth-btn-primary" @click="register" :disabled="loading">
                                <span v-if="loading" class="cpa-auth-spinner"></span>
                                {{ loading ? 'Création...' : "Créer mon compte" }}
                                <i v-if="!loading" class="bi-check2-circle ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Login link -->
                    <div class="cpa-auth-footer-link">
                        Vous avez déjà un compte ?
                        <a href="/cpa-login" class="cpa-auth-link">Se connecter</a>
                    </div>
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
                    <i class="bi-shield-check"></i>
                    Plateforme sécurisée
                </div>
                <h2 class="cpa-auth-hero-title">
                    Votre espace fiscal <br />& comptable intelligent
                </h2>
                <p class="cpa-auth-hero-desc">
                    Gérez vos déclarations, documents et communications avec votre cabinet comptable depuis une interface unique et intuitive.
                </p>
                <div class="cpa-auth-hero-features">
                    <div class="cpa-auth-hero-feature">
                        <div class="cpa-auth-hero-feature-icon"><i class="bi-file-earmark-text"></i></div>
                        <div>
                            <strong>Déclarations fiscales</strong>
                            <small>IFU, TVA, AIB, IRPP</small>
                        </div>
                    </div>
                    <div class="cpa-auth-hero-feature">
                        <div class="cpa-auth-hero-feature-icon"><i class="bi-folder2-open"></i></div>
                        <div>
                            <strong>Gestion documentaire</strong>
                            <small>Upload & archivage sécurisé</small>
                        </div>
                    </div>
                    <div class="cpa-auth-hero-feature">
                        <div class="cpa-auth-hero-feature-icon"><i class="bi-chat-dots"></i></div>
                        <div>
                            <strong>Messagerie intégrée</strong>
                            <small>Communication directe</small>
                        </div>
                    </div>
                    <div class="cpa-auth-hero-feature">
                        <div class="cpa-auth-hero-feature-icon"><i class="bi-graph-up-arrow"></i></div>
                        <div>
                            <strong>Suivi en temps réel</strong>
                            <small>Tableau de bord intelligent</small>
                        </div>
                    </div>
                </div>
                <!-- Animated shapes -->
                <div class="cpa-auth-hero-shape cpa-auth-shape-1"></div>
                <div class="cpa-auth-hero-shape cpa-auth-shape-2"></div>
                <div class="cpa-auth-hero-shape cpa-auth-shape-3"></div>
            </div>
        </div>
    </div>
</template>

<style>
/* ═══════════════════════════════════════════════════════════
   CPA AUTH PAGES — Crescendo CPA Inspired
   Split-screen layout: form left, hero right
   White dominant, Orange + Blue accents
   ═══════════════════════════════════════════════════════════ */

/* ── Page Shell ── */
.cpa-auth-page {
    display: flex;
    min-height: 100vh;
    background: #ffffff;
}

/* ── Left Panel (Form) ── */
.cpa-auth-form-panel {
    flex: 0 0 55%;
    max-width: 55%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    overflow-y: auto;
    max-height: 100vh;
}
.cpa-auth-form-inner {
    width: 100%;
    max-width: 520px;
}

/* ── Logo ── */
.cpa-auth-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 32px;
}
.cpa-auth-logo-icon {
    width: 38px;
    height: 38px;
    background: #FF7900;
    color: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.cpa-auth-logo-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 18px;
    color: #163A5E;
}

/* ── Typography ── */
.cpa-auth-title {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 26px;
    color: #163A5E;
    margin: 0 0 6px 0;
    line-height: 1.2;
}
.cpa-auth-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 28px 0;
    line-height: 1.5;
}

/* ── Progress Steps ── */
.cpa-reg-progress {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    position: relative;
    padding: 0 10px;
}
.cpa-reg-progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    z-index: 1;
}
.cpa-reg-step-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #9ca3af;
    transition: all 0.3s ease;
}
.cpa-reg-step-label {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    transition: color 0.3s ease;
}
.cpa-reg-step-active .cpa-reg-step-circle {
    background: #FF7900;
    border-color: #FF7900;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(255, 121, 0, 0.15);
}
.cpa-reg-step-active .cpa-reg-step-label {
    color: #FF7900;
}
.cpa-reg-step-done .cpa-reg-step-circle {
    background: #163A5E;
    border-color: #163A5E;
    color: #fff;
}
.cpa-reg-step-done .cpa-reg-step-label {
    color: #163A5E;
}
.cpa-reg-progress-line {
    position: absolute;
    top: 18px;
    left: 50px;
    right: 50px;
    height: 3px;
    background: #e5e7eb;
    border-radius: 2px;
    z-index: 0;
}
.cpa-reg-progress-fill {
    height: 100%;
    background: #163A5E;
    border-radius: 2px;
    transition: width 0.4s ease;
}

/* ── Form Fields ── */
.cpa-auth-field {
    margin-bottom: 18px;
}
.cpa-auth-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.cpa-auth-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.cpa-required {
    color: #FF7900;
}
.cpa-auth-input-wrap {
    display: flex;
    align-items: center;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0 14px;
    transition: all 0.2s ease;
}
.cpa-auth-input-wrap:focus-within {
    border-color: #FF7900;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.08);
}
.cpa-input-error {
    border-color: #dc3545 !important;
    background: #fff5f5 !important;
}
.cpa-input-error:focus-within {
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
}
.cpa-auth-input-icon {
    color: #9ca3af;
    font-size: 16px;
    margin-right: 10px;
    flex-shrink: 0;
}
.cpa-auth-input {
    border: none;
    background: transparent;
    padding: 12px 0;
    font-size: 14px;
    color: #1a1a2e;
    width: 100%;
    outline: none;
    font-family: 'Inter', sans-serif;
}
.cpa-auth-input::placeholder {
    color: #b0b5bf;
}
.cpa-auth-select-wrap {
    cursor: pointer;
}
.cpa-auth-select {
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
}
.cpa-field-error {
    display: block;
    font-size: 12px;
    color: #dc3545;
    margin-top: 4px;
    font-weight: 500;
}

/* ── Phone field ── */
.cpa-auth-phone-wrap {
    display: flex;
    align-items: center;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    transition: all 0.2s ease;
    overflow: hidden;
}
.cpa-auth-phone-wrap:focus-within {
    border-color: #FF7900;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.08);
}
.cpa-auth-phone-prefix {
    border: none;
    background: #f0f1f4;
    padding: 12px 10px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    outline: none;
    border-right: 1.5px solid #e5e7eb;
    min-width: 95px;
}
.cpa-auth-phone-input {
    padding-left: 14px !important;
}

/* ── Password Toggle ── */
.cpa-auth-toggle-pw {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    font-size: 16px;
    transition: color 0.15s;
    flex-shrink: 0;
}
.cpa-auth-toggle-pw:hover {
    color: #FF7900;
}

/* ── Password Strength ── */
.cpa-pw-strength {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
}
.cpa-pw-strength-bar {
    flex: 1;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
}
.cpa-pw-strength-fill {
    height: 100%;
    border-radius: 2px;
    transition: all 0.3s ease;
}
.cpa-pw-strength-label {
    font-size: 11px;
    font-weight: 600;
    min-width: 60px;
}

/* ── Checkbox ── */
.cpa-auth-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: #4b5563;
    cursor: pointer;
    margin: 18px 0 4px 0;
    line-height: 1.5;
}
.cpa-auth-checkbox input {
    display: none;
}
.cpa-auth-checkmark {
    width: 20px;
    height: 20px;
    min-width: 20px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
    transition: all 0.2s;
}
.cpa-auth-checkbox input:checked + .cpa-auth-checkmark {
    background: #FF7900;
    border-color: #FF7900;
}
.cpa-auth-checkbox input:checked + .cpa-auth-checkmark::after {
    content: '✓';
    color: #fff;
    font-size: 12px;
    font-weight: 700;
}
.cpa-input-error-text .cpa-auth-checkmark {
    border-color: #dc3545;
}

/* ── Buttons ── */
.cpa-auth-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 13px 28px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
    outline: none;
}
.cpa-auth-btn-primary {
    background: #FF7900;
    color: #fff;
    width: 100%;
    margin-top: 8px;
    box-shadow: 0 4px 14px rgba(255, 121, 0, 0.25);
}
.cpa-auth-btn-primary:hover {
    background: #e06700;
    box-shadow: 0 6px 20px rgba(255, 121, 0, 0.35);
    transform: translateY(-1px);
}
.cpa-auth-btn-primary:active {
    transform: translateY(0);
}
.cpa-auth-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.cpa-auth-btn-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}
.cpa-auth-btn-outline:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}
.cpa-auth-btn-group {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}
.cpa-auth-btn-group .cpa-auth-btn-primary {
    flex: 1;
}
.cpa-auth-btn-group .cpa-auth-btn-outline {
    flex: 0 0 auto;
}

/* ── Spinner ── */
.cpa-auth-spinner {
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: cpa-spin 0.7s linear infinite;
    margin-right: 8px;
}
@keyframes cpa-spin {
    to { transform: rotate(360deg); }
}

/* ── Account Type Selector ── */
.cpa-reg-type-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 22px;
}
.cpa-reg-type-card {
    padding: 14px 10px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.cpa-reg-type-card i {
    font-size: 22px;
    color: #9ca3af;
    transition: color 0.2s;
}
.cpa-reg-type-card strong {
    font-size: 12px;
    color: #374151;
}
.cpa-reg-type-card small {
    font-size: 10px;
    color: #9ca3af;
}
.cpa-reg-type-card:hover {
    border-color: #FF7900;
    background: rgba(255, 121, 0, 0.02);
}
.cpa-reg-type-active {
    border-color: #FF7900;
    background: rgba(255, 121, 0, 0.05);
    box-shadow: 0 0 0 3px rgba(255, 121, 0, 0.1);
}
.cpa-reg-type-active i {
    color: #FF7900;
}

/* ── Bénin info box ── */
.cpa-benin-info {
    background: linear-gradient(135deg, #fefbf3, #fff8f0);
    border: 1px solid #fde0b0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 18px;
}
.cpa-benin-info-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 14px;
    color: #163A5E;
}
.cpa-benin-flag {
    font-size: 22px;
}
.cpa-benin-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.cpa-benin-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #4b5563;
    padding: 4px 0;
}
.cpa-benin-list li i {
    color: #22c55e;
    font-size: 14px;
}

/* ── Alert ── */
.cpa-auth-alert {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 20px;
    position: relative;
}
.cpa-auth-alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.cpa-auth-alert-close {
    background: none;
    border: none;
    color: #991b1b;
    cursor: pointer;
    margin-left: auto;
    padding: 2px;
}

/* ── Links ── */
.cpa-auth-link {
    color: #FF7900;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.15s;
}
.cpa-auth-link:hover {
    color: #e06700;
    text-decoration: underline;
}
.cpa-auth-footer-link {
    text-align: center;
    font-size: 13px;
    color: #6b7280;
    margin-top: 24px;
}

/* ── Footer ── */
.cpa-auth-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 32px;
    font-size: 11px;
    color: #9ca3af;
}
.cpa-auth-footer a {
    color: #9ca3af;
    text-decoration: none;
}
.cpa-auth-footer a:hover {
    color: #FF7900;
}
.cpa-auth-footer-dot {
    color: #d1d5db;
}

/* ── Success State ── */
.cpa-reg-success {
    text-align: center;
    padding: 40px 0;
    animation: cpa-fadeInUp 0.5s ease;
}
.cpa-reg-success-icon {
    font-size: 64px;
    color: #22c55e;
    margin-bottom: 16px;
}
.cpa-reg-success h2 {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 24px;
    color: #163A5E;
    margin-bottom: 8px;
}
.cpa-reg-success p {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 24px;
}
.cpa-reg-loader {
    width: 200px;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    margin: 0 auto;
    overflow: hidden;
}
.cpa-reg-loader-bar {
    height: 100%;
    background: #FF7900;
    border-radius: 2px;
    animation: cpa-loader-anim 2s ease forwards;
}
@keyframes cpa-loader-anim {
    from { width: 0; }
    to { width: 100%; }
}

/* ── Step transition ── */
.cpa-reg-step-enter {
    animation: cpa-slideIn 0.35s ease;
}
@keyframes cpa-slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

/* ── Right Panel (Hero) ── */
.cpa-auth-hero-panel {
    flex: 0 0 45%;
    max-width: 45%;
    background: linear-gradient(145deg, #163A5E 0%, #0f2840 50%, #1a4e78 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 60px 50px;
}
.cpa-auth-hero-overlay {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.cpa-auth-hero-content {
    position: relative;
    z-index: 1;
    color: #fff;
    max-width: 420px;
}
.cpa-auth-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 24px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.cpa-auth-hero-title {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 34px;
    line-height: 1.2;
    margin-bottom: 16px;
}
.cpa-auth-hero-desc {
    font-size: 15px;
    line-height: 1.6;
    opacity: 0.85;
    margin-bottom: 36px;
}
.cpa-auth-hero-features {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.cpa-auth-hero-feature {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: background 0.2s;
}
.cpa-auth-hero-feature:hover {
    background: rgba(255, 255, 255, 0.1);
}
.cpa-auth-hero-feature-icon {
    width: 36px;
    height: 36px;
    background: rgba(255, 121, 0, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #FF7900;
    flex-shrink: 0;
}
.cpa-auth-hero-feature strong {
    display: block;
    font-size: 12px;
    font-weight: 700;
}
.cpa-auth-hero-feature small {
    font-size: 10px;
    opacity: 0.7;
}

/* ── Animated Shapes ── */
.cpa-auth-hero-shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.08;
}
.cpa-auth-shape-1 {
    width: 300px;
    height: 300px;
    background: #FF7900;
    top: -80px;
    right: -60px;
    animation: cpa-float 8s ease-in-out infinite;
}
.cpa-auth-shape-2 {
    width: 200px;
    height: 200px;
    background: #4dabf7;
    bottom: -50px;
    left: -40px;
    animation: cpa-float 10s ease-in-out infinite reverse;
}
.cpa-auth-shape-3 {
    width: 120px;
    height: 120px;
    background: #fff;
    bottom: 30%;
    right: 10%;
    animation: cpa-float 6s ease-in-out infinite 2s;
}
@keyframes cpa-float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(15px, -20px); }
}
@keyframes cpa-fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1024px) {
    .cpa-auth-hero-panel {
        display: none;
    }
    .cpa-auth-form-panel {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
@media (max-width: 640px) {
    .cpa-auth-form-panel {
        padding: 20px;
    }
    .cpa-auth-title {
        font-size: 22px;
    }
    .cpa-auth-row {
        grid-template-columns: 1fr;
    }
    .cpa-reg-type-selector {
        grid-template-columns: 1fr;
    }
    .cpa-auth-btn-group {
        flex-direction: column-reverse;
    }
    .cpa-auth-btn-group .cpa-auth-btn-outline {
        width: 100%;
    }
    .cpa-reg-step-label {
        font-size: 10px;
    }
    .cpa-reg-step-circle {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
}
</style>
