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
            "question" => "required|string|min:3|max:500",
            "title" => "nullable|string|max:255",
            "options" => "required|array|min:2|max:20",
            "options.*.label" => "required|string|min:1|max:255|distinct:ignore_case",
            "allow_multiple_choices" => "boolean",
            "allow_vote_change" => "boolean",
            "results_public" => "boolean",
            "duration" => "nullable|integer|min:60|max:604800",
        ], [
            "options.*.label.distinct" => "Chaque option doit être unique (doublon détecté).",
        ]);

        $poll = DB::transaction(function () use ($validated, $request) {
            $poll = Poll::create([
                "user_id" => $request->user()->id,
                "question" => $validated["question"],
                "title" => $validated["title"] ?? null,
                "is_draft" => true,
                "allow_multiple_choices" =>
                    $validated["allow_multiple_choices"] ?? false,
                "allow_vote_change" => $validated["allow_vote_change"] ?? false,
                "results_public" => $validated["results_public"] ?? false,
                "duration" => $validated["duration"] ?? null,
            ]);

            $poll->options()->createMany($validated["options"]);

            return $poll;
        });

        return response()->json($poll->load("options"), 201);
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
     * Start the specified poll (owner only).
     */
    public function start(Request $request, Poll $poll)
    {
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(["message" => "Unauthorized."], 403);
        }

        if (!$poll->is_draft) {
            return response()->json(
                ["message" => "Poll is already started."],
                409,
            );
        }

        $poll->is_draft = false;
        $poll->started_at = now();

        if ($poll->duration) {
            $poll->ends_at = now()->addSeconds($poll->duration);
        }

        $poll->save();

        // recharge les options pour retourner le poll complet
        $poll->load("options");
        $poll->makeHidden("secret_token");

        return response()->json($poll);
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(Request $request, string $token)
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

        $poll->makeHidden("secret_token");

        // Ne pas fuiter les compteurs si l'appelant n'a pas le droit.
        if (!$poll->canShowResultsTo($request->user())) {
            $poll->options->each->makeHidden("votes_count");
        }

        return $poll;
    }

    /**
     * Public results for a poll (counts per option, total, ended state).
     * Endpoint appelé par le polling frontend (@usePolling) toutes les 5s.
     */
    public function results(Request $request, string $token)
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

        if (!$poll->canShowResultsTo($request->user())) {
            return response()->json(
                ["message" => "Results are not public."],
                403,
            );
        }

        // Somme calculée côté API
        $totalVotes = $poll->options->sum("votes_count");
        $hasEnded = $poll->ends_at && now()->greaterThan($poll->ends_at);

        return response()->json([
            "options" => $poll->options->map(
                fn($option) => [
                    "id" => $option->id,
                    "label" => $option->label,
                    "votes_count" => $option->votes_count,
                ],
            ),
            "total_votes" => $totalVotes,
            "has_ended" => $hasEnded,
            "ends_at" => $poll->ends_at,
        ]);
    }

    /**
     * Update the specified poll (authenticated owner only).
     */
    public function update(Request $request, Poll $poll)
    {
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(["message" => "Unauthorized."], 403);
        }

        if (!$poll->is_draft) {
            return response()->json(
                ["message" => "Cannot edit a started poll."],
                409,
            );
        }

        $validated = $request->validate([
            "question" => "required|string|min:3|max:500",
            "title" => "nullable|string|max:255",
            "options" => "required|array|min:2|max:20",
            "options.*.id" => "nullable|integer|exists:poll_options,id",
            "options.*.label" => "required|string|min:1|max:255|distinct:ignore_case",
            "allow_multiple_choices" => "boolean",
            "allow_vote_change" => "boolean",
            "results_public" => "boolean",
            "duration" => "nullable|integer|min:60|max:604800",
        ], [
            "options.*.label.distinct" => "Chaque option doit être unique (doublon détecté).",
        ]);
        // Detecte les options a supprimer (presentes en base mais absentes du payload)
        $existingIds = $poll->options()->pluck("id")->toArray();
        // send by front
        $payloadIds = collect($validated["options"])
            ->pluck("id")
            ->filter()
            ->toArray();
        // id des options existantes en bdd
        $toDelete = array_diff($existingIds, $payloadIds);

        DB::transaction(function () use ($validated, $poll, $toDelete) {
            // upd du poll
            $poll->update([
                "question" => $validated["question"],
                "title" => $validated["title"] ?? null,
                "allow_multiple_choices" =>
                    $validated["allow_multiple_choices"] ?? false,
                "allow_vote_change" => $validated["allow_vote_change"] ?? false,
                "results_public" => $validated["results_public"] ?? false,
                "duration" => $validated["duration"] ?? null,
            ]);

            // suppr options
            if (!empty($toDelete)) {
                $poll->options()->whereIn("id", $toDelete)->delete();
            }

            foreach ($validated["options"] as $opt) {
                if (!empty($opt["id"])) {
                    $poll
                        ->options()
                        ->where("id", $opt["id"])
                        ->update([
                            "label" => $opt["label"],
                        ]);
                } else {
                    $poll->options()->create(["label" => $opt["label"]]);
                }
            }
        });

        // recharge le poll avec les options mises a jour
        return response()->json($poll->load("options"));
    }
}
