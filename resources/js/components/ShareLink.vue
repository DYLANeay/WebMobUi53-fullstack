<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    token: { type: String, required: true },
});

const shareUrl = computed(() => {
    return `${window.location.origin}/polls/${props.token}`;
});

//sert pour le toast
const copied = ref(false);

let hideTimeout = null;

async function copy() {
    try {
        await navigator.clipboard.writeText(shareUrl.value);

        
        copied.value = true;

        if (hideTimeout) {
            clearTimeout(hideTimeout);
        }
        hideTimeout = setTimeout(() => {
            // masque le toast après 2 secondes
            copied.value = false;
        }, 2000);
    } catch {
        
    }
}
</script>

<template>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            Lien de partage
        </label>
        <div class="flex gap-2">
            <input
                :value="shareUrl"
                readonly
                type="text"
                class="block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-600 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
            <button
                type="button"
                class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500"
                @click="copy"
            >
                Copier
            </button>
        </div>
    </div>

    <Teleport to="body">
        <div
            v-if="copied"
            class="fixed bottom-4 right-4 z-50 rounded-md bg-gray-900 px-4 py-2 text-sm text-white shadow-lg"
        >
            Lien copié !
        </div>
    </Teleport>
</template>
