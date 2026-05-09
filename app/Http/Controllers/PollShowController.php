<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollShowController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $poll = Poll::with([
            "options" => function ($query) {
                $query->withCount("votes");
            },
        ])
            ->where("secret_token", $token)
            ->first();

        if (!$poll) {
            abort(404);
        }

        $user = $request->user();
        $votedOptionIds = [];
        if ($user) {
            //on récupère les options pour lesquelles l'utilisateur a voté (s'il est connecté)
            $votedOptionIds = PollVote::where("poll_id", $poll->id)
                ->where("user_id", $user->id)
                ->pluck("poll_option_id")
                ->toArray();
        }

        $isOwner = $user !== null && $user->id === $poll->user_id;

        //makeHidden cache les votes count des options dans le HTML
        if (!$poll->canShowResultsTo($user)) {
            $poll->options->each->makeHidden("votes_count");
        }

        //on retourne la vue avec le poll et les options pour lesquelles l'utilisateur a voté (pour afficher les résultats si il a déjà voté)
        return view("polls.show", [
            "poll" => $poll,
            "hasVoted" => !empty($votedOptionIds),
            "votedOptionIds" => $votedOptionIds,
            "isOwner" => $isOwner,
        ]);
    }
}
