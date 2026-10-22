<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean,
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-xl' },
});
const emit = defineEmits(['close']);

const onKey = (e) => {
    if (e.key === 'Escape' && props.show) emit('close');
};
onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-brand-900/50 p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        @mousedown.self="emit('close')"
    >
        <div :class="['card my-8 w-full', maxWidth]">
            <div class="flex items-center justify-between border-b border-brand-100 px-5 py-3">
                <h2 class="text-base font-semibold text-brand-900">{{ title }}</h2>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-500 hover:bg-brand-50 hover:text-slate-800"
                    aria-label="Tutup"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </div>
            <div class="p-5">
                <slot />
            </div>
        </div>
    </div>
</template>
