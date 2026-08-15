<script setup>
import { ref } from 'vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const email = ref('');
const password = ref('');
const remember = ref(false);

const submit = () => {
    // Dans une vraie application, cela appellerait l'API
    window.location.href = "/gel/dashboard";
};
</script>

<template>
    <AuthLayout type="cabinet" title="Connexion Cabinet | GEL Cabinet">
        <div class="auth-form-wrapper">
            <h2 class="auth-title">Connexion Cabinet</h2>
            <p class="auth-subtitle">Accédez à l'espace collaborateur et administrateur.</p>

            <form @submit.prevent="submit" class="auth-form">
                <div class="form-group">
                    <label class="form-label">Adresse Email</label>
                    <div class="input-with-icon">
                        <i class="bi bi-envelope"></i>
                        <input v-model="email" type="email" class="form-input" placeholder="collaborateur@gelcabinet.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label class="form-label">Mot de passe</label>
                        <a href="/forgot-password" class="forgot-link">Mot de passe oublié ?</a>
                    </div>
                    <div class="input-with-icon">
                        <i class="bi bi-lock"></i>
                        <input v-model="password" type="password" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-checkbox">
                    <input v-model="remember" type="checkbox" id="remember" class="checkbox-input">
                    <label for="remember" class="checkbox-label">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn-submit">
                    Se connecter
                </button>
            </form>
            
            <div class="auth-divider">
                <span>Ou</span>
            </div>

            <div class="auth-register">
                Nouveau collaborateur ? <a href="/cpa/register" class="register-link">Créer un compte</a>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.auth-form-wrapper {
    width: 100%;
}

.auth-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 2.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.label-row .form-label {
    margin-bottom: 0;
}

.forgot-link {
    font-size: 0.85rem;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.forgot-link:hover {
    text-decoration: underline;
}

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-with-icon i {
    position: absolute;
    left: 1rem;
    color: #94a3b8;
    font-size: 1.1rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    font-family: inherit;
    font-size: 1rem;
    color: #0f172a;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #2563eb;
    background: white;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
}

.checkbox-input {
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    border: 1px solid #cbd5e1;
    accent-color: #2563eb;
    cursor: pointer;
}

.checkbox-label {
    font-size: 0.875rem;
    color: #475569;
    cursor: pointer;
}

.btn-submit {
    display: block;
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: white;
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
    transition: all 0.2s;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(37, 99, 235, 0.3);
}

.auth-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 2rem 0;
    color: #94a3b8;
    font-size: 0.875rem;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}

.auth-divider span {
    padding: 0 1rem;
}

.auth-register {
    text-align: center;
    font-size: 0.95rem;
    color: #475569;
}

.register-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
}

.register-link:hover {
    text-decoration: underline;
}
</style>
