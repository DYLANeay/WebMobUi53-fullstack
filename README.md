# Application de sondage, Dylan Eray - M53-2

TP fullstack HEIG-VD WebMobUI - système de sondage multi-plateforme.

---

## Installation

J'utilise `chart.js` et `vue-chartjs` pour les graphiques (voir [Choix techniques](#choix-techniques)).

```bash
npm install
npm run build
```

---

## Ce qui a été ajouté au template de départ

### Modèles (modifiés)

| Fichier | Modification |
|---------|-------------|
| `app/Models/Poll.php` | J'ai ajouté le hook `boot()` pour générer automatiquement un `secret_token` unique (32 chars) à la création via `Str::random()` avec boucle de garde anti-collision. J'ai aussi ajouté les casts pour les booléens et les dates. |

### Contrôleurs web (ajoutés)

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/PollDashboardController.php` | Charge la liste des sondages de l'utilisateur connecté et monte la vue Blade `polls.dashboard`. |
| `app/Http/Controllers/PollCreateController.php` | Monte la vue Blade `polls.create` (mode création). |
| `app/Http/Controllers/PollEditController.php` | Charge le sondage par ID (avec vérification de propriété), monte `polls.edit`. |
| `app/Http/Controllers/PollShowController.php` | Page publique de vote/résultats : charge le sondage par token, les résultats et les votes déjà soumis par l'utilisateur connecté (si applicable). |

### Contrôleurs API (ajoutés)

Tous dans `app/Http/Controllers/Api/v1/`.

| Fichier | Endpoints gérés |
|---------|----------------|
| `ApiPollController.php` | `GET /v1/polls`, `POST /v1/polls`, `GET /v1/polls/id/{poll}`, `PUT /v1/polls/{poll}`, `POST /v1/polls/{poll}/start`, `DELETE /v1/polls/{poll}`, `GET /v1/polls/{token}`, `GET /v1/polls/{token}/results` |
| `ApiPollVoteController.php` | `POST /v1/polls/{token}/vote`, `GET /v1/polls/{token}/vote` |

### Routes (modifiées)

- `routes/web.php` : ajout des 4 routes de pages sondage (dashboard, create, edit, show publique).
- `routes/api.php` : ajout des 10 endpoints `/api/v1/polls/...`.

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

### Composants (`resources/js/components/`)

| Fichier | Rôle |
|---------|------|
| `AppPollDashboard.vue` | Container racine du dashboard : charge la liste, gère la suppression avec confirmation. |
| `AppPollEdit.vue` | Container de création/édition : affiche le formulaire (brouillon), le panneau de lancement, ou le lien de partage selon l'état. |
| `AppPollVote.vue` | Container de la page publique : formulaire de vote (radio/checkbox), résultats en direct, graphique, gestion de l'expiration. |
| `PollForm.vue` | Formulaire réutilisable question + options + paramètres. Validation côté client en temps réel. |
| `PollOptionInput.vue` | Champ de saisie d'une option (avec bouton suppression). Utilise `defineModel`. |
| `PollTable.vue` | Tableau responsive des sondages du dashboard avec badges de statut et actions. |
| `PollStatusBadge.vue` | Badge coloré indiquant l'état du sondage (brouillon / en cours / terminé). |
| `PollResultsChart.vue` | Graphique en barres (Chart.js) des résultats, mis à jour réactivement. |
| `ShareLink.vue` | Affiche le lien de partage avec bouton copie + toast de confirmation. |
| `FlashToast.vue` | Notification temporaire (3 s) téléportée dans le `<body>`, avec animation. |
| `ConfirmModal.vue` | Modale de confirmation réutilisable, téléportée dans le `<body>`. |

### Composables (`resources/js/composables/`)

| Fichier | Rôle |
|---------|------|
| `useFetchApi.js` | Client HTTP : `fetch` avec `AbortController`, timeout 5 s, injection automatique du token XSRF, parsing JSON, gestion des erreurs. |
| `useFlash.js` | Singleton de notification : expose `flash(message, type)`, auto-dismiss configurable. |
| `usePolls.js` | État du dashboard : liste des sondages, suppression optimiste avec rollback en cas d'erreur. |
| `usePollForm.js` | Validation et soumission du formulaire de création/édition, normalisation NFC des chaînes. |
| `usePollStatus.js` | Propriétés calculées sur l'état d'un sondage (`isDraft`, `isRunning`, `isExpired`). Option `withClock` pour une réactivité à la seconde via `watchEffect`. |
| `usePollVote.js` | Gestion de la sélection des options et soumission du vote, avec gestion des codes d'erreur API (401, 403, 409, 422). |
| `usePolling.js` | Lance un `setInterval` au montage du composant et le nettoie au démontage. Utilisé pour rafraîchir les résultats toutes les 5 secondes. |

---

## Endpoints API JSON

Base : `/api/v1/`

| Méthode | URL | Auth | Description |
|---------|-----|------|-------------|
| `GET` | `/polls` | oui | Liste des sondages de l'utilisateur connecté |
| `POST` | `/polls` | oui | Crée un sondage (brouillon) |
| `GET` | `/polls/id/{id}` | oui | Détail d'un sondage par ID (propriétaire uniquement) |
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

### Sanctum SPA (cookie de session)

Les apps Vue sont embarquées sur le même domaine que le backend. L'authentification repose sur le **cookie de session** Laravel, pas sur un Bearer token. Le token XSRF est lu depuis le cookie `XSRF-TOKEN` au démarrage et injecté dans chaque requête mutante via `bootstrap.js`.

### Pas de Pinia ni Vuex

L'état partagé entre composants est minimal et géré via des composables à portée de module. Introduire un store global aurait ajouté de la complexité sans bénéfice réel pour la taille de cette application.

---

## Pages de l'application

| URL | Accès | Description |
|-----|-------|-------------|
| `/polls/dashboard` | Connecté | Liste de mes sondages |
| `/polls/create` | Connecté | Formulaire de création |
| `/polls/{id}/edit` | Connecté (propriétaire) | Formulaire d'édition, lancement, lien de partage |
| `/polls/{token}` | Public (mais pas de vote possible si pas authentifié) | Page de vote et résultats |
