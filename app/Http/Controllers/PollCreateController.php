<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PollCreateController extends Controller
{
    // __invoke() est la méthode appelée quand on utilise le contrôleur comme callable.
    // Permet d'enregistrer la route avec juste le nom de la classe (pas besoin de préciser
    // la méthode) : Route::get('/polls/create', PollCreateController::class)
    // C'est le pattern "single action controller" de Laravel.
    public function __invoke(Request $request)
    {
        return view('polls.create', [
            // Passe l'URL du dashboard à la vue Blade, qui l'injecte dans data-props
            // pour que le composant Vue puisse rediriger après création.
            'dashboardUrl' => route('polls.dashboard'),
        ]);
    }
}
