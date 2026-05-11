import { ref } from "vue";
import { useFetchApi } from "./useFetchApi";
import { flash } from "../stores/flashStore";

export function usePolls(initialPolls = []) {
    const polls = ref([...initialPolls]);
    const error = ref(null);

    const { fetchApi } = useFetchApi();

    async function remove(id) {
        const previous = polls.value;
        polls.value = polls.value.filter((p) => p.id !== id);
        error.value = null;
        try {
            await fetchApi({ url: `/polls/${id}`, method: "DELETE" });
            flash("Sondage supprimé.", "success");
        } catch (err) {
            polls.value = previous;
            const msg = err?.data?.message || "Suppression impossible.";
            error.value = msg;
            flash(msg, "error");
        }
    }

    return { polls, error, remove };
}
