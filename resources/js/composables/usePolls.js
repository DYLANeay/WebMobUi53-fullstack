import { ref } from "vue";
import { useFetchApi } from "./useFetchApi";

/**
 * Composable de gestion de la liste des sondages de l'utilisateur connecté.
 *
 * @param {Array} initialPolls - liste initiale (eager-loadée depuis Blade)
 */
export function usePolls(initialPolls = []) {
    const polls = ref([...initialPolls]);
    const error = ref(null);

    const { fetchApi } = useFetchApi();

    async function remove(id) {
        //delete de l'interface
        const previous = polls.value;
        polls.value = polls.value.filter((p) => p.id !== id);
        error.value = null;
        //delete de la db
        try {
            await fetchApi({ url: `/polls/${id}`, method: "DELETE" });
        } catch (err) {
            polls.value = previous;
            error.value = err?.data?.message || "Suppression impossible.";
        }
    }

    return { polls, error, remove };
}
