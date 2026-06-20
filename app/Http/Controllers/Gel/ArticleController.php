<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Article::latest();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 20), 100);
            return response()->json($query->paginate($perPage));
        }

        $articles = $query->paginate(20);
        $categories = Article::select('category')->distinct()->pluck('category');
        return view('app', ['page' => 'gel-articles', 'props' => compact('articles', 'categories')]);
    }

    public function create(): View
    {
        return view('app', ['page' => 'gel-articles-form']);
    }

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
        $data['published_at'] = $data['is_published'] ? now() : null;
        $article = Article::create($data);

        if ($request->wantsJson()) {
            return response()->json($article->fresh(), 201);
        }

        return redirect()->route('gel.articles.index')->with('success', 'Article créé.');
    }

    public function show(Request $request, Article $article): View|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json($article);
        }

        return view('app', ['page' => 'gel-articles-show', 'props' => ['articleId' => $article->id]]);
    }

    public function edit(Article $article): View
    {
        return view('app', ['page' => 'gel-articles-form', 'props' => compact('article')]);
    }

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
        if ($data['is_published'] && !$article->published_at) {
            $data['published_at'] = now();
        }
        $article->update($data);

        if ($request->wantsJson()) {
            return response()->json($article->fresh());
        }

        return redirect()->route('gel.articles.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse|JsonResponse
    {
        $article->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Article supprimé.']);
        }

        return redirect()->route('gel.articles.index')->with('success', 'Article supprimé.');
    }
}
