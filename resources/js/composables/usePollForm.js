import { reactive, ref } from "vue";
import { useFetchApi } from "./useFetchApi";

export function usePollForm(pollId = null) {
    //cree une instance du composable avec /api/v1 comme base
    const { fetchApi } = useFetchApi("/api/v1");

    const isEdit = !!pollId;
    const errors = ref({});
    const submitting = ref(false);

    function clearErrors() {
        Object.keys(errors).forEach((k) => delete errors[k]);
    }

    function validate(form) {
        clearErrors();

        if (!form.question || form.question.trim().length < 3) {
            errors.question =
                "La question doit contenir au moins 3 caractères.";
        }
        if (form.question && form.question.trim().length > 500) {
            errors.question =
                "La question ne peut pas dépasser 500 caractères.";
        }

        const filled = form.options.filter((o) => o.label.trim() !== "");
        if (filled.length < 2) {
            errors.options = "Veuillez saisir au moins 2 options.";
        }

        return Object.keys(errors).length === 0;
    }

    async function submit(form) {
        // Validation côté front avant tout envoi réseau
        // Si invalide : on rejette la promesse, PollForm récupère l'erreur dans son catch
        if (!validate(form)) {
            return Promise.reject(errors);
        }

        submitting.value = true; // désactive le bouton "Créer" pendant l'envoi
        clearErrors();

        // Construit le payload à envoyer à l'API :

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
            // Envoie POST ou PUT et retourne le poll
            const poll = await fetchApi({
                url: isEdit ? `/polls/${pollId}` : "/polls",
                method: isEdit ? "PUT" : "POST",
                data: payload,
            });
            return poll;
        } catch (err) {
            errors._global = err.data?.message ?? "Une erreur est survenue.";
            return Promise.reject(errors);
        } finally {
            // Toujours exécuté^ : réactive le bouton
            submitting.value = false;
        }
    }

    return { errors, submitting, validate, submit, isEdit };
}
