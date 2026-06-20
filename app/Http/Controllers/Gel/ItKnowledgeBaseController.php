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
    public function index(Request $request): View
    {
        $query = ItKnowledgeBase::query();
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('search')) $query->where('title', 'like', '%'.$request->search.'%');

        $articles = $query->latest()->paginate(20);
        $categories = ItKnowledgeBase::distinct()->pluck('category')->filter();
        return view('app', ['page' => 'gel-it-knowledge-base', 'props' => compact('articles', 'categories')]);
    }

    public function create(): View
    {
        return view('app', ['page' => 'gel-it-knowledge-base-form']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.uniqid();
        $validated['created_by'] = auth()->id();
        $validated['tags'] = $validated['tags'] ?? [];

        $article = ItKnowledgeBase::create($validated);
        AuditTrailService::log($article, 'created', null, $validated, 'Article KB créé');

        return redirect()->route('gel.it-knowledge-base.index')->with('success', 'Article créé.');
    }

    public function show(ItKnowledgeBase $article): View
    {
        $article->increment('views');
        return view('app', ['page' => 'gel-it-knowledge-base-show', 'props' => compact('article')]);
    }

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

    public function destroy(ItKnowledgeBase $article): RedirectResponse
    {
        $old = $article->getAttributes();
        $article->delete();
        AuditTrailService::log($article, 'deleted', $old, null, 'Article KB supprimé');
        return redirect()->route('gel.it-knowledge-base.index')->with('success', 'Article supprimé.');
    }
}
