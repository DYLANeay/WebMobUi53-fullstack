<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(
            function () {
                // Insert John Doe into the users table
                DB::table('users')->insert([
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                    'email' => 'john.doe@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => new \DateTime('2026-02-09 10:00:00'),
                    'updated_at' => new \DateTime('2026-02-09 10:00:00'),
                ]);

                // Insert Jane Doe into the users table
                DB::table('users')->insert([
                    'id' => 2,
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'username' => 'janedoe',
                    'email' => 'jane.doe@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => new \DateTime('2026-02-09 11:00:00'),
                    'updated_at' => new \DateTime('2026-02-09 11:00:00'),
                ]);

                // Insert some posts for John Doe
                DB::table('posts')->insert([
                    [
                        'id' => 1,
                        'user_id' => 1,
                        'title' => "John's First Post",
                        'content' => "This is the content of John's first post.",
                        'created_at' => new \DateTime('2026-02-09 12:00:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:00:00'),
                    ],
                    [
                        'id' => 2,
                        'user_id' => 1,
                        'title' => null,
                        'content' => "This is the content of John's second post.",
                        'created_at' => new \DateTime('2026-02-09 12:05:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:05:00'),
                    ],
                    [
                        'id' => 3,
                        'user_id' => 1,
                        'title' => null,
                        'content' => "This is the content of John's third post.",
                        'created_at' => new \DateTime('2026-02-09 12:10:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:10:00'),
                    ]
                ]);

                // Insert some posts for Jane Doe
                DB::table('posts')->insert([
                    [
                        'id' => 4,
                        'user_id' => 2,
                        'title' => null,
                        'content' => "This is the content of Jane's first post.",
                        'created_at' => new \DateTime('2026-02-09 12:05:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:05:00'),
                    ],
                    [
                        'id' => 5,
                        'user_id' => 2,
                        'title' => "Jane's Second Post",
                        'content' => "This is the content of Jane's second post.",
                        'created_at' => new \DateTime('2026-02-09 12:10:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:10:00'),
                    ],
                    [
                        'id' => 6,
                        'user_id' => 2,
                        'title' => "Jane's Third Post",
                        'content' => "This is the content of Jane's third post.",
                        'created_at' => new \DateTime('2026-02-09 12:15:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:15:00'),
                    ]
                ]);

                // Insert some likes for John's posts
                DB::table('likes')->insert([
                    [
                        'user_id' => 2,
                        'post_id' => 1,
                        'reaction' => 'like',
                        'created_at' => new \DateTime('2026-02-09 12:20:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:20:00'),
                    ],
                    [
                        'user_id' => 1, // John likes his own post
                        'post_id' => 2,
                        'reaction' => 'love',
                        'created_at' => new \DateTime('2026-02-09 12:25:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:25:00'),
                    ],
                ]);

                // Insert some likes for Jane's posts
                DB::table('likes')->insert([
                    [
                        'user_id' => 1,
                        'post_id' => 4,
                        'reaction' => 'like',
                        'created_at' => new \DateTime('2026-02-09 12:30:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:30:00'),
                    ],
                    [
                        'user_id' => 1,
                        'post_id' => 5,
                        'reaction' => 'love',
                        'created_at' => new \DateTime('2026-02-09 12:35:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:35:00'),
                    ],
                    [
                        'user_id' => 2, // Jane likes her own post
                        'post_id' => 5,
                        'reaction' => 'wow',
                        'created_at' => new \DateTime('2026-02-09 12:40:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:40:00'),
                    ]
                ]);

                $polls = [
                    [1, 1, 'Technologie', 'Quel est votre langage de programmation préféré ?', true,  false, false, false, null,  null, null, '2026-04-19 10:00:00'],
                    [2, 1, 'Alimentation', 'Quelle cuisine du monde préférez-vous ?',           false, false, false, true,  null,  '2026-04-19 11:00:00', null, '2026-04-19 11:00:00'],
                    [3, 1, 'Sport', 'Quel sport pratiquez-vous le plus souvent ?',               false, true,  false, true,  3600,  '2026-04-20 09:00:00', '2026-04-20 10:00:00', '2026-04-20 09:00:00'],
                    [4, 1, 'Musique', 'Quel genre musical écoutez-vous en travaillant ?',        true,  false, false, false, null,  null, null, '2026-04-20 10:00:00'],
                    [5, 1, 'Transport', 'Comment venez-vous au travail ?',                       false, true,  true,  true,  7200,  '2026-04-21 08:00:00', '2026-04-21 10:00:00', '2026-04-21 08:00:00'],
                    [6, 1, 'Cinéma', 'Quel genre de film regardez-vous le plus ?',               false, false, false, true,  null,  '2026-04-21 12:00:00', null, '2026-04-21 12:00:00'],
                    [7, 1, 'Environnement', 'Comment réduisez-vous votre empreinte carbone ?',   true,  true,  false, false, null,  null, null, '2026-04-22 09:00:00'],
                    [8, 1, 'Santé', 'Combien d\'heures dormez-vous par nuit ?',                  false, false, false, true,  null,  '2026-04-22 10:00:00', null, '2026-04-22 10:00:00'],
                    [9, 1, 'Voyage', 'Quelle destination de voyage rêvez-vous visiter ?',        false, false, true,  false, 86400, '2026-04-23 00:00:00', '2026-04-24 00:00:00', '2026-04-23 00:00:00'],
                    [10, 1, null, 'Préférez-vous le café ou le thé ?',                           false, false, false, true,  null,  '2026-04-23 08:00:00', null, '2026-04-23 08:00:00'],
                    [11, 2, 'Technologie', 'Quel système d\'exploitation utilisez-vous ?',       false, false, false, true,  null,  '2026-04-19 14:00:00', null, '2026-04-19 14:00:00'],
                    [12, 2, 'Lecture', 'Quel genre de livres lisez-vous ?',                      true,  true,  false, false, null,  null, null, '2026-04-20 09:00:00'],
                    [13, 2, 'Réseaux sociaux', 'Quelle plateforme utilisez-vous le plus ?',      false, false, false, true,  3600,  '2026-04-20 15:00:00', '2026-04-20 16:00:00', '2026-04-20 15:00:00'],
                    [14, 2, null, 'Travaillez-vous mieux le matin ou le soir ?',                 false, false, true,  true,  null,  '2026-04-21 10:00:00', null, '2026-04-21 10:00:00'],
                    [15, 2, 'Animaux', 'Avez-vous un animal de compagnie ?',                     true,  false, false, false, null,  null, null, '2026-04-22 11:00:00'],
                    [16, 2, 'Jeux vidéo', 'Sur quelle plateforme jouez-vous ?',                  false, true,  false, true,  null,  '2026-04-22 14:00:00', null, '2026-04-22 14:00:00'],
                    [17, 2, 'Gastronomie', 'Quel repas de la journée préférez-vous ?',           false, false, false, true,  null,  '2026-04-23 09:00:00', null, '2026-04-23 09:00:00'],
                    [18, 2, 'Mode', 'Quel style vestimentaire adoptez-vous au quotidien ?',      true,  true,  false, false, null,  null, null, '2026-04-23 10:00:00'],
                    [19, 1, 'Éducation', 'Quelle méthode d\'apprentissage préférez-vous ?',      false, false, false, true,  7200,  '2026-04-24 10:00:00', '2026-04-24 12:00:00', '2026-04-24 10:00:00'],
                    [20, 2, 'Loisirs', 'Comment passez-vous votre temps libre ?',                false, true,  true,  true,  null,  '2026-04-24 14:00:00', null, '2026-04-24 14:00:00'],
                ];

                foreach ($polls as [$id, $userId, $title, $question, $isDraft, $allowMultiple, $allowChange, $resultsPublic, $duration, $startedAt, $endsAt, $createdAt]) {
                    DB::table('polls')->insert([
                        'id'                    => $id,
                        'user_id'               => $userId,
                        'title'                 => $title,
                        'question'              => $question,
                        'secret_token'          => Str::random(32),
                        'is_draft'              => $isDraft,
                        'allow_multiple_choices'=> $allowMultiple,
                        'allow_vote_change'     => $allowChange,
                        'results_public'        => $resultsPublic,
                        'duration'              => $duration,
                        'started_at'            => $startedAt,
                        'ends_at'               => $endsAt,
                        'created_at'            => new \DateTime($createdAt),
                        'updated_at'            => new \DateTime($createdAt),
                    ]);
                }

                $options = [
                    // Poll 1 - langages
                    [1, 'PHP'], [1, 'JavaScript'], [1, 'Python'], [1, 'Rust'],
                    // Poll 2 - cuisine
                    [2, 'Japonaise'], [2, 'Italienne'], [2, 'Mexicaine'], [2, 'Indienne'],
                    // Poll 3 - sport
                    [3, 'Football'], [3, 'Tennis'], [3, 'Natation'], [3, 'Cyclisme'],
                    // Poll 4 - musique
                    [4, 'Jazz'], [4, 'Rock'], [4, 'Classique'], [4, 'Lo-fi'],
                    // Poll 5 - transport
                    [5, 'Voiture'], [5, 'Vélo'], [5, 'Transports en commun'], [5, 'À pied'],
                    // Poll 6 - cinéma
                    [6, 'Action'], [6, 'Comédie'], [6, 'Science-fiction'], [6, 'Documentaire'],
                    // Poll 7 - environnement
                    [7, 'Vélo / marche'], [7, 'Alimentation végétarienne'], [7, 'Réduction des déchets'], [7, 'Énergies renouvelables'],
                    // Poll 8 - sommeil
                    [8, 'Moins de 6h'], [8, '6-7h'], [8, '7-8h'], [8, 'Plus de 8h'],
                    // Poll 9 - voyage
                    [9, 'Japon'], [9, 'Islande'], [9, 'Nouvelle-Zélande'], [9, 'Pérou'],
                    // Poll 10 - café/thé
                    [10, 'Café'], [10, 'Thé'],
                    // Poll 11 - OS
                    [11, 'Linux'], [11, 'macOS'], [11, 'Windows'],
                    // Poll 12 - lecture
                    [12, 'Roman'], [12, 'Science-fiction'], [12, 'Essai'], [12, 'BD / Manga'],
                    // Poll 13 - réseaux sociaux
                    [13, 'Instagram'], [13, 'Twitter/X'], [13, 'TikTok'], [13, 'LinkedIn'],
                    // Poll 14 - matin/soir
                    [14, 'Matin'], [14, 'Soir'],
                    // Poll 15 - animaux
                    [15, 'Chat'], [15, 'Chien'], [15, 'Autre'], [15, 'Aucun'],
                    // Poll 16 - jeux vidéo
                    [16, 'PC'], [16, 'PlayStation'], [16, 'Nintendo Switch'], [16, 'Mobile'],
                    // Poll 17 - repas
                    [17, 'Petit-déjeuner'], [17, 'Déjeuner'], [17, 'Dîner'],
                    // Poll 18 - mode
                    [18, 'Casual'], [18, 'Sportswear'], [18, 'Formel'], [18, 'Streetwear'],
                    // Poll 19 - apprentissage
                    [19, 'Vidéos en ligne'], [19, 'Livres'], [19, 'Pratique directe'], [19, 'Cours en présentiel'],
                    // Poll 20 - loisirs
                    [20, 'Sport'], [20, 'Jeux vidéo'], [20, 'Lecture'], [20, 'Sorties / voyages'],
                ];

                $now = new \DateTime('2026-04-19 10:00:00');
                foreach ($options as [$pollId, $label]) {
                    DB::table('poll_options')->insert([
                        'poll_id'    => $pollId,
                        'label'      => $label,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        );
    }
}
