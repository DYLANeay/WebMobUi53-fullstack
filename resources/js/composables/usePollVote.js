import { ref } from "vue";
import { useFetchApi } from "./useFetchApi";

export function usePollVote() {
    const { fetchApi } = useFetchApi("/api/v1");

    //contains the ids of selcted options
    const selectedOptions = ref([]);
    const submitting = ref(false);
    const error = ref(null);
    const success = ref(false);

    function toggleOption(optionId) {
        const index = selectedOptions.value.indexOf(optionId);
        if (index > -1) {
            selectedOptions.value.splice(index, 1);
        } else {
            selectedOptions.value.push(optionId);
        }
    }

    function isSelected(optionId) {
        return selectedOptions.value.includes(optionId);
    }

    async function submit(token, allowMultiple) {
        if (selectedOptions.value.length === 0) {
            error.value = "Veuillez sélectionner au moins une option.";
            return;
        }

        if (!allowMultiple && selectedOptions.value.length > 1) {
            error.value = "Vous ne pouvez sélectionner qu'une seule option.";
            return;
        }

        submitting.value = true;
        error.value = null;
        success.value = false;

        try {
            await fetchApi({
                url: `/polls/${token}/vote`,
                method: "POST",
                data: {
                    options: selectedOptions.value,
                },
            });
            success.value = true;
        } catch (err) {
            if (err.status === 409) {
                error.value =
                    err.data?.message ?? "Vous avez deja voté pour ce sondage.";
            } else if (err.status === 403) {
                error.value =
                    err.data?.message ?? "Ce sondage n'est pas accessible.";
            } else if (err.status === 422) {
                error.value = err.data?.message ?? "Données invalides.";
            } else {
                error.value = "Une erreur est survenue lors du vote.";
            }
        } finally {
            submitting.value = false;
        }
    }

    function reset() {
        selectedOptions.value = [];
        error.value = null;
        success.value = false;
    }

    return {
        selectedOptions,
        submitting,
        error,
        success,
        toggleOption,
        isSelected,
        submit,
        reset,
    };
}
