# Application de sondage, Dylan Eray - M53-2

TP fullstack HEIG-VD WebMobUI - système de sondage multi-plateforme.

---

## Démarrage rapide depuis un clone

Pour lancer l'application depuis zéro après avoir cloné le dépôt :

```bash
# 1. Dépendances PHP (Laravel 12)
composer install

# 2. Dépendances JS (Vue 3.5, Tailwind v4, Chart.js, vue-chartjs)
npm install

# 3. Fichier d'environnement + clé d'application
cp .env.example .env
php artisan key:generate

# 4. Base de données SQLite (par défaut dans .env.example)
touch database/database.sqlite
php artisan migrate --seed   # crée les tables + insère 2 utilisateurs et 30 sondages de démo

# 5. Lien symbolique pour le storage (photos de profil)
php artisan storage:link

# 6. Lancer le serveur de dev 
composer run dev                  # serveur Vite (HMR) pour les apps Vue
```

Comptes de démonstration créés par le seeder (mot de passe : `password`) :

| Email | Rôle |
|-------|------|
| `john.doe@example.com` | Propriétaire de la moitié des sondages de démo |
| `jane.doe@example.com` | Propriétaire de l'autre moitié |

Pour un bundle de production à la place du serveur Vite : `npm run build`.

---

## Ce qui a été ajouté au template de départ

### Modèles (modifiés)

| Fichier | Modification |
|---------|-------------|
| `app/Models/Poll.php` | J'ai ajouté le hook `boot()` pour générer automatiquement un `secret_token` unique (32 chars) à la création via `Str::random()` avec boucle de garde anti-collision. J'ai aussi ajouté les casts pour les booléens et les dates. |

### Contrôleur web (ajouté)

Un seul contrôleur resourceful (`app/Http/Controllers/PollController.php`) regroupe les 4 pages liées aux sondages. Chaque méthode monte une vue Blade dédiée qui sert d'hôte à une app Vue.

| Méthode | Route | Rôle |
|---------|-------|------|
| `index()` | `GET /polls/dashboard` | Charge la liste des sondages de l'utilisateur connecté, monte `polls.dashboard`. |
| `create()` | `GET /polls/create` | Monte `polls.create` (mode création, sans données initiales). |
| `edit(Poll $poll)` | `GET /polls/{poll}/edit` | Route-model binding + vérification de propriété, monte `polls.edit`. |
| `show(string $token)` | `GET /polls/{token}` | Page publique : charge le sondage par token, les résultats et les votes déjà soumis (si utilisateur connecté). |

### Contrôleurs API (ajoutés)

Tous dans `app/Http/Controllers/Api/v1/`.

| Fichier | Endpoints gérés |
|---------|----------------|
| `ApiPollController.php` | `GET /v1/polls`, `POST /v1/polls`, `PUT /v1/polls/{poll}`, `POST /v1/polls/{poll}/start`, `DELETE /v1/polls/{poll}`, `GET /v1/polls/{token}`, `GET /v1/polls/{token}/results` |
| `ApiPollVoteController.php` | `POST /v1/polls/{token}/vote`, `GET /v1/polls/{token}/vote` |

### Routes (modifiées)

- `routes/web.php` : ajout des 4 routes de pages sondage (dashboard, create, edit, show publique).
- `routes/api.php` : ajout des 9 endpoints `/api/v1/polls/...`.

### Frontend Vue.js (entièrement ajouté)

Tout le frontend est dans `resources/js/`.

---

## Structure du frontend

### Entrypoints Vite

Chaque page Blade monte sa propre application Vue isolée. Il n'y a pas de routeur SPA, c'est Laravel qui gère le routing.

| Fichier | App montée | Page |
|---------|-----------|------|
| `poll-dashboard.js` | `AppPollDashboard.vue` | `/polls/dashboard` |
| `poll-edit.js` | `AppPollEdit.vue` | `/polls/create` et `/polls/{id}/edit` |
| `poll-vote.js` | `AppPollVote.vue` | `/polls/{token}` |

### Containers d'app (`resources/js/`)

| Fichier | Rôle |
|---------|------|
| `AppPollDashboard.vue` | Container racine du dashboard : charge la liste, gère la suppression avec confirmation. |
| `AppPollEdit.vue` | Container de création/édition : affiche le formulaire (brouillon), le panneau de lancement, ou le lien de partage selon l'état. |
| `AppPollVote.vue` | Container de la page publique : formulaire de vote (radio/checkbox), résultats en direct, graphique, gestion de l'expiration. |

### Composants (`resources/js/components/`)

Les composants sont organisés en sous-dossiers par responsabilité : `poll/` regroupe le domaine métier sondage, `ui/` les briques réutilisables génériques.

#### `components/poll/` : domaine sondage

| Fichier | Rôle |
|---------|------|
| `PollForm.vue` | Formulaire réutilisable question + options + paramètres. Validation côté client en temps réel. |
| `PollOptionInput.vue` | Champ de saisie d'une option (avec bouton suppression). Utilise `defineModel`. |
| `PollTable.vue` | Tableau responsive des sondages du dashboard avec badges de statut et actions. |
| `PollStatusBadge.vue` | Badge coloré indiquant l'état du sondage (brouillon / en cours / terminé). |
| `PollResultsChart.vue` | Graphique en barres (Chart.js) des résultats, mis à jour réactivement. |
| `ShareLink.vue` | Affiche le lien de partage avec bouton copie + toast de confirmation. |

#### `components/ui/` : UI générique

| Fichier | Rôle |
|---------|------|
| `FlashToast.vue` | Notification temporaire (3 s) téléportée dans le `<body>`, avec animation. |
| `ConfirmModal.vue` | Modale de confirmation réutilisable, téléportée dans le `<body>`. |

### Composables (`resources/js/composables/`)

Même logique de découpage que les composants : `api/` pour le client HTTP, `poll/` pour la logique métier, `ui/` pour les utilitaires génériques.

#### `composables/api/`

| Fichier | Rôle |
|---------|------|
| `useFetchApi.js` | Client HTTP : `fetch` avec `AbortController`, timeout 5 s, injection automatique du token XSRF, parsing JSON, gestion des erreurs. |

#### `composables/poll/`

| Fichier | Rôle |
|---------|------|
| `usePolls.js` | État du dashboard : liste des sondages, suppression optimiste avec rollback en cas d'erreur. |
| `usePollForm.js` | Validation et soumission du formulaire de création/édition, normalisation NFC des chaînes. |
| `usePollStatus.js` | Propriétés calculées sur l'état d'un sondage (`isDraft`, `isRunning`, `isExpired`). Option `withClock` pour une réactivité à la seconde via `watchEffect`. |
| `usePollVote.js` | Gestion de la sélection des options et soumission du vote, avec gestion des codes d'erreur API (401, 403, 409, 422). |

#### `composables/ui/`

| Fichier | Rôle |
|---------|------|
| `usePolling.js` | Lance un `setInterval` au montage du composant et le nettoie au démontage. Utilisé pour rafraîchir les résultats toutes les 5 secondes. |

### Stores (`resources/js/stores/`)

| Fichier | Rôle |
|---------|------|
| `flashStore.js` | Store global minimaliste de notifications (refs `message`, `type`, `visible` + fonctions `flash()` / `dismiss()`). Pas de Pinia, simple module exportant des `ref` partagés, suffisant pour cette portée. |

---

## Endpoints API JSON

Base : `/api/v1/`

| Méthode | URL | Auth | Description |
|---------|-----|------|-------------|
| `GET` | `/polls` | oui | Liste des sondages de l'utilisateur connecté |
| `POST` | `/polls` | oui | Crée un sondage (brouillon) |
| `PUT` | `/polls/{id}` | oui | Modifie un sondage (brouillon uniquement) |
| `POST` | `/polls/{id}/start` | oui | Lance un sondage (brouillon vers actif) |
| `DELETE` | `/polls/{id}` | oui | Supprime un sondage |
| `GET` | `/polls/{token}` | non | Détail public d'un sondage (via token) |
| `GET` | `/polls/{token}/results` | non | Résultats publics (nombre de votes par option) |
| `POST` | `/polls/{token}/vote` | oui | Soumet un vote |
| `GET` | `/polls/{token}/vote` | oui | Options déjà votées par l'utilisateur |

J'ai garanti l'unicité du vote en mode choix unique côté API : un second vote retourne un `409 Conflict` si `allow_vote_change` est désactivé, ou remplace le vote précédent si activé.

---

## Choix techniques

### Pourquoi Chart.js + vue-chartjs ?

J'ai choisi **Chart.js** car c'est la bibliothèque de graphiques la plus répandue de l'écosystème JavaScript : documentation exhaustive, maintenance active, légère (~60 KB gzippé), et elle couvre tous les types de graphiques courants sans dépendances externes.

J'ai utilisé **vue-chartjs** car c'est le wrapper officiel Vue 3 pour Chart.js. Il expose les graphiques comme des composants Vue ordinaires, ce qui me permet de piloter les données directement depuis des `ref`/`computed` sans manipuler l'instance Chart.js manuellement. La réactivité est gérée automatiquement : passer un nouvel objet `chartData` suffit pour mettre à jour le graphique.

Ma première idée était d'utiliser Apache ECharts, qui est très puissant pour les visualisations complexes. Cependant, il est plus lourd (~200 KB gzippé) et son intégration avec Vue 3 est moins fluide que vue-chartjs. Pour une application de sondage avec des graphiques relativement simples (barres), Chart.js semble offrir un meilleur compromis entre fonctionnalités et performance.

### Architecture multi-apps Vue (sans Vue Router)

J'ai délégué le routing à Laravel. Chaque page Blade monte son propre entrypoint Vite indépendant. Cette approche évite d'embarquer un routeur SPA complet (Vue Router) pour une application où les transitions de page sont gérées par le serveur. Elle simplifie aussi l'intégration avec le système d'authentification existant (cookie de session Sanctum).

---

## Fichiers supprimés (refactor, lisibilité)

Ces fichiers étaient présents dans le template de départ mais **n'ont jamais été utilisés** par les apps Vue du projet. Les garder créait de la confusion et augmentait la charge cognitive sans valeur ajoutée.

| Fichier | Pourquoi il a été supprimé |
|---------|---------------------------|
| `resources/js/utils/fetchJson.js` | Wrapper `fetch` bas niveau jamais appelé. Le projet utilise exclusivement `useFetchApi.js` pour les requêtes HTTP. |
| `resources/js/composables/useFetchJson.js` | Dépend de `fetchJson.js` et n'était importé nulle part. |
| `resources/js/composables/useHashRoute.js` | Router hash interne inutilisé, le routing est entièrement délégué à Laravel (`routes/web.php`). |
| `resources/js/composables/useJsonStorage.js` | Wrapper `localStorage` réactif jamais utilisé ; aucun état n'a besoin de persistance locale. |
| `resources/js/utils/jsonStorage.js` | Utilitaire sous-jacent à `useJsonStorage.js`, également inutilisé. |

---

## Pages de l'application

| URL | Accès | Description |
|-----|-------|-------------|
| `/polls/dashboard` | Connecté | Liste de mes sondages |
| `/polls/create` | Connecté | Formulaire de création |
| `/polls/{id}/edit` | Connecté (propriétaire) | Formulaire d'édition, lancement, lien de partage |
| `/polls/{token}` | Public (mais pas de vote possible si pas authentifié) | Page de vote et résultats |
