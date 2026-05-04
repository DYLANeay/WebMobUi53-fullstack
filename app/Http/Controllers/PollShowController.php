<?php

namespace App\Http\Controllers;

use App\Models\Poll;
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

        return view("polls.show", [
            "poll" => $poll,
        ]);
    }
}
