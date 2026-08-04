/* ============================================================
 * Fichier : stores/cart.js
 * Description : Store reactif du panier d'achat
 * Gerer l'etat du panier (articles, quantites, total)
 * Communique avec l'API backend via fetch()
 * ============================================================ */

import { reactive } from 'vue';

// --- Store reactif du panier ---
// Contient les articles, le nombre total et le montant total
export const cartStore = reactive({
    items: [],
    count: 0,
    total: 0,
    isLoading: false,
});

// Jeton CSRF mis en cache pour les requetes API
let csrfToken = null;

// Recupere le jeton CSRF depuis la balise meta du DOM
function getCsrf() {
    if (!csrfToken) {
        csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    return csrfToken;
}

// --- Chargement initial du panier ---
// Recupere les articles depuis l'API /api/cart
export async function fetchCart() {
    cartStore.isLoading = true;
    try {
        const res = await fetch('/api/cart');
        const data = await res.json();
        cartStore.items = data.items || [];
        cartStore.count = data.count || 0;
        cartStore.total = data.total || 0;
    } catch (e) {
        // Cart may not be available yet, silently fail
        console.warn('Could not load cart:', e);
    } finally {
        cartStore.isLoading = false;
    }
}

// --- Ajout d'un article au panier ---
// Envoie une requete POST avec l'ID produit et la quantite
export async function addToCart(productId, quantity = 1) {
    try {
        const res = await fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: productId, quantity })
        });
        if (!res.ok) throw new Error('Failed to add to cart');
        const data = await res.json();
        cartStore.items = data.items || [];
        cartStore.count = data.count || 0;
        cartStore.total = data.total || 0;
        return true;
    } catch (e) {
        console.error('Add to cart error:', e);
        return false;
    }
}

// --- Mise a jour de la quantite d'un article ---
// Envoie une requete PUT avec l'ID produit et la nouvelle quantite
export async function updateCartItem(productId, quantity) {
    try {
        const res = await fetch('/api/cart/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: productId, quantity })
        });
        if (!res.ok) throw new Error('Failed to update cart');
        const data = await res.json();
        cartStore.items = data.items || [];
        cartStore.count = data.count || 0;
        cartStore.total = data.total || 0;
        return true;
    } catch (e) {
        console.error('Update cart error:', e);
        return false;
    }
}

// --- Suppression d'un article du panier ---
// Envoie une requete DELETE avec l'ID produit
export async function removeFromCart(productId) {
    try {
        const res = await fetch('/api/cart/remove/' + productId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            }
        });
        if (!res.ok) throw new Error('Failed to remove from cart');
        const data = await res.json();
        cartStore.items = data.items || [];
        cartStore.count = data.count || 0;
        cartStore.total = data.total || 0;
        return true;
    } catch (e) {
        console.error('Remove from cart error:', e);
        return false;
    }
}

// --- Vidage complet du panier ---
// Envoie une requete POST pour vider le panier et reinitialise le store
export async function clearCart() {
    try {
        await fetch('/api/cart/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            }
        });
        cartStore.items = [];
        cartStore.count = 0;
        cartStore.total = 0;
    } catch (e) {
        console.error('Clear cart error:', e);
    }
}
