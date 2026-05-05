<script setup>
import { computed } from "vue";
import { Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";

// Enregistrement des modules Chart.js utilisés
ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
);

const props = defineProps({
    options: { type: Array, required: true },
});

//se recalcule quand les options changent, pour mettre à jour le graphique automatiquement
const chartData = computed(() => ({
    // axe x
    labels: props.options.map((o) => o.label),
    datasets: [
        {
            label: "Votes",
            backgroundColor: "#4f46e5",
            borderRadius: 6,
            // axe y
            data: props.options.map((o) => o.votes_count || 0),
        },
    ],
}));

const chartOptions = {
    // Bar chart vertical (par défaut Chart.js) : barres montantes
    responsive: true,
    // cf div class="h-72"
    maintainAspectRatio: false,
    plugins: {
        // hide "votes" legend
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { precision: 0 }, // pas de demi-vote
        },
    },
};
</script>

<template>
    <!-- on contraint la hauteur sur le wrapper -->
    <div class="h-72">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
