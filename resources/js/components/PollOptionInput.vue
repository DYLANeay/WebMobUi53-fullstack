<script setup>
const model = defineModel({ required: true });

defineProps({
    // index sert à afficher le numéro de l'option et à identifier que supprimer
    index: { type: Number, required: true },
});

defineEmits(["remove"]);
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- :for lie le label à l'input via l'id dynamique option-0, option-1, etc. -->
        <label
            :for="`option-${index}`"
            class="w-20 shrink-0 text-sm text-gray-500"
        >
            Option {{ index + 1 }}
        </label>

        <input
            :id="`option-${index}`"
            v-model="model"
            type="text"
            placeholder="Libellé de l'option"
            class="block w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        />

        <button
            type="button"
            class="shrink-0 text-sm text-red-500 hover:text-red-700 disabled:opacity-30"
            :disabled="index < 2"
            @click="$emit('remove', index)"
        >
            ✕
        </button>
    </div>
</template>
