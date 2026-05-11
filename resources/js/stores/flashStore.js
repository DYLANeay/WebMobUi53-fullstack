import { ref } from "vue";

// Store global du flash toast.
export const message = ref("");
export const type = ref("success"); // "success" | "error" | "info"
export const visible = ref(false);

let timer = null;

export function flash(msg, flashType = "success", duration = 3000) {
    if (timer) clearTimeout(timer);
    message.value = msg;
    type.value = flashType;
    visible.value = true;
    timer = setTimeout(() => {
        visible.value = false;
    }, duration);
}

export function dismiss() {
    visible.value = false;
    if (timer) clearTimeout(timer);
}
