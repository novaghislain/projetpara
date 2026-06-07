import { reactive } from 'vue';

export const cartStore = reactive({
    items: [],
    count: 0,
    total: 0,
    isLoading: false,
});

let csrfToken = null;

function getCsrf() {
    if (!csrfToken) {
        csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    return csrfToken;
}

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
