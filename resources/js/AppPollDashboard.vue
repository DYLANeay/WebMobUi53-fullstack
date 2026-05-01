<script setup>
import { ref } from "vue";
import PollTable from "./components/PollTable.vue";
import ConfirmModal from "./components/ConfirmModal.vue";
import { usePolls } from "./composables/usePolls";

//rend data accessible dans compo
const props = defineProps({
    polls: { type: Array, default: () => [] },
});

// Utilise le composable pour gérer les sondages et on utilise une ref pour les polls!
// car une prop est en lecture seule, on ne peut pas la modifier directement, mais usePolls nous fournit une ref réactive qui contient les sondages et que nous pouvons mettre à jour
const { polls, error, remove } = usePolls(props.polls);

const confirmOpen = ref(false);
const pollToDelete = ref(null);

function askDelete(poll) {
    pollToDelete.value = poll;
    //ouvre la modal
    confirmOpen.value = true;
}

function confirmDelete() {
    if (pollToDelete.value) {
        //remove qui vient du composable usePolls, on lui passe l'id du sondage à supprimer
        remove(pollToDelete.value.id);
        pollToDelete.value = null;
    }
}

function goCreate() {
    window.location.href = "/polls/create";
}

function goEdit(poll) {
    window.location.href = `/polls/${poll.id}/edit`;
}
</script>

<template>
    <main class="mx-auto max-w-5xl p-4 sm:p-6">
        <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Mes sondages
                </h1>
                <p class="text-sm text-gray-500">
                    Gérez et lancez vos sondages.
                </p>
            </div>
            <button
                type="button"
                class="rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500"
                @click="goCreate"
            >
                + Nouveau sondage
            </button>
        </header>

        <div
            v-if="error"
            class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 ring-1 ring-inset ring-red-200"
        >
            {{ error }}
        </div>

        <PollTable :polls="polls" @edit="goEdit" @delete="askDelete" />

        <!-- @confirm qui est déclenché quand l'utilisateur confirme la suppression, on appelle la fonction confirmDelete -->
        <!-- //si l'utilisateur annule, on ne fait rien donc pas besoin de gérer l'événement cancel -->
        <ConfirmModal
            v-model:open="confirmOpen"
            title="Supprimer le sondage"
            :message="
                pollToDelete
                    ? `Voulez-vous vraiment supprimer « ${pollToDelete.title || pollToDelete.question} » ? Cette action est irréversible.`
                    : ''
            "
            confirm-label="Supprimer"
            @confirm="confirmDelete"
        />
    </main>
</template>
