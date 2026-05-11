# KIMI_ARCHITECTURE_OPINION.md — Audit architectural du TP Laravel + Vue.js

## 1. Vue d'ensemble & conformité au TP

L'architecture globale respecte scrupuleusement les contraintes du `TP.md` et du `CLAUDE.md` :

| Contrainte | Statut | Commentaire |
|---|---|---|
| **Multi-apps Vue** dans des pages Blade (`id="app"` + `data-props`) | ✅ | `poll-dashboard`, `poll-edit`, `poll-vote` — trois entrypoints Vite distincts. |
| **Pas de Vue Router** | ✅ | Le routing est piloté par Laravel (`routes/web.php`). Navigation via `window.location.href`. |
| **Pas de Pinia / Vuex** | ✅ | État local via `ref` / `computed` ; état global léger via singleton module-level (`useFlash`). |
| **Pas de Quasar** | ✅ | UI 100 % Tailwind CSS v4 + patterns Tailwind UI. |
| **Composition API** (`<script setup>`) | ✅ | Utilisée partout. |
| **API versionnée** `/api/v1/` | ✅ | Contrôleurs dans `app/Http/Controllers/Api/v1/`. |
| **Auth Sanctum** (cookie de session) | ✅ | Conservée en l'état. CSRF injecté via `bootstrap.js`. |
| **Mobile-first** | ✅ | Classes Tailwind `sm:` utilisées systématiquement. |
| **Unicité du vote côté API** | ✅ | `ApiPollVoteController@store` gère l'unicité en choix simple et le changement de vote. |
| **Polling résultats en direct** | ✅ | `usePolling(refreshResults, 5000)` sur la page de vote. |
| **Graphique résultats** | ✅ | `vue-chartjs` + Chart.js (bar chart vertical). |
| **Accès anonyme conditionnel** | ✅ | `showResults` computed vérifie `results_public` ; endpoint `/results` public. |

**Verdict global :** L'architecture est cohérente, défendable à l'oral et couvre l'intégralité des critères fonctionnels du TP.

---

## 2. Ce qui est très bien

### 2.1. Découpage backend / frontend

- **Pattern "single action controller"** (`__invoke`) pour les pages Blade (`PollDashboardController`, `PollCreateController`, `PollEditController`, `PollShowController`). C'est élégant et conforme aux conventions Laravel modernes.
- **Eager loading** côté Laravel : les données initiales sont injectées dans `data-props` (ex: `$poll->load('options')` dans `PollShowController`). Cela évite un round-trip API au montage de l'app Vue — pas de "flash" de loading.
- **Transactions DB** (`DB::transaction`) sur `store`, `update` et `vote`. L'atomicité est garantie ; pas de poll orphelin ni de vote corrompu.

### 2.2. Architecture Vue.js

- **Composables riches et spécialisés** : `usePollForm` (validation + NFC + soumission), `usePollVote` (sélection + soumission + mapping erreurs HTTP), `usePollStatus` (computed d'état avec horloge optionnelle), `usePolling` (abstraction lifecycle-safe de `setInterval`). Chaque composable a une responsabilité unique.
- **`defineModel()`** (Vue 3.4+) utilisé dans `PollOptionInput.vue` pour le binding bidirectionnel des labels d'options. C'est un concept moderne et pertinent pour ce cas d'usage.
- **`<Teleport>`** utilisé de manière canonique pour les modales (`ConfirmModal.vue`) et les toasts (`FlashToast.vue`, `ShareLink.vue`). Cela résout proprement les problèmes de `z-index` et `overflow:hidden`.
- **`<Transition>`** pour l'animation du toast — pas de JS d'animation, uniquement des classes CSS.
- **Séparation présentation / orchestration** : `PollTable.vue` est purement présentationnel (émet `edit`/`delete`), tandis que `AppPollDashboard.vue` orchestre l'état et les actions.

### 2.3. UX & robustesse

- **Suppression optimiste** dans `usePolls.remove` : la ligne disparaît immédiatement, rollback en cas d'erreur. UX fluide.
- **Validation live** dans `PollForm.vue` (`watch(form, ..., { deep: true })`) : les erreurs disparaissent au fur et à mesure que l'utilisateur corrige.
- **Normalisation Unicode NFC** dans `usePollForm.js` : défense contre les doublons visuels causés par des encodages différents (macOS vs Windows).
- **Génération du `secret_token` dans `Poll::boot()`** : centralisée, avec boucle de garde anti-collision. Toute création de poll (contrôleur, seed, test) en bénéficie automatiquement.
- **Polling avec garde réactive** : `refreshResults` vérifie `isExpired.value` avant chaque appel. Quand le sondage expire, le polling devient un no-op naturel — pas besoin de `clearInterval` explicite.

### 2.4. Documentation

- **`EXPLANATIONS.md`**, **`CODE.md`**, **`PLAN.md`**, **`ORAL_QA.md`** constituent une documentation exceptionnelle pour un TP. Le niveau de détail (justification concept par concept, questions/réponses probables, pièges) est un atout majeur pour la défense orale.

---

## 3. Ce qui pourrait être mieux / optimisé

### 3.1. 🔴 Critique — Config Vite invalide

**Fichier concerné :** `vite.config.js:13`

```js
laravel({
    input: [
        // ...
        'resources/js/poll-dashboard-integrated.js', // ← CE FICHIER N'EXISTE PAS
    ],
}),
```

**Problème :** `resources/js/poll-dashboard-integrated.js` n'existe pas dans le codebase. `npm run build` va planter avec une erreur Rollup/Vite.

**Impact :** Le projet ne build plus en production. C'est un bug bloquant.

**Recommandation :** Supprimer cette ligne de `vite.config.js` et nettoyer le dossier `public/build/` des anciens chunks orphelins (`poll-dashboard-integrated-*.js/css`).

---

### 3.2. 🔴 Critique — Bogue `defineModel` dans `ConfirmModal.vue`

**Fichiers concernés :** `ConfirmModal.vue:4` + `AppPollDashboard.vue:73`

```vue
<!-- ConfirmModal.vue -->
const open = defineModel("open", { type: Boolean, required: true });

<!-- AppPollDashboard.vue -->
<ConfirmModal v-model="confirmOpen" ... />
```

**Problème :** `defineModel("open")` expose une prop nommée `open` et un emit `update:open`. Le parent doit donc utiliser **`v-model:open="confirmOpen"`**. En utilisant `v-model` (sans nom), Vue cherche `modelValue` / `update:modelValue`, qui n'existent pas. La modale ne s'ouvre/souvre pas correctement.

**Impact :** La suppression de sondage via modale est fonctionnellement cassée.

**Recommandation :** Soit renommer en `const open = defineModel({ type: Boolean, required: true })` (nom par défaut `modelValue`), soit corriger le parent en `v-model:open="confirmOpen"`.

---

### 3.3. 🔴 Critique — Fuite du `secret_token` dans les réponses API

**Fichiers concernés :** `ApiPollController.php:117` (méthode `show`) et `ApiPollVoteController.php:114` (méthode `store`).

**Problème :** Les méthodes retournent l'instance Eloquent complète (`return $poll;` ou `return response()->json($poll)`). Le champ `secret_token` fait partie du modèle (`$fillable`) et est donc sérialisé dans le JSON. Un visiteur public qui appelle `GET /api/v1/polls/{token}` reçoit le **même token** dans la réponse. De même, après un vote, le front reçoit le token en réponse.

**Impact :** Le token est supposé être un "secret" partagé par le créateur. Son exposition dans l'API publique annule partiellement son intérêt sécuritaire.

**Recommandation :** Masquer le champ dans les ressources publiques :
```php
// Dans ApiPollController@show
$poll->makeHidden('secret_token');
return $poll;

// Ou, mieux, utiliser une API Resource Laravel :
return new PollResource($poll);
```

---

### 3.4. 🟡 Important — Polling naïf et risque d'empilement

**Fichier concerné :** `composables/usePolling.js`

```js
export function usePolling(fn, interval = 5000) {
  let timer;
  onMounted(() => { timer = setInterval(fn, interval); });
  onUnmounted(() => clearInterval(timer));
}
```

**Problème :** `setInterval` appelle `fn` toutes les 5 secondes **indépendamment** de la durée d'exécution de `fn`. Si `refreshResults` met plus de 5s (réseau lent), les requêtes s'empilent. De plus, il n'y a aucune gestion de l'onglet inactif (`document.visibilitychange`) : le polling continue de consommer des ressources alors que l'utilisateur n'est pas sur la page.

**Impact :** Surcharge réseau côté client et serveur ; expérience dégradée sur connexion lente.

**Recommandation :**
```js
export function usePolling(fn, interval = 5000) {
  let timer;
  let running = false;

  async function tick() {
    if (running) return;
    running = true;
    await fn();
    running = false;
  }

  onMounted(() => {
    tick(); // appel immédiat
    timer = setInterval(tick, interval);
    document.addEventListener('visibilitychange', handleVisibility);
  });

  onUnmounted(() => {
    clearInterval(timer);
    document.removeEventListener('visibilitychange', handleVisibility);
  });
}
```

---

### 3.5. 🟡 Important — `PollStatusBadge` non réactif au temps

**Fichier concerné :** `components/PollStatusBadge.vue:8-14`

```js
const status = computed(() => {
  if (props.poll.is_draft) return "draft";
  if (props.poll.ends_at && new Date(props.poll.ends_at) < new Date()) {
    return "ended";
  }
  return "running";
});
```

**Problème :** `new Date()` du côté droit n'est pas une source réactive. Le `computed` est mis en cache par Vue. Tant que `props.poll.ends_at` ne change pas, le statut reste figé. Un sondage "En cours" ne passera jamais automatiquement à "Terminé" dans le dashboard sans rechargement de page.

**Impact :** UX incohérente : le dashboard affiche "En cours" alors que le sondage est déjà terminé.

**Recommandation :** Passer `now` comme prop réactive depuis le parent, ou utiliser `usePollStatus(pollRef, { withClock: true })` dans `PollTable.vue` / `AppPollDashboard.vue` pour que le badge soit mis à jour chaque seconde.

---

### 3.6. 🟡 Important — `refreshResults` ignore les métadonnées du serveur

**Fichier concerné :** `AppPollVote.vue:79-98`

```js
async function refreshResults() {
  // ...
  const data = await fetchApi({ url: `/polls/${poll.value.secret_token}/results` });
  poll.value.options = data.options; // ← on ignore data.has_ended et data.ends_at
}
```

**Problème :** L'endpoint `/results` retourne `has_ended`, `ends_at`, `total_votes`. Or `refreshResults` ne met à jour que `options`. Si le serveur considère le sondage terminé (horloge serveur légèrement en avance), le front continue d'afficher le formulaire de vote jusqu'à ce que son propre `isExpired` (basé sur son horloge locale) ne bascule.

**Impact :** Un utilisateur pourrait soumettre un vote une fraction de seconde après la fin réelle du sondage, recevant une erreur 403.

**Recommandation :** Synchroniser l'état serveur :
```js
poll.value.options = data.options;
poll.value.ends_at = data.ends_at; // ou poll.value.has_ended = data.has_ended
```

---

### 3.7. 🟡 Important — Risque de crash dans `poll-vote.js`

**Fichier concerné :** `resources/js/poll-vote.js:6`

```js
const props = JSON.parse(el.dataset.props);
```

**Problème :** Contrairement à `poll-dashboard.js` et `poll-edit.js` qui utilisent `el.dataset.props ?? "{}"`, cet entrypoint ne gère pas l'absence de l'attribut. Si Blade omet `data-props`, `JSON.parse(undefined)` lève une exception et l'app Vue ne monte pas.

**Recommandation :** Uniformiser avec `JSON.parse(el.dataset.props ?? "{}")`.

---

### 3.8. 🟠 Moyen — Suppression d'options sans vérification des votes

**Fichier concerné :** `ApiPollController.php:221-224`

```php
if (!empty($toDelete)) {
    $poll->options()->whereIn("id", $toDelete)->delete();
}
```

**Problème :** La migration `poll_options` a une contrainte `onDelete('cascade')` sur `poll_votes.poll_option_id`. Donc supprimer une option supprime **silencieusement** les votes associés. En théorie, un brouillon ne devrait pas avoir de votes (l'API bloque le vote si `is_draft`), mais en cas de contournement ou de données de test, cela corrompt l'intégrité des résultats.

**Recommandation :** Vérifier explicitement que les options à supprimer n'ont pas de votes, ou utiliser `onDelete('restrict')` en migration pour que la base refuse la suppression.

---

### 3.9. 🟠 Moyen — Absence de pagination sur le dashboard

**Fichier concerné :** `PollDashboardController.php:11`

```php
$polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();
```

**Problème :** Tous les sondages sont chargés en mémoire. Pour un utilisateur avec 500 sondages, le temps de réponse Blade + taille du JSON dans `data-props` deviennent problématiques.

**Impact :** Performance dégradée à grande échelle.

**Recommandation :** Pour un TP, c'est acceptable, mais à mentionner à l'oral comme "amélioration future" : ajouter `->paginate(20)` et un composant `PollPagination.vue`.

---

### 3.10. 🟠 Moyen — Code mort / duplication conceptuelle

**Fichiers concernés :** `resources/js/utils/fetchJson.js`, `resources/js/composables/useFetchJson.js`, `resources/js/composables/useHashRoute.js`, `resources/js/composables/useJsonStorage.js`.

**Problème :** Ces fichiers sont fournis dans le projet de base mais ne semblent **pas utilisés** par les apps Vue actuelles. Le projet repose exclusivement sur `useFetchApi.js`. La présence de code mort augmente la charge cognitive et le risque de confusion à l'oral ("pourquoi avez-vous deux wrappers fetch ?").

**Recommandation :** Supprimer ou déplacer dans un dossier `/_legacy/` si vous voulez les conserver à des fins historiques.

---

### 3.11. 🟢 Mineur — Pas de Form Request ni de Policy Laravel

**Fichiers concernés :** `ApiPollController.php`, `ApiPollVoteController.php`

**Problème :** La validation est inline (`$request->validate(...)`) et l'autorisation est vérifiée manuellement (`if ($poll->user_id !== $request->user()->id)`). Ce n'est pas incorrect, mais ce n'est pas "l'idiome Laravel" le plus pur.

**Recommandation :** Pour un TP noté sur l'architecture, citer en oral que vous connaissez les `FormRequest` et les `Policy` (ex: `PollPolicy::update($user, $poll)`), mais que vous avez choisi l'inline pour rester dans le périmètre front-first du TP.

---

### 3.12. 🟢 Mineur — Incohérence d'eager loading (EditController vs vue)

**Fichier concerné :** `PollEditController.php:19-22`

```php
return view('polls.edit', [
    'poll' => $poll,
    'dashboardUrl' => route('polls.dashboard'),
]);
```

**Problème :** Le contrôleur passe `$poll` sans charger les options. C'est la vue Blade qui fait `$poll->load('options')`. Cela fonctionne, mais c'est incohérent avec `PollShowController` qui fait le `withCount` dans le contrôleur. C'est un détail de style.

**Recommandation :** Déplacer le `load('options')` dans le contrôleur pour que la vue Blade soit "bête" (seulement du rendu).

---

## 4. Tableau de synthèse des risques pour l'évaluation

| # | Risque | Sévérité | Probabilité d'être pointé à l'oral |
|---|---|---|---|
| 1 | `vite.config.js` référence un fichier inexistant (build cassé) | 🔴 Critique | Très élevée (démonstration en direct) |
| 2 | `ConfirmModal.vue` buggé (`defineModel("open")` vs `v-model`) | 🔴 Critique | Élevée (suppression ne marche pas) |
| 3 | `secret_token` exposé dans les réponses API publiques | 🔴 Critique | Élevée (sécurité) |
| 4 | Polling qui s'empile si réseau lent | 🟡 Important | Moyenne (question sur `usePolling`) |
| 5 | `PollStatusBadge` figé dans le dashboard | 🟡 Important | Moyenne ("pourquoi le statut ne change pas ?") |
| 6 | `refreshResults` ignore `has_ended` du serveur | 🟡 Important | Moyenne (désynchro front/back) |
| 7 | `poll-vote.js` sans fallback `?? "{}"` | 🟡 Important | Faible (coin edge case) |
| 8 | Suppression d'options cascade silencieuse | 🟠 Moyen | Faible (si l'examinateur creuse les migrations) |
| 9 | Pas de pagination dashboard | 🟠 Moyen | Faible |
| 10 | Code mort (`fetchJson.js`, `useFetchJson.js`, etc.) | 🟠 Moyen | Moyenne ("à quoi servent ces fichiers ?") |

---

## 5. Recommandations prioritaires (ordre d'exécution)

1. **Corriger `vite.config.js`** — Retirer `poll-dashboard-integrated.js`. Nettoyer `public/build/`.
2. **Corriger `ConfirmModal.vue`** — Passer à `defineModel({ type: Boolean, required: true })` sans nom personnalisé.
3. **Masquer `secret_token`** dans `ApiPollController@show` et `ApiPollVoteController@store` via `makeHidden` ou une API Resource.
4. **Rendre `usePolling` robuste** — Ajouter un flag `running` et écouter `visibilitychange`.
5. **Synchroniser `ends_at` dans `refreshResults`** — Mettre à jour `poll.value.ends_at` depuis la réponse API.
6. **Uniformiser `poll-vote.js`** — Ajouter `?? "{}"`.
7. **Nettoyer le code mort** — Supprimer `fetchJson.js`, `useFetchJson.js`, `useHashRoute.js`, `useJsonStorage.js` si inutilisés.
8. **Ajouter une garde anti-suppression de votes** dans `ApiPollController@update` (ou passer à `onDelete('restrict')`).

---

## 6. Points de défense orale forts à mettre en avant

- **Pourquoi multi-apps Vue ?** Bundles séparés, intégration native avec l'auth Laravel (redirection côté serveur), pas de complexité SPA inutile.
- **Pourquoi pas de store global ?** Le singleton `useFlash` suffit pour un état global minimal ; tout le reste est local au composant ou au composable.
- **Pourquoi `ref` plutôt que `reactive` ?** `ref` est le défaut recommandé par Vue (unification primitives/objets, pas de perte de réactivité par déstructuration).
- **Pourquoi polling et pas WebSockets ?** Scope du TP : pas de stack temps-réel (Reverb/Pusher) à maintenir. Le polling à 5s est un trade-off simple et suffisant.
- **Pourquoi la normalisation NFC ?** Défense contre les doublons d'options visuellement identiques mais encodées différemment selon les OS.

---

*Document rédigé le 5 mai 2026. Basé sur l'état du dépôt à la date du commit.*
