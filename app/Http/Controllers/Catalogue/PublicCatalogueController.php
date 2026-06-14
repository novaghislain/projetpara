<?php

namespace App\Http\Controllers\Catalogue;

use App\Http\Controllers\Controller;
use App\Models\CatalogueCategory;
use App\Models\CatalogueService;
use Illuminate\Http\Request;


class PublicCatalogueController extends Controller
{
    /**
     * Affiche le catalogue complet des services par catégories
     */
    public function index()
    {
        $categories = CatalogueCategory::with(['services' => function ($query) {
            $query->where('actif', true)->orderBy('ordre_affichage');
        }])
        ->where('actif', true)
        ->orderBy('ordre')
        ->get();

        return view('app', [
            'page' => 'public-catalogue-index',
            'props' => ['categories' => $categories]
        ]);
    }

    /**
     * Affiche les détails d'un service spécifique
     */
    public function show($category_id, $service_id)
    {
        $category = CatalogueCategory::findOrFail($category_id);
        $service = CatalogueService::where('category_id', $category_id)
            ->where('id', $service_id)
            ->where('actif', true)
            ->firstOrFail();

        return view('app', [
            'page' => 'public-catalogue-show',
            'props' => [
                'category' => $category,
                'service' => $service
            ]
        ]);
    }
}
