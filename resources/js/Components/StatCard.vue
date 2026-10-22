<script setup>
defineProps({
    label: String,
    value: [String, Number],
    hint: { type: String, default: '' },
});
</script>

<template>
    <div class="card p-5">
        <p class="text-sm font-medium text-slate-600">{{ label }}</p>
        <p class="mt-1 text-3xl font-bold tracking-tight text-brand-800">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
    </div>
</template>
