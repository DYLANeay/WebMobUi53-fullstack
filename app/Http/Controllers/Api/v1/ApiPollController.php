<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;

class ApiPollController extends Controller
{
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
