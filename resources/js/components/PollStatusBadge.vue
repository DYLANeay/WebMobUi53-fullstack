<script setup>
import { computed } from "vue";

const props = defineProps({
    poll: { type: Object, required: true },
});

const status = computed(() => {
    if (props.poll.is_draft) return "draft";
    if (props.poll.ends_at && new Date(props.poll.ends_at) < new Date()) {
        return "ended";
    }
    return "running";
});

const label = computed(
    () =>
        ({
            draft: "Brouillon",
            running: "En cours",
            ended: "Terminé",
        })[status.value],
);
//strictement la même chose
// const label2 = computed(() => {
//     const map = {
//         draft: "Brouillon",
//         running: "En cours",
//         ended: "Terminé",
//     };
//     return map[status.value];
// });

const classes = computed(
    () =>
        ({
            draft: "bg-gray-100 text-gray-700 ring-gray-300",
            running: "bg-green-100 text-green-700 ring-green-300",
            ended: "bg-red-100 text-red-700 ring-red-300",
        })[status.value],
);
</script>

<template>
    <span
        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
        :class="classes"
    >
        {{ label }}
    </span>
</template>
