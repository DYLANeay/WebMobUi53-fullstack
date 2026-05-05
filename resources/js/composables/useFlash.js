import { ref } from "vue";

const message = ref("");
const type = ref("success"); // "success" | "error" | "info"
const visible = ref(false);

let timer = null;

export function useFlash() {
    function flash(msg, flashType = "success", duration = 3000) {
        if (timer) clearTimeout(timer);
        message.value = msg;
        type.value = flashType;
        visible.value = true;
        timer = setTimeout(() => {
            visible.value = false;
        }, duration);
    }

    function dismiss() {
        visible.value = false;
        if (timer) clearTimeout(timer);
    }

    return { message, type, visible, flash, dismiss };
}
