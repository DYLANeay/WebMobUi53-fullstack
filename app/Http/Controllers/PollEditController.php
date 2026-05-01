<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\Request;

class PollEditController extends Controller
{
    /**
     * Single action controller : affiche la page d'édition d'un sondage.
     */
    public function __invoke(Request $request, Poll $poll)
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('polls.edit', [
            'poll' => $poll,
            'dashboardUrl' => route('polls.dashboard'),
        ]);
    }
}
