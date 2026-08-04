<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\ItKnowledgeBase;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ItKnowledgeBaseController extends Controller
{
    /**
     * Contrôleur de la base de connaissances IT.
     * Permet de gérer les articles de la base de connaissances :
     * création, modification, consultation et suppression d'articles
     * techniques destinés aux équipes IT.
     */

    /**
     * Liste paginée des articles de la base de connaissances avec filtres.
     *
     * @param Request $request La requête HTTP avec les filtres (catégorie, recherche)
     * @return View
     */
    public function index(Request $request): View
    {
        $query = ItKnowledgeBase::query();

        // Filtre par catégorie
        if ($request->filled('category')) $query->where('category', $request->category);
        // Recherche textuelle dans le titre
        if ($request->filled('search')) $query->where('title', 'like', '%'.$request->search.'%');

        $articles = $query->latest()->paginate(20);
        $categories = ItKnowledgeBase::distinct()->pluck('category')->filter();
        return view('app', ['page' => 'gel-it-knowledge-base', 'props' => compact('articles', 'categories')]);
    }

    /**
     * Affiche le formulaire de création d'un article.
     *
     * @return View
     */
    public function create(): View
    {
        return view('app', ['page' => 'gel-it-knowledge-base-form']);
    }

    /**
     * Enregistre un nouvel article dans la base de connaissances.
     *
     * @param Request $request La requête HTTP avec les données de l'article
     * @return RedirectResponse Redirection vers la liste
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        // Génération du slug unique à partir du titre
        $validated['slug'] = Str::slug($validated['title']).'-'.uniqid();
        $validated['created_by'] = auth()->id();
        $validated['tags'] = $validated['tags'] ?? [];

        $article = ItKnowledgeBase::create($validated);
        AuditTrailService::log($article, 'created', null, $validated, 'Article KB créé');

        return redirect()->route('gel.it-knowledge-base.index')->with('success', 'Article créé.');
    }

    /**
     * Affiche le détail d'un article et incrémente le compteur de vues.
     *
     * @param ItKnowledgeBase $article L'article à afficher (injection de modèle)
     * @return View
     */
    public function show(ItKnowledgeBase $article): View
    {
        $article->increment('views');
        return view('app', ['page' => 'gel-it-knowledge-base-show', 'props' => compact('article')]);
    }

    /**
     * Met à jour un article existant.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param ItKnowledgeBase $article L'article à modifier (injection de modèle)
     * @return RedirectResponse Redirection vers la liste
     */
    public function update(Request $request, ItKnowledgeBase $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $old = $article->getAttributes();
        $article->update($validated);
        AuditTrailService::log($article, 'updated', $old, $article->getAttributes(), 'Article KB mis à jour');

        return redirect()->route('gel.it-knowledge-base.index')->with('success', 'Article mis à jour.');
    }

    /**
     * Supprime un article de la base de connaissances.
     *
     * @param ItKnowledgeBase $article L'article à supprimer (injection de modèle)
     * @return RedirectResponse Redirection vers la liste
     */
    public function destroy(ItKnowledgeBase $article): RedirectResponse
    {
        $old = $article->getAttributes();
        $article->delete();
        AuditTrailService::log($article, 'deleted', $old, null, 'Article KB supprimé');
        return redirect()->route('gel.it-knowledge-base.index')->with('success', 'Article supprimé.');
    }
}
