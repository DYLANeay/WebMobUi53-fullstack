<script setup>
import { useFetchApi } from "../composables/useFetchApi";

defineProps({
    polls: { type: Array, default: () => [] },
});

const { fetchApi } = useFetchApi();

function editPoll(id) {
    alert(`Editer le sondage ${id}`);
}

async function deletePoll(id) {
    if (!confirm(`Voulez-vous vraiment supprimer le sondage ${id} ?`)) return;

    try {
        await fetchApi({ url: `/polls/${id}`, method: "DELETE" });
        alert(`Sondage ${id} supprimé`);
    } catch (err) {
        console.error(err);
        alert(`Erreur lors de la suppression du sondage ${id}`);
    }
}
</script>

<template>
    <p v-if="polls.length === 0">Aucun sondage.</p>

    <table v-else class="w-full border-collapse text-left">
        <thead>
            <tr>
                <th class="border px-3 py-2">ID</th>
                <th class="border px-3 py-2">Titre</th>
                <th class="border px-3 py-2">Question</th>
                <th class="border px-3 py-2">Brouillon</th>
                <th class="border px-3 py-2">Debut</th>
                <th class="border px-3 py-2">Fin</th>
                <th class="border px-3 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="poll in polls" :key="poll.id">
                <td class="border px-3 py-2">{{ poll.id }}</td>
                <td class="border px-3 py-2">{{ poll.title || "-" }}</td>
                <td class="border px-3 py-2">{{ poll.question }}</td>
                <td class="border px-3 py-2">
                    {{ poll.is_draft ? "Oui" : "Non" }}
                </td>
                <td class="border px-3 py-2">{{ poll.started_at || "-" }}</td>
                <td class="border px-3 py-2">{{ poll.ends_at || "-" }}</td>
                <td class="border px-3 py-2">
                    <button
                        class="bg-blue-500 text-white px-2 py-1 rounded"
                        @click="editPoll(poll.id)"
                    >
                        Editer
                    </button>
                    <button
                        class="bg-red-500 text-white px-2 py-1 rounded"
                        @click="deletePoll(poll.id)"
                    >
                        Supprimer
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
