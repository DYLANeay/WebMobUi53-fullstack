<script setup>
import PollTable from "./components/PollTable.vue";

const { polls } = defineProps({
    polls: {
        type: Object,
        required: true,
    },
});

console.log(polls);
</script>

<template>
    <PollTable :polls="polls" />
    <div>Hello World</div>
</template>

<style scoped></style>
