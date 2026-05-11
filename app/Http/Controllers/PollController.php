<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * Dashboard : liste des sondages de la personne connectée.
     */
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

        return view('polls.dashboard', [
            'polls' => $polls,
        ]);
    }

    /**
     * Page de création d'un sondage (monte l'app Vue poll-edit en mode "create").
     */
    public function create()
    {
        return view('polls.create', [
            'dashboardUrl' => route('polls.dashboard'),
        ]);
    }

    /**
     * Page d'édition d'un sondage (owner uniquement).
     */
    public function edit(Request $request, Poll $poll)
    {
        if ($poll->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('polls.edit', [
            'poll' => $poll,
            'dashboardUrl' => route('polls.dashboard'),
        ]);
    }

    /**
     * Page publique de vote / résultats accessible via le secret_token.
     */
    public function show(Request $request, string $token)
    {
        $poll = Poll::with([
            'options' => function ($query) {
                $query->withCount('votes');
            },
        ])
            ->where('secret_token', $token)
            ->first();

        if (!$poll) {
            abort(404);
        }

        $user = $request->user();
        $votedOptionIds = [];
        if ($user) {
            $votedOptionIds = PollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->pluck('poll_option_id')
                ->toArray();
        }

        $isOwner = $user !== null && $user->id === $poll->user_id;

        if (!$poll->canShowResultsTo($user)) {
            $poll->options->each->makeHidden('votes_count');
        }

        return view('polls.show', [
            'poll' => $poll,
            'hasVoted' => !empty($votedOptionIds),
            'votedOptionIds' => $votedOptionIds,
            'isOwner' => $isOwner,
        ]);
    }
}
