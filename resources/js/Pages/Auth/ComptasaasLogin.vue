<script setup>
import { ref } from 'vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const email = ref('');
const password = ref('');
const remember = ref(false);

const submit = () => {
    // Dans une vraie application, cela appellerait l'API
    window.location.href = "/company/dashboard";
};
</script>

<template>
    <AuthLayout type="client" title="Espace Client | GEL Cabinet">
        <div class="auth-form-wrapper">
            <h2 class="auth-title">Espace Client</h2>
            <p class="auth-subtitle">Accédez à vos documents et à votre comptabilité.</p>

            <form @submit.prevent="submit" class="auth-form">
                <div class="form-group">
                    <label class="form-label">Adresse Email professionnelle</label>
                    <div class="input-with-icon">
                        <i class="bi bi-envelope"></i>
                        <input v-model="email" type="email" class="form-input" placeholder="vous@votre-entreprise.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label class="form-label">Mot de passe</label>
                        <a href="/forgot-password" class="forgot-link">Oublié ?</a>
                    </div>
                    <div class="input-with-icon">
                        <i class="bi bi-lock"></i>
                        <input v-model="password" type="password" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-checkbox">
                    <input v-model="remember" type="checkbox" id="remember" class="checkbox-input">
                    <label for="remember" class="checkbox-label">Rester connecté</label>
                </div>

                <button type="submit" class="btn-submit">
                    Se connecter à mon espace
                </button>
            </form>
            
            <div class="auth-divider">
                <span>Ou</span>
            </div>

            <div class="auth-register">
                Première connexion ? <a href="/saas/register" class="register-link">Activez votre compte</a>
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
    color: #10b981;
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
    border-color: #10b981;
    background: white;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
    accent-color: #10b981;
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
    background: linear-gradient(135deg, #059669, #10b981);
    color: white;
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    transition: all 0.2s;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(16, 185, 129, 0.3);
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
    color: #10b981;
    font-weight: 600;
    text-decoration: none;
}

.register-link:hover {
    text-decoration: underline;
}
</style>
