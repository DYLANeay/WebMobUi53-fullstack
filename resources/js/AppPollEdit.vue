<script setup>
import { computed, ref } from "vue";
import PollForm from "./components/PollForm.vue";
import ShareLink from "./components/ShareLink.vue";
import { useFetchApi } from "./composables/useFetchApi";

const props = defineProps({
    poll: { type: Object, default: null },
    dashboardUrl: { type: String, required: true },
});

//définis si la page est en mode edit ou create
const isEdit = !!props.poll;

// par défaut true si pas de poll
const isDraft = computed(() => props.poll?.is_draft ?? true);

const isRunning = computed(() => {
    if (!props.poll?.started_at) return false;
    return new Date(props.poll.started_at) <= new Date();
});

const isExpired = computed(() => {
    if (!props.poll?.ends_at) return false;
    return new Date(props.poll.ends_at) <= new Date();
});

const statusLabel = computed(() => {
    if (isDraft.value) return "Brouillon";
    if (isExpired.value) return "Terminé";
    if (isRunning.value) return "En cours";
    return "Inconnu";
});

//return classes tw pour le badge de statut, en fonction du statut du sondage
const statusClass = computed(() => {
    if (isDraft.value) return "bg-gray-100 text-gray-700 ring-gray-300";
    if (isExpired.value) return "bg-red-100 text-red-700 ring-red-300";
    if (isRunning.value) return "bg-green-100 text-green-700 ring-green-300";
    return "bg-gray-100 text-gray-700";
});

const { fetchApi } = useFetchApi("/api/v1");
const launching = ref(false);
const launchError = ref(null);

async function launchPoll() {
    const ok = confirm(
        "Lancer le sondage maintenant ? Il ne pourra plus être modifié.",
    );
    if (!ok) return;

    launching.value = true;
    launchError.value = null;

    try {
        await fetchApi({
            url: `/polls/${props.poll.id}/start`,
            method: "POST",
        });
        // Recharge la page pour refléter le nouveau statut depuis Blade.
        window.location.reload();
    } catch (err) {
        launchError.value =
            err.data?.message ?? "Impossible de lancer le sondage.";
    } finally {
        launching.value = false;
    }
}

function onSaved() {
    window.location.href = props.dashboardUrl;
}
</script>

<template>
    <main class="mx-auto max-w-2xl p-4 sm:p-6">
        <header class="mb-6">
            <a
                :href="dashboardUrl"
                class="mb-3 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700"
            >
                ← Retour au dashboard
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ isEdit ? "Modifier le sondage" : "Nouveau sondage" }}
                </h1>
                <span
                    v-if="isEdit"
                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                    :class="statusClass"
                >
                    {{ statusLabel }}
                </span>
            </div>
            <p class="text-sm text-gray-500">
                {{
                    isEdit
                        ? "Modifiez les informations de votre sondage."
                        : "Le sondage sera créé en brouillon, vous pourrez le lancer plus tard."
                }}
            </p>
        </header>

        <PollForm :poll="poll" @saved="onSaved" />

        <!-- Panneau de lancement (visible uniquement en brouillon) -->
        <div
            v-if="isEdit && isDraft"
            class="mt-8 rounded-lg border border-indigo-200 bg-indigo-50 p-6"
        >
            <h2 class="text-lg font-semibold text-indigo-900">
                Lancer le sondage
            </h2>
            <p class="mt-1 text-sm text-indigo-700">
                Une fois lancé, le sondage ne pourra plus être modifié.
            </p>

            <!-- Récapitulatif des paramètres, dl = definition list -->
            <dl class="mt-4 space-y-1 text-sm text-indigo-800">
                <div class="flex gap-2">
                    <!-- dt = definition term -->
                    <dt class="font-medium">Choix multiples :</dt>
                    <!--  dt = definition description -->
                    <dd>{{ poll.allow_multiple_choices ? "Oui" : "Non" }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="font-medium">Modification du vote :</dt>
                    <dd>{{ poll.allow_vote_change ? "Oui" : "Non" }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="font-medium">Résultats publics :</dt>
                    <dd>{{ poll.results_public ? "Oui" : "Non" }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="font-medium">Durée :</dt>
                    <dd>
                        {{
                            poll.duration
                                ? `${poll.duration} secondes`
                                : "Illimitée"
                        }}
                    </dd>
                </div>
            </dl>

            <p v-if="launchError" class="mt-4 text-sm text-red-600">
                {{ launchError }}
            </p>

            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    :disabled="launching"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
                    @click="launchPoll"
                >
                    {{ launching ? "Lancement…" : "Lancer maintenant" }}
                </button>
            </div>
        </div>

        <!-- Panneau de partage (visible uniquement si lancé) -->
        <div
            v-if="isEdit && !isDraft"
            class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
        >
            <h2 class="text-lg font-semibold text-gray-900">
                Partager le sondage
            </h2>
            <div class="mt-4 space-y-4">
                <ShareLink :token="poll.secret_token" />

                <div class="space-y-1 text-sm text-gray-600">
                    <p>
                        <span class="font-medium">Début :</span>
                        {{
                            poll.started_at
                                ? new Date(poll.started_at).toLocaleString()
                                : "-"
                        }}
                    </p>
                    <p>
                        <span class="font-medium">Fin :</span>
                        {{
                            poll.ends_at
                                ? new Date(poll.ends_at).toLocaleString()
                                : "Illimitée"
                        }}
                    </p>
                </div>
            </div>
        </div>
    </main>
</template>
