<script setup>
import PollForm from "./components/PollForm.vue";

const props = defineProps({
    dashboardUrl: { type: String, required: true },
});

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
                Nouveau sondage
            </h1>
            <p class="text-sm text-gray-500">
                Le sondage sera créé en brouillon, vous pourrez le lancer plus
                tard.
            </p>
        </header>

        <PollForm @saved="onSaved" />
    </main>
</template>
