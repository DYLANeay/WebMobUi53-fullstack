<script setup>
import { computed } from "vue";

// defineModel se déclare dans l'enfant, et se lie par nom de model
// implicitement "modelValue", mais on peut aussi lui donner un nom personnalisé (rien à voir), ici "open"
const open = defineModel({ type: Boolean, required: true });

const props = defineProps({
    title: { type: String, default: "Confirmer" },
    message: { type: String, default: "Êtes-vous sûr ?" },
    confirmLabel: { type: String, default: "Confirmer" },
    cancelLabel: { type: String, default: "Annuler" },
    // variant pilote la couleur du bouton de confirmation.
    // "danger" (rouge) par défaut, "success" (vert) pour les actions positives.
    variant: { type: String, default: "danger" },
});

const emit = defineEmits(["confirm", "cancel"]);

const confirmClass = computed(() =>
    props.variant === "success"
        ? "bg-green-600 hover:bg-green-500"
        : "bg-red-600 hover:bg-red-500",
);

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
                        class="rounded-md px-3 py-2 text-sm font-medium text-white shadow-sm"
                        :class="confirmClass"
                        @click="confirm"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
