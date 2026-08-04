<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de l'espace de travail du cabinet comptable.
 *
 * Affiche la vue principale de l'espace de travail où les utilisateurs
 * peuvent accéder à leurs tâches et dossiers en cours.
 */
class WorkController extends Controller
{
    /**
     * Affiche la page de l'espace de travail.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('gel-accountant.work.index', ['currentSection' => 'work', 'currentPage' => 'travail']);
    }
}
