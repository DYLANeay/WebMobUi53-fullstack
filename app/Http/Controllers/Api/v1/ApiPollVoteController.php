<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiPollVoteController extends Controller
{
    /**
     * Submit a vote on a poll (authenticated user)
     */
    public function store(Request $request, string $token)
    {
        $poll = Poll::with("options")->where("secret_token", $token)->first();

        if (!$poll) {
            return response()->json(["message" => "Poll not found."], 404);
        }

        if ($poll->is_draft) {
            return response()->json(
                ["message" => "Poll is not started yet."],
                403,
            );
        }

        if ($poll->ends_at && now()->greaterThan($poll->ends_at)) {
            return response()->json(["message" => "Poll has ended."], 403);
        }

        $validated = $request->validate([
            "options" => "required|array|min:1",
            // "pour chaque élément d'options[], ..."
            "options.*" => "integer|exists:poll_options,id",
        ]);

        $optionIds = collect($validated["options"])
            // apply intval to each element to ensure they are integers
            ->map("intval")
            // valide aucun doublons
            ->unique()
            ->values();

        // valide que les options appartiennent bien à ce sondage
        $validOptionIds = $poll->options->pluck("id");
        if ($optionIds->diff($validOptionIds)->isNotEmpty()) {
            return response()->json(
                ["message" => "Invalid option for this poll."],
                422,
            );
        }

        // sondage max 1 vote
        if (!$poll->allow_multiple_choices && $optionIds->count() > 1) {
            return response()->json(
                ["message" => "Only one choice is allowed."],
                422,
            );
        }

        $userId = $request->user()->id;

        DB::transaction(function () use ($poll, $optionIds, $userId) {
            if (!$poll->allow_multiple_choices) {
                $existing = PollVote::where("poll_id", $poll->id)
                    ->where("user_id", $userId)
                    ->first();
                if ($existing && !$poll->allow_vote_change) {
                    abort(409, "Vous avez déjà voté pour ce sondage.");
                }
                $existing?->delete();
            } else {
                // Multiple choice mode: remove votes for options
                // that are no longer selected (if changing vote)
                PollVote::where("poll_id", $poll->id)
                    ->where("user_id", $userId)
                    ->whereNotIn("poll_option_id", $optionIds)
                    ->delete();

                // Keep only options that are not already voted
                $alreadyVoted = PollVote::where("poll_id", $poll->id)
                    ->where("user_id", $userId)
                    ->whereIn("poll_option_id", $optionIds)
                    ->pluck("poll_option_id")
                    ->toArray();

                $optionIds = $optionIds->diff($alreadyVoted)->values();
            }

            foreach ($optionIds as $optionId) {
                PollVote::create([
                    "poll_id" => $poll->id,
                    "user_id" => $userId,
                    "poll_option_id" => $optionId,
                ]);
            }
        });

        // Reload poll with options and vote counts for the response
        $poll->load([
            "options" => function ($query) {
                $query->withCount("votes");
            },
        ]);

        $poll->makeHidden("secret_token");

   

        if (!$poll->canShowResultsTo($request->user())) {
            $poll->options->each->makeHidden("votes_count");
        }

        return response()->json($poll);
    }

    // show poll result for the authenticated user
    public function show(Request $request, string $token)
    {
        $poll = Poll::where("secret_token", $token)->first();

        if (!$poll) {
            return response()->json(["message" => "Poll not found."], 404);
        }

        $votes = PollVote::where("poll_id", $poll->id)
            ->where("user_id", $request->user()->id)
            ->pluck("poll_option_id")
            ->toArray();

        return response()->json([
            "has_voted" => !empty($votes),
            "option_ids" => $votes,
        ]);
    }
}
