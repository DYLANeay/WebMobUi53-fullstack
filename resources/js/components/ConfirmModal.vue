<script setup>
const open = defineModel({ type: Boolean, required: true });

defineProps({
    title: { type: String, default: "Confirmer" },
    message: { type: String, default: "Êtes-vous sûr ?" },
    confirmLabel: { type: String, default: "Confirmer" },
    cancelLabel: { type: String, default: "Annuler" },
});

const emit = defineEmits(["confirm", "cancel"]);

function confirm() {
    emit("confirm");
    open.value = false;
}

function cancel() {
    emit("cancel");
    open.value = false;
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="cancel"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ message }}</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        @click="cancel"
                    >
                        {{ cancelLabel }}
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-500"
                        @click="confirm"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
