<script setup>
import { toRef } from "vue";
import { usePollStatus } from "../../composables/poll/usePollStatus";

const props = defineProps({
    poll: { type: Object, required: true },
});

const { statusLabel, statusClass } = usePollStatus(toRef(props, "poll"), {
    withClock: true,
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
        :class="statusClass"
    >
        {{ statusLabel }}
    </span>
</template>
