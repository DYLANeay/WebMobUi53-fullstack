<script setup>
import { computed, ref } from "vue";
import { usePollVote } from "./composables/usePollVote";
import { usePollStatus } from "./composables/usePollStatus";
import { usePolling } from "./composables/usePolling";
import { useFetchApi } from "./composables/useFetchApi";
import PollResultsChart from "./components/PollResultsChart.vue";

const props = defineProps({
    poll: { type: Object, required: true },
    hasVoted: { type: Boolean, default: false },
    votedOptionIds: { type: Array, default: () => [] },
});

// Ref locale : on mute cette valeur après le vote pour rafraîchir les résultats
// sans recharger la page.
const poll = ref(props.poll);

// Centralise les computed de statut (avec horloge réactive pour l'expiration)
const { isDraft, isRunning, isExpired } = usePollStatus(poll, {
    withClock: true,
});

const canVote = computed(() => {
    return !isDraft.value && isRunning.value && !isExpired.value;
});

const {
    selectedOptions,
    submitting,
    error,
    success,
    toggleOption,
    setSingleOption,
    isSelected,
    submit,
} = usePollVote();

// Pré-sélectionne les options déjà votées par l'utilisateur (utile quand
// allow_vote_change est activé, sinon juste pour visualiser ce qui a été voté).
if (props.hasVoted && props.votedOptionIds.length > 0) {
    selectedOptions.value = [...props.votedOptionIds];
}

// Affiche les résultats si l'utilisateur a déjà voté, vient juste de voter,
// ou si le sondage rend les résultats publics.
const showResults = computed(() => {
    return props.hasVoted || success.value || poll.value.results_public;
});

// Masque le formulaire si l'utilisateur a déjà voté et ne peut pas changer son vote.
const showForm = computed(() => {
    if (success.value) return false;
    if (props.hasVoted && !poll.value.allow_vote_change) return false;
    return true;
});

async function handleSubmit() {
    const result = await submit(
        poll.value.secret_token,
        poll.value.allow_multiple_choices,
    );
    // L'API retourne le poll avec les votes_count mis à jour.
    // On injecte ce nouveau poll dans la ref locale → le template se met à jour.
    if (result) {
        poll.value = result;
    }
}

const totalVotes = computed(() => {
    return poll.value.options.reduce(
        (sum, opt) => sum + (opt.votes_count || 0),
        0,
    );
});

const { fetchApi } = useFetchApi("/api/v1");

async function refreshResults() {
    // Brouillon ou fini => pas besoin de rafraîchir
    if (isDraft.value || isExpired.value) return;

    try {
        const data = await fetchApi({
            url: `/polls/${poll.value.secret_token}/results`,
        });
        // L'API renvoie les options
        // On remplace l'array des options, vue rerender
        // les barres / le total via le computed totalVotes
        poll.value.options = data.options;
    } catch {
        // silencieux : on réessaiera au prochain intervalle
    }
}

usePolling(refreshResults, 5000);
</script>

<template>
    <main class="mx-auto max-w-2xl p-4 sm:p-6">
        <header class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ poll.question }}
            </h1>
            <p v-if="poll.title" class="text-sm text-gray-500">
                {{ poll.title }}
            </p>
        </header>

        <div
            v-if="isDraft"
            class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800 ring-1 ring-inset ring-yellow-200"
        >
            Ce sondage n'est pas encore disponible.
        </div>

        <div v-else-if="isExpired" class="space-y-6">
            <div
                class="rounded-md bg-gray-50 p-4 text-sm text-gray-700 ring-1 ring-inset ring-gray-200"
            >
                Ce sondage est terminé.
            </div>

            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Resultats</h2>
                <PollResultsChart :options="poll.options" />
                <p class="text-sm text-gray-500">
                    Total : {{ totalVotes }} vote{{
                        totalVotes === 1 ? "" : "s"
                    }}
                </p>
            </div>
        </div>

        <div v-else-if="canVote" class="space-y-6">
            <div
                v-if="success"
                class="rounded-md bg-green-50 p-4 text-sm text-green-800 ring-1 ring-inset ring-green-200"
            >
                Merci d'avoir voté !
            </div>

            <div
                v-else-if="hasVoted"
                class="rounded-md bg-blue-50 p-4 text-sm text-blue-800 ring-1 ring-inset ring-blue-200"
            >
                Vous avez déjà voté pour ce sondage.
            </div>

            <form
                v-if="showForm"
                class="space-y-4"
                @submit.prevent="handleSubmit"
            >
                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-gray-700">
                        {{
                            poll.allow_multiple_choices
                                ? "Selectionnez une ou plusieurs options"
                                : "Selectionnez une option"
                        }}
                    </legend>
                    <div class="space-y-2">
                        <label
                            v-for="option in poll.options"
                            :key="option.id"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50"
                            :class="{
                                'border-indigo-500 bg-indigo-50': isSelected(
                                    option.id,
                                ),
                            }"
                        >
                            <input
                                :type="
                                    poll.allow_multiple_choices
                                        ? 'checkbox'
                                        : 'radio'
                                "
                                :name="
                                    poll.allow_multiple_choices
                                        ? 'option-' + option.id
                                        : 'poll-option'
                                "
                                :value="option.id"
                                :checked="isSelected(option.id)"
                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @change="
                                    poll.allow_multiple_choices
                                        ? toggleOption(option.id)
                                        : setSingleOption(option.id)
                                "
                            />
                            <span class="text-sm text-gray-700">
                                {{ option.label }}
                            </span>
                        </label>
                    </div>
                </fieldset>

                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
                    >
                        {{ submitting ? "Envoi…" : "Voter" }}
                    </button>
                </div>
            </form>

            <!-- show results -->
            <div
                v-if="showResults"
                class="space-y-4 pt-6 border-t border-gray-200"
            >
                <h2 class="text-lg font-semibold text-gray-900">Resultats</h2>
                <PollResultsChart :options="poll.options" />
                <p class="text-sm text-gray-500">
                    Total : {{ totalVotes }} vote{{
                        totalVotes === 1 ? "" : "s"
                    }}
                </p>
            </div>
        </div>

        <div
            v-else
            class="rounded-md bg-gray-50 p-4 text-sm text-gray-700 ring-1 ring-inset ring-gray-200"
        >
            Ce sondage n'a pas encore démarré.
        </div>
    </main>
</template>
