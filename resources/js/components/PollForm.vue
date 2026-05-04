<script setup>
import { ref, watch } from "vue";
import PollOptionInput from "./PollOptionInput.vue";
import { usePollForm } from "../composables/usePollForm";

const props = defineProps({
    poll: { type: Object, default: null },
});

const emit = defineEmits(["saved"]);

function buildInitialForm() {
    //donc si le poll a bien été repassé par laravel, on pré-remplit le formulaire avec ses données, sinon on part sur des valeurs par défaut pour la création
    if (props.poll) {
        return {
            id: props.poll.id,
            title: props.poll.title ?? "",
            question: props.poll.question ?? "",
            //on map les options du poll pour ne garder que l'id et le label, et si jamais il n'y en a aucune (cas improbable), on initialise avec 2 options vides
            options: props.poll.options?.map((o) => ({
                id: o.id,
                label: o.label,
            })) ?? [{ label: "" }, { label: "" }],
            allow_multiple_choices: props.poll.allow_multiple_choices ?? false,
            allow_vote_change: props.poll.allow_vote_change ?? false,
            results_public: props.poll.results_public ?? false,
            duration: props.poll.duration ?? null,
            is_draft: props.poll.is_draft ?? true,
        };
    }
    return {
        title: "",
        question: "",
        options: [{ label: "" }, { label: "" }],
        allow_multiple_choices: false,
        allow_vote_change: false,
        results_public: false,
        duration: null,
        is_draft: true,
    };
}

const form = ref(buildInitialForm());

const pollId = props.poll?.id ?? null;
const { formErrors, globalError, submitting, validate, submit, isEdit } =
    usePollForm(pollId);

// watch pour la validation live — { deep: true } surveille les propriétés imbriquées
watch(
    form,
    () => {
        if (Object.keys(formErrors.value).length > 0) {
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
    <div
        v-if="!form.is_draft"
        class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800 ring-1 ring-inset ring-yellow-200"
    >
        Ce sondage est lancé et ne peut plus être modifié.
    </div>

    <form v-else class="space-y-6" @submit.prevent="handleSubmit">
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
                :class="{ 'border-red-400': formErrors.question }"
            ></textarea>
            <p v-if="formErrors.question" class="mt-1 text-xs text-red-600">
                {{ formErrors.question }}
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
            <p v-if="formErrors.options" class="mt-1 text-xs text-red-600">
                {{ formErrors.options }}
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
        <p v-if="globalError" class="text-sm text-red-600">
            {{ globalError }}
        </p>

        <!-- Soumission -->
        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="submitting"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
            >
                {{
                    submitting
                        ? "Enregistrement…"
                        : isEdit
                          ? "Enregistrer les modifications"
                          : "Créer le sondage"
                }}
            </button>
        </div>
    </form>
</template>
