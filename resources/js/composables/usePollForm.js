import { reactive, ref } from "vue";
import { useFetchApi } from "./useFetchApi";

export function usePollForm() {
    //cree une instance du composable avec /api/v1 comme base
    const { fetchApi } = useFetchApi("/api/v1");

    const errors = reactive({});
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
        // - normalise question et labels en NFC (cohérence Unicode)
        // - filtre les options laissées vides par l'utilisateur
        // - met title/duration à null si vides (plutôt que chaîne vide)
        const payload = {
            question: form.question.normalize("NFC"),
            title: form.title || null,
            options: form.options
                .filter((o) => o.label.trim() !== "")
                .map((o) => ({ label: o.label.normalize("NFC") })),
            allow_multiple_choices: form.allow_multiple_choices,
            allow_vote_change: form.allow_vote_change,
            results_public: form.results_public,
            duration: form.duration || null,
        };

        try {
            // Envoie POST /api/v1/polls et retourne le poll créé (avec ses options)
            const poll = await fetchApi({
                url: "/polls",
                method: "POST",
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

    return { errors, submitting, validate, submit };
}
