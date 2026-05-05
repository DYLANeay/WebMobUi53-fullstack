<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poll extends Model
{
    protected $fillable = [
        "user_id",
        "title",
        "question",
        "secret_token",
        "is_draft",
        "allow_multiple_choices",
        "allow_vote_change",
        "results_public",
        "duration",
        "started_at",
        "ends_at",
    ];

    protected $casts = [
        "is_draft" => "boolean",
        "allow_multiple_choices" => "boolean",
        "allow_vote_change" => "boolean",
        "results_public" => "boolean",
        "duration" => "integer",
        //afin d'avoir quelque chose de : 2026-05-04T14:30:00.000000Z
        // => facilement usable par le front
        "started_at" => "datetime",
        "ends_at" => "datetime",
    ];

    // boot() est appelé une seule fois par Eloquent au chargement du modèle.
    protected static function boot(): void
    {
        parent::boot(); // obligatoire,initialise les listeners internes d'Eloquent

        // Déclenché juste avant chaque INSERT : on génère le token de partage public
        static::creating(function (Poll $poll) {
            if (empty($poll->secret_token)) {
                // Boucle de garde : génère un token, vérifie en base qu'aucun poll
                // ne l'utilise déjà (self = la classe Poll elle-même), recommence si collision.
                do {
                    $token = Str::random(32);
                } while (self::where("secret_token", $token)->exists());

                $poll->secret_token = $token;
            }
        });
    }

    /**
     * Get the user that owns the poll.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the options for the poll.
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    /**
     * Get the votes for the poll.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}
