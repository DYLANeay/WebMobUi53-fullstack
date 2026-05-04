import { computed, ref, watchEffect } from "vue";

/**
 * Centralise les computed de statut d'un poll.
 */
export function usePollStatus(pollRef, { withClock = false } = {}) {
    const now = ref(new Date());

    if (withClock) {
        watchEffect((onCleanup) => {
            const interval = setInterval(() => {
                now.value = new Date();
            }, 1000);
            onCleanup(() => clearInterval(interval));
        });
    }

    const isDraft = computed(() => pollRef.value?.is_draft ?? true);

    const isRunning = computed(() => {
        if (!pollRef.value?.started_at) return false;
        return new Date(pollRef.value.started_at) <= now.value;
    });

    const isExpired = computed(() => {
        if (!pollRef.value?.ends_at) return false;
        return new Date(pollRef.value.ends_at) <= now.value;
    });

    const statusLabel = computed(() => {
        if (isDraft.value) return "Brouillon";
        if (isExpired.value) return "Termine";
        if (isRunning.value) return "En cours";
        return "Inconnu";
    });

    const statusClass = computed(() => {
        if (isDraft.value) return "bg-gray-100 text-gray-700 ring-gray-300";
        if (isExpired.value) return "bg-red-100 text-red-700 ring-red-300";
        if (isRunning.value)
            return "bg-green-100 text-green-700 ring-green-300";
        return "bg-gray-100 text-gray-700";
    });

    return {
        now,
        isDraft,
        isRunning,
        isExpired,
        statusLabel,
        statusClass,
    };
}
