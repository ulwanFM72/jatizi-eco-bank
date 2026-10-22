<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { fmtGram, fmtNum } from '../helpers';

const props = defineProps({ peringkat: Array });

const juara = computed(() => props.peringkat.filter((k) => k.rank === 1 && k.total_poin > 0));
const maks = computed(() => Math.max(1, ...props.peringkat.map((k) => k.total_poin)));

// Peringkat dimuat ulang tiap 30 detik agar selalu mengikuti setoran terbaru.
let timer;
onMounted(() => {
    timer = setInterval(() => {
        if (!document.hidden) router.reload({ only: ['peringkat'], preserveScroll: true });
    }, 30000);
});
onUnmounted(() => clearInterval(timer));

const medali = { 1: '🥇', 2: '🥈', 3: '🥉' };
</script>

<template>
    <Head title="Peringkat Kelas" />

    <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Peringkat Kelas</h1>
    <p class="mt-1 text-sm text-slate-600">
        Diurutkan menurut total poin, lalu total berat sampah. Diperbarui otomatis setelah setoran dicatat.
    </p>

    <section v-if="juara.length" class="hero mt-5 rounded-2xl px-6 py-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-brand-800">Kontribusi lingkungan tertinggi</p>
        <p class="mt-1 text-2xl font-extrabold text-brand-900">
            🏆 {{ juara.map((k) => k.nama).join(' · ') }}
        </p>
        <p class="text-sm text-brand-900/90">
            {{ fmtNum(juara[0].total_poin) }} poin · {{ fmtGram(juara[0].total_gram) }} sampah terkumpul
        </p>
    </section>

    <section class="mt-5">
        <div class="table-wrap bg-white">
            <table class="min-w-full divide-y divide-brand-100">
                <thead class="bg-brand-100">
                    <tr>
                        <th class="th w-20">Peringkat</th>
                        <th class="th">Kelas</th>
                        <th class="th w-1/3">Perolehan</th>
                        <th class="th text-right">Total berat</th>
                        <th class="th text-right">Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    <tr v-if="!peringkat.length">
                        <td colspan="5" class="td py-10 text-center text-slate-500">Belum ada kelas terdaftar.</td>
                    </tr>
                    <tr v-for="k in peringkat" :key="k.id" :class="k.rank <= 3 && k.total_poin > 0 ? 'bg-brand-50/70' : ''">
                        <td class="td font-bold tabular-nums text-brand-800">
                            <span v-if="medali[k.rank] && k.total_poin > 0" aria-hidden="true">{{ medali[k.rank] }}</span>
                            {{ k.rank }}
                        </td>
                        <td class="td font-semibold text-slate-800">{{ k.nama }}</td>
                        <td class="td">
                            <div class="bar-track" role="img" :aria-label="`${fmtNum(k.total_poin)} poin`">
                                <div class="bar-fill" :style="{ width: `${(k.total_poin / maks) * 100}%` }" />
                            </div>
                        </td>
                        <td class="td text-right tabular-nums">{{ fmtGram(k.total_gram) }}</td>
                        <td class="td text-right text-base font-bold tabular-nums text-brand-800">{{ fmtNum(k.total_poin) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
