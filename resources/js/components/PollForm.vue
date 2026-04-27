<script setup>
import { ref, watch } from "vue";
import PollOptionInput from "./PollOptionInput.vue";
import { usePollForm } from "../composables/usePollForm";

const emit = defineEmits(["saved"]);

const form = ref({
    title: "",
    question: "",
    options: [{ label: "" }, { label: "" }],
    allow_multiple_choices: false,
    allow_vote_change: false,
    results_public: false,
    duration: null,
});

const { errors, submitting, validate, submit } = usePollForm();

// watch pour la validation live — { deep: true } surveille les propriétés imbriquées
watch(
    form,
    () => {
        if (Object.keys(errors).length > 0) {
            validate(form.value);
        }
    },
    { deep: true },
);

function addOption() {
    if (form.value.options.length < 20) {
        form.value.options.push({ label: "" });
    }
}

function removeOption(index) {
    if (form.value.options.length > 2) {
        form.value.options.splice(index, 1);
    }
}

async function handleSubmit() {
    try {
        const poll = await submit(form.value);
        emit("saved", poll);
    } catch {
        // errors est déjà rempli par usePollForm
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="handleSubmit">
        <!-- Question -->
        <div>
            <label
                for="question"
                class="block text-sm font-medium text-gray-700"
            >
                Question <span class="text-red-500">*</span>
            </label>
            <textarea
                id="question"
                v-model="form.question"
                rows="3"
                placeholder="Ex : Quel est votre langage préféré ?"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                :class="{ 'border-red-400': errors.question }"
            ></textarea>
            <p v-if="errors.question" class="mt-1 text-xs text-red-600">
                {{ errors.question }}
            </p>
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">
                Titre (optionnel)
            </label>
            <input
                id="title"
                v-model="form.title"
                type="text"
                placeholder="Ex : Sondage tech 2025"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
        </div>

        <!-- Options -->
        <div>
            <p class="mb-2 text-sm font-medium text-gray-700">
                Options <span class="text-red-500">*</span>
            </p>
            <div class="space-y-2">
                <PollOptionInput
                    v-for="(option, i) in form.options"
                    :key="i"
                    v-model="form.options[i].label"
                    :index="i"
                    @remove="removeOption"
                />
            </div>
            <p v-if="errors.options" class="mt-1 text-xs text-red-600">
                {{ errors.options }}
            </p>
            <button
                v-if="form.options.length < 20"
                type="button"
                class="mt-3 text-sm text-indigo-600 hover:text-indigo-800"
                @click="addOption"
            >
                + Ajouter une option
            </button>
        </div>

        <!-- Paramètres -->
        <fieldset class="rounded-lg border border-gray-200 p-4">
            <legend class="px-1 text-sm font-medium text-gray-700">
                Paramètres
            </legend>
            <div class="space-y-3">
                <label class="flex items-center gap-3">
                    <input
                        v-model="form.allow_multiple_choices"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                    />
                    <span class="text-sm text-gray-700"
                        >Choix multiples autorisés</span
                    >
                </label>
                <label class="flex items-center gap-3">
                    <input
                        v-model="form.allow_vote_change"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                    />
                    <span class="text-sm text-gray-700"
                        >Modification du vote autorisée</span
                    >
                </label>
                <label class="flex items-center gap-3">
                    <input
                        v-model="form.results_public"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                    />
                    <span class="text-sm text-gray-700">Résultats publics</span>
                </label>
                <div>
                    <label for="duration" class="block text-sm text-gray-700">
                        Durée (secondes, optionnel)
                    </label>
                    <input
                        id="duration"
                        v-model.number="form.duration"
                        type="number"
                        min="60"
                        max="604800"
                        placeholder="Ex : 3600"
                        class="mt-1 block w-40 rounded-md border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
            </div>
        </fieldset>
        <!-- errors -->
        <p v-if="errors._global" class="text-sm text-red-600">
            {{ errors._global }}
        </p>

        <!-- Soumission -->
        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="submitting"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
            >
                {{ submitting ? "Enregistrement…" : "Créer le sondage" }}
            </button>
        </div>
    </form>
</template>
