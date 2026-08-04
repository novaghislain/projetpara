<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des articles (blog/knowledge base).
 * Opérations CRUD complètes avec support JSON pour les appels API
 * et rendu Blade pour les pages web.
 */
class ArticleController extends Controller
{
    /**
     * Affiche la liste paginée des articles.
     * Supporte le filtrage par catégorie, la recherche plein texte
     * et le format de réponse JSON pour les appels API.
     *
     * @param Request $request La requête HTTP contenant les filtres (category, search, per_page)
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Article::latest();
        // Filtrer par catégorie si spécifié
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        // Recherche plein texte sur le titre et le contenu
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        // Réponse JSON pour les appels API (avec limite de 100 éléments max)
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 20), 100);
            return response()->json($query->paginate($perPage));
        }

        $articles = $query->paginate(20);
        $categories = Article::select('category')->distinct()->pluck('category');
        return view('app', ['page' => 'gel-articles', 'props' => compact('articles', 'categories')]);
    }

    /**
     * Affiche le formulaire de création d'un nouvel article.
     *
     * @return View
     */
    public function create(): View
    {
        return view('app', ['page' => 'gel-articles-form']);
    }

    /**
     * Enregistre un nouvel article dans la base de données.
     * Gère les tags, le statut de publication et les réponses JSON/HTML.
     *
     * @param Request $request La requête HTTP contenant les données de l'article
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|max:500',
            'category' => 'nullable|max:100',
            'author' => 'nullable|max:100',
            'reading_minutes' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
        ]);
        $data['tags'] = $request->tags ?? [];
        $data['is_published'] = $request->boolean('is_published');
        // Définir la date de publication si l'article est publié immédiatement
        $data['published_at'] = $data['is_published'] ? now() : null;
        $article = Article::create($data);

        if ($request->wantsJson()) {
            return response()->json($article->fresh(), 201);
        }

        return redirect()->route('gel.articles.index')->with('success', 'Article créé.');
    }

    /**
     * Affiche le détail d'un article.
     *
     * @param Request $request La requête HTTP
     * @param Article $article L'article à afficher (injection de modèle)
     * @return View|JsonResponse
     */
    public function show(Request $request, Article $article): View|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json($article);
        }

        return view('app', ['page' => 'gel-articles-show', 'props' => ['articleId' => $article->id]]);
    }

    /**
     * Affiche le formulaire d'édition d'un article existant.
     *
     * @param Article $article L'article à modifier (injection de modèle)
     * @return View
     */
    public function edit(Article $article): View
    {
        return view('app', ['page' => 'gel-articles-form', 'props' => compact('article')]);
    }

    /**
     * Met à jour un article existant.
     * Si l'article est marqué comme publié et ne l'était pas encore,
     * la date de publication est définie automatiquement.
     *
     * @param Request $request La requête HTTP contenant les données mises à jour
     * @param Article $article L'article à modifier (injection de modèle)
     * @return RedirectResponse|JsonResponse
     */
    public function update(Request $request, Article $article): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|max:500',
            'category' => 'nullable|max:100',
            'author' => 'nullable|max:100',
            'reading_minutes' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
        ]);
        $data['tags'] = $request->tags ?? [];
        $data['is_published'] = $request->boolean('is_published');
        // Définir la date de publication uniquement si l'article vient d'être publié
        if ($data['is_published'] && !$article->published_at) {
            $data['published_at'] = now();
        }
        $article->update($data);

        if ($request->wantsJson()) {
            return response()->json($article->fresh());
        }

        return redirect()->route('gel.articles.index')->with('success', 'Article mis à jour.');
    }

    /**
     * Supprime un article définitivement.
     *
     * @param Request $request La requête HTTP
     * @param Article $article L'article à supprimer (injection de modèle)
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, Article $article): RedirectResponse|JsonResponse
    {
        $article->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Article supprimé.']);
        }

        return redirect()->route('gel.articles.index')->with('success', 'Article supprimé.');
    }
}
