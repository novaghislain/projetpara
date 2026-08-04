<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion de l'équipe du cabinet.
 *
 * Affiche la page de gestion des membres de l'équipe du cabinet
 * comptable, permettant de visualiser et d'administrer les
 * collaborateurs.
 */
class TeamController extends Controller
{
    /**
     * Affiche la liste des membres de l'équipe.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('gel-accountant.team.index', ['currentSection' => 'team', 'currentPage' => 'equipe']);
    }
}
