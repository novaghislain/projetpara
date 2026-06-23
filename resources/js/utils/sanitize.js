// =============================================================================
// FICHIER : sanitize.js
// RÔLE    : Utilitaire de sanitisation HTML — Protection XSS
// ÉQUIPE  : GEL Cabinet — Équipe Dev Frontend
// =============================================================================
// Utilise DOMPurify pour nettoyer le HTML provenant des utilisateurs avant
// de l'afficher via v-html dans Vue 3.
//
// Configuration restrictive :
//   - Seules les balises "sûres" sont autorisées (b, i, a, p, table, etc.)
//   - Pas de <script>, <style>, <iframe>, <object>, <embed>
//   - Les attributs data-* sont interdits
//   - Les liens externes reçoivent automatiquement rel="noopener noreferrer"
//
// Utilisation :
//   import { sanitizeHtml } from '@/utils/sanitize.js'
//   <div v-html="sanitizeHtml(contenuUser)"></div>
// =============================================================================

/**
 * Utilitaire de sanitisation HTML (DOMPurify).
 * Protection XSS pour les contenus utilisateur affichés via v-html.
 */
import DOMPurify from 'dompurify';

// ─── Configuration restrictive par défaut ────────────────────────────────
// Seules ces balises et attributs sont conservés. Tout le reste est supprimé.
DOMPurify.setConfig({
    ALLOWED_TAGS: [
        'b', 'i', 'em', 'strong', 'a', 'p', 'br', 'ul', 'ol', 'li',
        'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'blockquote', 'pre', 'code', 'hr', 'img', 'figure', 'figcaption',
    ],
    ALLOWED_ATTR: [
        'href', 'target', 'rel', 'class', 'style', 'src', 'alt',
        'width', 'height', 'title', 'id', 'name',
    ],
    // Interdire les attributs data-* (peuvent cacher du contenu malveillant)
    ALLOW_DATA_ATTR: false,
    // Forcer le attribut target (pour les liens)
    ADD_ATTR: ['target'],
});

// ─── Protection des liens externes ───────────────────────────────────────
// Ajoute automatiquement rel="noopener noreferrer" sur tous les liens
// qui s'ouvrent dans un nouvel onglet (target="_blank").
// Empêche la vulnérabilité "tabnabbing" où la page ouverte peut modifier
// la page d'origine via window.opener.
DOMPurify.addHook('afterSanitizeAttributes', function (node) {
    if (node.tagName === 'A' && node.getAttribute('target') === '_blank') {
        node.setAttribute('rel', 'noopener noreferrer');
    }
});

/**
 * Sanitise une chaîne HTML pour affichage dans v-html.
 * Nettoie le HTML en supprimant les balises et attributs dangereux.
 *
 * @param {string} html - Le HTML à nettoyer (peut être null/undefined)
 * @param {object} options - Options optionnelles pour surcharger la config
 * @returns {string} HTML nettoyé et sûr
 *
 * @example
 *   // Usage simple
 *   sanitizeHtml('<p>Bonjour <script>alert(1)</script></p>')
 *   // → '<p>Bonjour </p>'
 *
 *   // Avec options (autoriser une balise supplémentaire)
 *   sanitizeHtml(html, { ALLOWED_TAGS: [...DOMPurify.getConfig().ALLOWED_TAGS, 'video'] })
 */
export function sanitizeHtml(html, options = {}) {
    if (!html) return '';
    const config = { ...DOMPurify.getConfig(), ...options };
    return DOMPurify.sanitize(html, config);
}

export default DOMPurify;
