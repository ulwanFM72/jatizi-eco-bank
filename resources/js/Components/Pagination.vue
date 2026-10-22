<script setup>
import { Link } from '@inertiajs/vue3';

// `links` berasal dari paginator Laravel: [{ url, label, active }]
defineProps({ links: { type: Array, default: () => [] } });
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1" aria-label="Halaman">
        <template v-for="(link, i) in links" :key="i">
            <span
                v-if="!link.url"
                class="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                :class="[
                    'rounded-lg px-3 py-1.5 text-sm font-medium transition',
                    link.active ? 'bg-brand-700 text-white' : 'text-brand-900 hover:bg-brand-100',
                ]"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
