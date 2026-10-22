<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
</script>

<template>
    <div class="space-y-2" aria-live="polite">
        <div
            v-if="success"
            role="status"
            class="rounded-xl border border-brand-300 bg-brand-100 px-4 py-3 text-sm font-medium text-brand-900"
        >
            {{ success }}
        </div>
        <div
            v-if="error"
            role="alert"
            class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"
        >
            {{ error }}
        </div>
    </div>
</template>
