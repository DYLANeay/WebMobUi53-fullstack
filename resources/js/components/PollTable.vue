<script setup>
import PollStatusBadge from "./PollStatusBadge.vue";

defineProps({
  polls: { type: Array, default: () => [] },
});

defineEmits(["edit", "delete"]);

function formatDate(value) {
  if (!value) return "-";
  return new Date(value).toLocaleString();
}
</script>

<template>
  <p v-if="polls.length === 0" class="text-sm text-gray-500">
    Aucun sondage pour le moment.
  </p>

  <div v-else class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 font-medium text-gray-700">Question</th>
          <th class="px-4 py-3 font-medium text-gray-700">Statut</th>
          <th class="px-4 py-3 font-medium text-gray-700">Début</th>
          <th class="px-4 py-3 font-medium text-gray-700">Fin</th>
          <th class="px-4 py-3 text-right font-medium text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 bg-white">
        <tr v-for="poll in polls" :key="poll.id" class="hover:bg-gray-50">
          <td class="px-4 py-3 text-gray-900">
            <div class="font-medium">{{ poll.title || poll.question }}</div>
            <div v-if="poll.title" class="text-xs text-gray-500">
              {{ poll.question }}
            </div>
          </td>
          <td class="px-4 py-3">
            <PollStatusBadge :poll="poll" />
          </td>
          <td class="px-4 py-3 text-gray-600">{{ formatDate(poll.started_at) }}</td>
          <td class="px-4 py-3 text-gray-600">{{ formatDate(poll.ends_at) }}</td>
          <td class="px-4 py-3 text-right">
            <button
              type="button"
              class="rounded-md bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
              @click="$emit('edit', poll)"
            >
              Éditer
            </button>
            <button
              type="button"
              class="ml-2 rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-red-500"
              @click="$emit('delete', poll)"
            >
              Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
