<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ============================================================
            // 1. USERS (2 users)
            // ============================================================
            DB::table('users')->insert([
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                    'email' => 'john.doe@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'username' => 'janedoe',
                    'email' => 'jane.doe@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // ============================================================
            // 2. POLLS (30 polls — mix de brouillons, en cours, expirés)
            // ============================================================
            $pollTemplates = [
                ['Technologie', 'Quel est votre langage de programmation préféré ?', ['PHP', 'JavaScript', 'Python', 'Rust', 'Go', 'Java']],
                ['Alimentation', 'Quelle cuisine du monde préférez-vous ?', ['Japonaise', 'Italienne', 'Mexicaine', 'Indienne', 'Thaïlandaise', 'Française']],
                ['Sport', 'Quel sport pratiquez-vous le plus souvent ?', ['Football', 'Tennis', 'Natation', 'Cyclisme', 'Course à pied', 'Musculation']],
                ['Musique', 'Quel genre musical écoutez-vous en travaillant ?', ['Jazz', 'Rock', 'Classique', 'Lo-fi', 'Électro', 'Hip-hop']],
                ['Transport', 'Comment venez-vous au travail ?', ['Voiture', 'Vélo', 'Transports en commun', 'À pied', 'Trottinette', 'Covoiturage']],
                ['Cinéma', 'Quel genre de film regardez-vous le plus ?', ['Action', 'Comédie', 'Science-fiction', 'Documentaire', 'Horreur', 'Drame']],
                ['Environnement', 'Comment réduisez-vous votre empreinte carbone ?', ['Vélo / marche', 'Alimentation végétarienne', 'Réduction des déchets', 'Énergies renouvelables', 'Zéro déchet', 'Seconde main']],
                ['Santé', 'Combien d\'heures dormez-vous par nuit ?', ['Moins de 6h', '6-7h', '7-8h', 'Plus de 8h']],
                ['Voyage', 'Quelle destination de voyage rêvez-vous visiter ?', ['Japon', 'Islande', 'Nouvelle-Zélande', 'Pérou', 'Norvège', 'Costa Rica']],
                [null, 'Préférez-vous le café ou le thé ?', ['Café', 'Thé', 'Les deux', 'Aucun']],
                ['Technologie', 'Quel système d\'exploitation utilisez-vous ?', ['Linux', 'macOS', 'Windows', 'Chrome OS']],
                ['Lecture', 'Quel genre de livres lisez-vous ?', ['Roman', 'Science-fiction', 'Essai', 'BD / Manga', 'Biographie', 'Policier']],
                ['Réseaux sociaux', 'Quelle plateforme utilisez-vous le plus ?', ['Instagram', 'Twitter/X', 'TikTok', 'LinkedIn', 'Bluesky', 'Mastodon']],
                [null, 'Travaillez-vous mieux le matin ou le soir ?', ['Matin', 'Soir', 'Ça dépend', 'Je suis toujours productif']],
                ['Animaux', 'Avez-vous un animal de compagnie ?', ['Chat', 'Chien', 'Poisson', 'Oiseau', 'Rongeur', 'Aucun']],
                ['Jeux vidéo', 'Sur quelle plateforme jouez-vous ?', ['PC', 'PlayStation', 'Nintendo Switch', 'Xbox', 'Mobile', 'VR']],
                ['Gastronomie', 'Quel repas de la journée préférez-vous ?', ['Petit-déjeuner', 'Déjeuner', 'Dîner', 'Le goûter']],
                ['Mode', 'Quel style vestimentaire adoptez-vous au quotidien ?', ['Casual', 'Sportswear', 'Formel', 'Streetwear', 'Minimaliste', 'Vintage']],
                ['Éducation', 'Quelle méthode d\'apprentissage préférez-vous ?', ['Vidéos en ligne', 'Livres', 'Pratique directe', 'Cours en présentiel', 'Mentorat', 'Documentation']],
                ['Loisirs', 'Comment passez-vous votre temps libre ?', ['Sport', 'Jeux vidéo', 'Lecture', 'Sorties / voyages', 'Bricolage', 'Bénévolat']],
                ['Finance', 'Comment gérez-vous votre budget ?', ['Excel / Sheets', 'App dédiée', 'Mémoire', 'Enveloppe', 'Aucune méthode', 'Comptable']],
                ['Télétravail', 'Combien de jours par semaine en télétravail ?', ['0 (présentiel)', '1-2 jours', '3 jours', '4 jours', '100 % télétravail', 'Freelance']],
                ['Mobilité', 'Quel est votre prochain achat tech ?', ['Smartphone', 'Ordinateur', 'Tablette', 'Montre connectée', 'Casque audio', 'Aucun']],
                ['Climat', 'Quelle saison préférez-vous ?', ['Printemps', 'Été', 'Automne', 'Hiver']],
                ['Productivité', 'Quel outil de productivité utilisez-vous ?', ['Notion', 'Trello', 'Todoist', 'Obsidian', 'Papier + stylo', 'Aucun']],
                ['Web', 'Quel framework web préférez-vous ?', ['Laravel', 'Django', 'Rails', 'Express', 'Spring Boot', 'ASP.NET']],
                ['Design', 'Quel logiciel de design utilisez-vous ?', ['Figma', 'Adobe XD', 'Sketch', 'Photoshop', 'Illustrator', 'Canva']],
                ['Bien-être', 'Quelle activité pour vous détendre ?', ['Méditation', 'Yoga', 'Marche', 'Musique', 'Lecture', 'Netflix']],
                ['Crypto', 'Possédez-vous des cryptomonnaies ?', ['Bitcoin', 'Ethereum', 'Solana', 'Aucune', 'Plusieurs', 'Stablecoins uniquement']],
                ['Voiture', 'Quel type de motorisation pour votre prochaine voiture ?', ['Électrique', 'Hybride', 'Essence', 'Diesel', 'Hydrogène', 'Pas de voiture']],
            ];

            $pollIds = [];
            foreach ($pollTemplates as $idx => [$title, $question, $options]) {
                $isDraft = $idx < 3;                         // 3 brouillons
                $allowMultiple = ($idx % 5 === 0);           // 1 sur 5 = choix multiple
                $allowChange = ($idx % 7 === 0);             // 1 sur 7 = vote changeable
                $resultsPublic = ($idx % 3 !== 0);           // 2 sur 3 = résultats publics
                $duration = ($idx % 4 === 0) ? ($idx + 1) * 1800 : null;

                if ($isDraft) {
                    $startedAt = null;
                    $endsAt = null;
                } else {
                    $startedAt = now()->subDays(rand(1, 10))->subHours(rand(0, 23));
                    $endsAt = $duration
                        ? (clone $startedAt)->addSeconds($duration)
                        : null;
                }

                $pollId = DB::table('polls')->insertGetId([
                    'user_id' => ($idx % 2 === 0) ? 1 : 2,
                    'title' => $title,
                    'question' => $question,
                    'secret_token' => Str::random(32),
                    'is_draft' => $isDraft,
                    'allow_multiple_choices' => $allowMultiple,
                    'allow_vote_change' => $allowChange,
                    'results_public' => $resultsPublic,
                    'duration' => $duration,
                    'started_at' => $startedAt,
                    'ends_at' => $endsAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $pollIds[$pollId] = $options;
            }

            // ============================================================
            // 3. OPTIONS (toutes les options de tous les polls)
            // ============================================================
            $optionIdsByPoll = [];
            foreach ($pollIds as $pollId => $optionLabels) {
                foreach ($optionLabels as $label) {
                    $optionId = DB::table('poll_options')->insertGetId([
                        'poll_id' => $pollId,
                        'label' => $label,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $optionIdsByPoll[$pollId][] = $optionId;
                }
            }

            // ============================================================
            // 4. VOTES (~800 votes répartis sur les polls lancés)
            // ============================================================
            $launchedPollIds = DB::table('polls')
                ->where('is_draft', false)
                ->pluck('id')
                ->toArray();

            $votes = [];
            $voteCount = 0;
            $maxVotes = 800;

            for ($v = 0; $v < $maxVotes; $v++) {
                $pollId = $launchedPollIds[array_rand($launchedPollIds)];
                $availableOptions = $optionIdsByPoll[$pollId];

                // Choix unique : 1 option. Choix multiple : 1 à 3 options.
                $poll = DB::table('polls')->where('id', $pollId)->first();
                $numChoices = $poll->allow_multiple_choices
                    ? rand(1, min(3, count($availableOptions)))
                    : 1;

                $chosenOptions = array_rand(array_flip($availableOptions), $numChoices);
                $chosenOptions = is_array($chosenOptions)
                    ? $chosenOptions
                    : [$chosenOptions];

                foreach ($chosenOptions as $optionId) {
                    // Vérifier unicité pour les sondages en choix unique
                    if (! $poll->allow_multiple_choices) {
                        $alreadyExists = DB::table('poll_votes')
                            ->where('poll_id', $pollId)
                            ->where('user_id', 1)
                            ->exists();
                        if ($alreadyExists) {
                            continue;
                        }
                    }

                    $votes[] = [
                        'poll_id' => $pollId,
                        'user_id' => ($v % 2 === 0) ? 1 : 2,
                        'poll_option_id' => $optionId,
                        'created_at' => now()->subMinutes(rand(0, 1440)),
                        'updated_at' => now()->subMinutes(rand(0, 1440)),
                    ];

                    $voteCount++;
                    if ($voteCount >= $maxVotes) {
                        break 2;
                    }
                }
            }

            // Insert par chunks pour éviter de dépasser les limites SQL
            foreach (array_chunk($votes, 200) as $chunk) {
                DB::table('poll_votes')->insert($chunk);
            }
        });
    }
}
