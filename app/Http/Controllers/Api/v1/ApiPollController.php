<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiPollController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question'               => 'required|string|min:3|max:500',
            'title'                  => 'nullable|string|max:255',
            'options'                => 'required|array|min:2|max:20',
            'options.*.label'        => 'required|string|min:1|max:255',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change'      => 'boolean',
            'results_public'         => 'boolean',
            'duration'               => 'nullable|integer|min:60|max:604800',
        ]);

        $poll = DB::transaction(function () use ($validated, $request) {
            $poll = Poll::create([
                'user_id'                => $request->user()->id,
                'question'               => $validated['question'],
                'title'                  => $validated['title'] ?? null,
                'is_draft'               => true,
                'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? false,
                'allow_vote_change'      => $validated['allow_vote_change'] ?? false,
                'results_public'         => $validated['results_public'] ?? false,
                'duration'               => $validated['duration'] ?? null,
            ]);

            $poll->options()->createMany($validated['options']);

            return $poll;
        });

        return response()->json($poll->load('options'), 201);
    }

    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        $polls = $request
            ->user()
            ->polls()
            ->orderBy("created_at", "desc")
            ->get();

        return $polls;
    }

    public function destroy(Request $request, Poll $poll)
    {
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(["message" => "Unauthorized."], 403);
        }

        $poll->delete();

        return response()->json(["message" => "Poll deleted successfully."]);
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with([
            "options" => function ($query) {
                $query->withCount("votes");
            },
        ])
            ->where("secret_token", $token)
            ->first();

        if (!$poll) {
            return response()->json(["message" => "Poll not found."], 404);
        }

        return $poll;
    }
}
