<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\UserBookmark;
use App\Models\UserDashboardConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestion des préférences d'interface (§8.14 Signets & §8.15 Dashboard widgetisable).
 *
 * - user_bookmarks        : CRUD des signets de la sidebar (max 10 par utilisateur)
 * - user_dashboard_config : persistance de l'ordre / visibilité des widgets du dashboard
 */
class BookmarkController extends Controller
{
    public const MAX_BOOKMARKS = 10;

    /**
     * Liste les signets de l'utilisateur connecté.
     */
    public function index(): JsonResponse
    {
        $bookmarks = Auth::user()->bookmarks()->get();

        return response()->json([
            'success'   => true,
            'bookmarks' => $bookmarks,
            'max'       => self::MAX_BOOKMARKS,
        ]);
    }

    /**
     * Ajoute un signet (max 10).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'url'   => 'required|string|max:500',
            'icon'  => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        if ($user->bookmarks()->count() >= self::MAX_BOOKMARKS) {
            return response()->json([
                'success' => false,
                'message' => 'Nombre maximum de ' . self::MAX_BOOKMARKS . ' signets atteint.',
            ], 422);
        }

        // Évite les doublons d'URL
        if ($user->bookmarks()->where('url', $validated['url'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce signet existe déjà.',
            ], 422);
        }

        $nextOrder = ($user->bookmarks()->max('sort_order') ?? -1) + 1;

        $bookmark = $user->bookmarks()->create([
            'label'      => $validated['label'],
            'url'        => $validated['url'],
            'icon'       => $validated['icon'] ?? 'fas fa-bookmark',
            'sort_order' => $nextOrder,
        ]);

        return response()->json([
            'success'  => true,
            'bookmark' => $bookmark,
            'message'  => 'Signet ajouté.',
        ]);
    }

    /**
     * Supprime un signet.
     */
    public function destroy(Request $request, UserBookmark $bookmark): JsonResponse
    {
        if ($bookmark->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $bookmark->delete();

        // Ré-indexe l'ordre des signets restants
        Auth::user()->bookmarks()->orderBy('sort_order')->get()
            ->each(function ($bm, $index) {
                $bm->update(['sort_order' => $index]);
            });

        return response()->json(['success' => true, 'message' => 'Signet supprimé.']);
    }

    /**
     * Réordonne les signets (drag & drop).
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
        ]);

        $user = Auth::user();
        $validIds = $user->bookmarks()->pluck('id')->flip();

        foreach ($validated['ids'] as $index => $id) {
            if ($validIds->has($id)) {
                UserBookmark::where('id', $id)->where('user_id', $user->id)
                    ->update(['sort_order' => $index]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Ordre mis à jour.']);
    }

    /**
     * Sauvegarde la configuration des widgets du dashboard (§8.15).
     */
    public function saveDashboardConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widget_order'   => 'sometimes|array',
            'hidden_widgets' => 'sometimes|array',
        ]);

        $config = UserDashboardConfig::forUser(Auth::id());

        if (array_key_exists('widget_order', $validated)) {
            $config->widget_order = $validated['widget_order'];
        }
        if (array_key_exists('hidden_widgets', $validated)) {
            $config->hidden_widgets = $validated['hidden_widgets'];
        }
        $config->save();

        return response()->json([
            'success' => true,
            'config'  => $config,
            'message' => 'Configuration du dashboard enregistrée.',
        ]);
    }

    /**
     * Récupère la configuration des widgets du dashboard.
     */
    public function getDashboardConfig(): JsonResponse
    {
        $config = UserDashboardConfig::forUser(Auth::id());

        return response()->json([
            'success' => true,
            'config'  => $config,
        ]);
    }
}
