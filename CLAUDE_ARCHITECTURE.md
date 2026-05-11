# CLAUDE_ARCHITECTURE.md — Pistes de nettoyage

État fonctionnel : les **13 critères du TP** + le **bonus** sont implémentés.
Ce document liste ce qui peut être amélioré pour rendre le code plus lisible,
plus cohérent, et plus défendable à l'oral. Aucune de ces remarques ne bloque la
livraison.

Priorité : **HIGH** (à corriger), **MED** (recommandé), **LOW** (cosmétique).

---

## 1. Frontend Vue

### HIGH

#### `PollStatusBadge.vue` — badge "En cours" qui ne passe pas à "Terminé"
- Fichier : `resources/js/components/PollStatusBadge.vue:8-14`
- Le `computed` lit `new Date()` une seule fois (Vue ne réévalue que si `props.poll.is_draft` ou `props.poll.ends_at` changent).
- Conséquence : sur le **dashboard**, un sondage expiré pendant que la page est ouverte reste affiché "En cours" jusqu'au reload.
- Sur la page de vote ça marche parce que `AppPollVote.vue` utilise `usePollStatus(..., { withClock: true })`. Le composant `PollStatusBadge` lui n'a pas de clock.
- **Fix** : injecter un `now` réactif (depuis un composable `useNow()`), ou faire de `PollStatusBadge` un consommateur de `usePollStatus`.

#### `usePolling.js` — polling naïf
- Fichier : `resources/js/composables/usePolling.js:9-17`
- `setInterval(fn, 5000)` ne protège pas contre :
  1. **Empilement** : si `fn` (un fetch) prend > 5 s, deux requêtes se chevauchent.
  2. **Onglet caché** : continue à interroger l'API quand l'utilisateur est ailleurs (batterie + charge serveur inutile).
- **Fix** :
  ```js
  let running = false;
  async function tick() {
    if (running) return;
    running = true;
    try { await fn(); } finally { running = false; }
  }
  // + écouter document.visibilitychange pour pause/resume
  ```
- À l'oral, c'est typiquement le genre de question qu'on peut te poser : "que se passe-t-il si le réseau est lent ?"

### MED

#### `PollForm.vue` mélange UI, validation et submission
- Fichier : `resources/js/components/PollForm.vue` (~306 lignes)
- Un `usePollForm.js` existe mais une partie de la logique de validation/normalisation reste inline dans le composant.
- **Fix** : déplacer **toute** la logique non-UI (validation, normalisation NFC, payload builder) dans `usePollForm`. Le composant ne fait que rendre + relayer les events.

#### `AppPollVote.vue` — `refreshResults` ignore l'état serveur
- Fichier : `resources/js/AppPollVote.vue` (~ ligne 79)
- Le polling met à jour `poll.value.options` mais pas `poll.value.ends_at` ni un éventuel `has_ended`.
- Conséquence : l'horloge frontend décide seule de l'expiration. Si le serveur change `ends_at` (ou si l'horloge client dérive), incohérence possible.
- **Fix** : synchroniser tout l'objet poll renvoyé par `/v1/polls/{token}/results`, pas seulement les options.

#### Erreurs d'API non typées
- Fichier : `resources/js/composables/useFetchApi.js:78-79`
- Rejette un objet brut `{ status, statusText, data }`. Chaque composant doit ré-extraire `err?.data?.message`.
- **Fix** : encapsuler dans une classe `ApiError` (ou au minimum un helper `getApiErrorMessage(err)`) pour centraliser la logique d'affichage utilisateur.

#### Validation des props faible
- Ex. `PollTable.vue` : `polls: { type: Array, default: () => [] }` — aucune validation de la forme des éléments.
- **Fix** : ajouter une fonction `validator` ou des `@typedef` JSDoc pour documenter la shape `Poll` une seule fois et la réutiliser.

### LOW

#### Constantes magiques
- `5000` (intervalle de polling) répété entre `AppPollVote.vue` et `usePolling.js`.
- `60` / `604800` (min/max durée) en dur dans `ApiPollController`.
- **Fix** : `resources/js/constants.js` côté front, `config/poll.php` côté Laravel.

#### Tailwind soup
- Mêmes combinaisons (`rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500`) répétées sur plusieurs boutons.
- Pour la taille du projet, c'est **acceptable**. Si une 4ᵉ app Vue arrivait, extraire `.btn-primary` dans la couche `@layer components` Tailwind.

#### Commentaires bilingues
- Mélange français/anglais dans les composables et contrôleurs. Choisir une langue (FR vu le projet) et s'y tenir.

#### Code mort dans `PollStatusBadge.vue:24-32`
- Bloc commenté `label2` qui dit "strictement la même chose". Soit on le supprime, soit on le garde dans un `EXPLANATIONS.md` (lieu prévu pour ça).

---

## 2. Backend Laravel

### MED

#### Pas de FormRequest, pas de Policy
- Fichier : `app/Http/Controllers/Api/v1/ApiPollController.php`, `ApiPollVoteController.php`
- Validation inline (`$request->validate(...)`) et autorisation manuelle (`if ($poll->user_id !== $request->user()->id) abort(403)`).
- Ça fonctionne, mais ce n'est pas idiomatique Laravel.
- **Fix recommandé** : extraire au moins `StorePollRequest` / `UpdatePollRequest` (séparation responsabilité). Une `PollPolicy` rendrait `update`/`destroy`/`start` plus propres.
- À l'oral : sache **dire** que tu connais le pattern et que tu as choisi la simplicité pour le périmètre du TP.

#### Suppression silencieuse d'options
- Fichier : `ApiPollController.php` (~ ligne 221)
- Sur update, les options retirées du payload sont supprimées en cascade — si elles ont des votes (cas peu probable mais possible si on relance un sondage), les votes disparaissent silencieusement.
- **Fix** : avant `delete()`, vérifier `PollVote::whereIn('poll_option_id', $toDelete)->exists()` et renvoyer 409 si oui.

#### Pas de pagination dashboard
- `PollDashboardController` charge tous les sondages d'un user en mémoire et les sérialise dans `data-props`.
- Pas un problème pour 10 sondages, en serait un pour 500. Note-le comme "future optimization" dans `EXPLANATIONS.md`.

### LOW

#### Pas d'API Resource
- Les contrôleurs utilisent `makeHidden('secret_token')` à la main à plusieurs endroits.
- Une `PollResource` centraliserait la sérialisation et éviterait l'oubli (bug réel passé selon `KIMI_ARCHITECTURE_OPINION.md`).

#### Tests
- `tests/Feature/ExampleTest.php` est encore le stub Laravel par défaut.
- Au moins **un** test happy-path (créer un sondage → voter → vérifier les résultats) renforcerait considérablement la défense orale.

---

## 3. Cohérence et organisation

### MED

#### Arborescence frontend plate
- Aujourd'hui : `resources/js/AppPollDashboard.vue`, `AppPollEdit.vue`, `AppPollVote.vue` à la racine de `resources/js/`, et les entrypoints à côté.
- **Fix recommandé** :
  ```
  resources/js/
    apps/
      poll-dashboard/{AppPollDashboard.vue, entry.js}
      poll-edit/{AppPollEdit.vue, entry.js}
      poll-vote/{AppPollVote.vue, entry.js}
    components/   # partagés
    composables/  # partagés
  ```
- Bénéfice : on voit immédiatement qu'il y a 3 apps Vue distinctes et ce qui est partagé. Renforce la défense de l'archi multi-apps.

### LOW

#### `KIMI_ARCHITECTURE_OPINION.md` à la racine
- Fichier untracked. Soit tu intègres ce qui est encore valable dans `EXPLANATIONS.md`, soit tu le supprimes. Laisser un fichier d'opinion d'IA à la racine d'un rendu n'aide pas.

#### Bilingue FR/EN
- API/DB en anglais (`Poll`, `PollVote`, `secret_token`), UI en français (`Sondage`, `Brouillon`). Cohérent par couche, donc OK — mais à mentionner volontairement à l'oral.

---

## 4. Ce qui est déjà bien (à garder, à défendre à l'oral)

- **Multi-apps Vue mountées via `data-props`** : pattern propre, justifié par "Laravel pilote le routing, Vue gère les vues riches".
- **`usePolls`, `usePollForm`, `usePolling`, `usePollStatus`, `useFetchApi`, `useFlash`** : la logique réutilisable est bien isolée dans des composables. C'est exactement ce qu'attend le critère 14 du TP.
- **Unicité du vote choix unique** : enforcée **côté front** (`usePollVote.js`) **et côté API** (`ApiPollVoteController:store`) avec des codes HTTP distincts (422 multi-choix interdit, 409 déjà voté). Très propre.
- **Sécurité du token** : `makeHidden('secret_token')` appliqué dans les réponses API. Bien.
- **Bonus `allow_vote_change`** : implémenté proprement (suppression de l'ancien vote avant insertion du nouveau).
- **NFC normalization** des chaînes avant envoi API (`usePollForm.js`) : détail soigné, parle d'un vrai souci de cohérence Unicode.

---

## Plan d'action suggéré (ordre de priorité)

1. Corriger `PollStatusBadge` (clock réactif) — HIGH, ~15 min
2. Robustifier `usePolling` (anti-empilement + visibility) — HIGH, ~20 min
3. Sync complet du poll dans `refreshResults` — MED, ~10 min
4. Extraire la validation restante de `PollForm.vue` vers `usePollForm` — MED, ~30 min
5. `PollResource` + au moins un `FormRequest` (`StorePollRequest`) — MED, ~30 min
6. Réorganiser `resources/js/` en `apps/<name>/` — MED, ~15 min (mécanique, met à jour `vite.config.js`)
7. Un test feature happy-path — MED, ~30 min
8. Constantes (`5000`, `60`, `604800`) extraites — LOW, ~10 min
9. Supprimer `KIMI_ARCHITECTURE_OPINION.md` ou l'intégrer à `EXPLANATIONS.md` — LOW

Total estimé : **~2 h 30** pour passer de "complet" à "très propre".
