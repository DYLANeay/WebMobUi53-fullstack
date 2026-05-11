<script setup>
import { message, type, visible, dismiss } from "../../stores/flashStore";
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-4 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-4 opacity-0"
        >
            <!-- c'est ce v-if qui dicte si le toast est visible -->
            <div
                v-if="visible"
                class="fixed bottom-4 left-1/2 z-50 -translate-x-1/2 w-max max-w-sm px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-3"
                :class="{
                    'bg-green-600 text-white': type === 'success',
                    'bg-red-600 text-white': type === 'error',
                    'bg-indigo-600 text-white': type === 'info',
                }"
                role="alert"
            >
                <span>{{ message }}</span>
                <button
                    type="button"
                    class="ml-auto shrink-0 opacity-70 hover:opacity-100"
                    @click="dismiss"
                    aria-label="Fermer"
                >
                    ✕
                </button>
            </div>
        </Transition>
    </Teleport>
</template>
