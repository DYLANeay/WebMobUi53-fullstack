import { ref } from "vue";
import { useFetchApi } from "./useFetchApi";

export function usePollForm(pollId = null) {
    const { fetchApi } = useFetchApi("/api/v1");

    const isEdit = !!pollId;
    const formErrors = ref({});
    const globalError = ref(null);
    const submitting = ref(false);

    function clearformErrors() {
        formErrors.value = {};
        globalError.value = null;
    }

    function validate(form) {
        clearformErrors();

        if (!form.question || form.question.trim().length < 3) {
            formErrors.value.question =
                "La question doit contenir au moins 3 caractères.";
        }
        if (form.question && form.question.trim().length > 500) {
            formErrors.value.question =
                "La question ne peut pas dépasser 500 caractères.";
        }

        const filled = form.options.filter((o) => o.label.trim() !== "");
        if (filled.length < 2) {
            formErrors.value.options = "Veuillez saisir au moins 2 options.";
        }

        return Object.keys(formErrors.value).length === 0;
    }

    async function submit(form) {
        if (!validate(form)) {
            throw formErrors;
        }

        submitting.value = true;
        clearformErrors();

        const payload = {
            question: form.question.normalize("NFC"),
            title: form.title || null,
            options: form.options
                .filter((o) => o.label.trim() !== "")
                .map((o) => ({
                    ...(o.id ? { id: o.id } : {}),
                    label: o.label.normalize("NFC"),
                })),
            allow_multiple_choices: form.allow_multiple_choices,
            allow_vote_change: form.allow_vote_change,
            results_public: form.results_public,
            duration: form.duration || null,
        };

        try {
            const poll = await fetchApi({
                url: isEdit ? `/polls/${pollId}` : "/polls",
                method: isEdit ? "PUT" : "POST",
                data: payload,
            });
            return poll;
        } catch (err) {
            globalError.value = err.data?.message ?? "Une erreur est survenue.";
            throw globalError;
        } finally {
            submitting.value = false;
        }
    }

    return { formErrors, globalError, submitting, validate, submit, isEdit };
}
