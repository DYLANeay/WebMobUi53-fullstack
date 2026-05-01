<script setup>
import PollForm from "./components/PollForm.vue";

const props = defineProps({
    poll: { type: Object, default: null },
    dashboardUrl: { type: String, required: true },
});

const isEdit = !!props.poll;

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
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ isEdit ? "Modifier le sondage" : "Nouveau sondage" }}
            </h1>
            <p class="text-sm text-gray-500">
                {{
                    isEdit
                        ? "Modifiez les informations de votre sondage."
                        : "Le sondage sera créé en brouillon, vous pourrez le lancer plus tard."
                }}
            </p>
        </header>

        <PollForm :poll="poll" @saved="onSaved" />
    </main>
</template>
