import { reactive } from 'vue';

export const authStore = reactive({
    user: null,
    isAuthenticated: false,
});

export function initAuth() {
    const el = document.getElementById('auth-data');
    if (el) {
        try {
            const data = JSON.parse(el.textContent);
            authStore.user = data.user;
            authStore.isAuthenticated = !!data.user;
        } catch (e) {
            console.warn('Failed to parse auth data:', e);
        }
    }
}
