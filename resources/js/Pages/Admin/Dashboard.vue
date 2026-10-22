<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StatCard from '../../Components/StatCard.vue';
import SetoranTable from '../../Components/SetoranTable.vue';
import SetoranDetail from '../../Components/SetoranDetail.vue';
import { fmtGram, fmtNum, kategoriClass, kategoriLabel } from '../../helpers';

const props = defineProps({
    ringkasan: Object,
    hariIni: Number,
    kategori: Array,
    peringkat: Array,
    terbaru: Array,
});

const detail = ref(null);
const maks = computed(() => Math.max(1, ...props.kategori.map((k) => k.gram)));
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Dashboard</h1>
        <Link href="/admin/setoran" class="btn btn-primary">+ Catat setoran</Link>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik">
        <StatCard label="Total sampah terkumpul" :value="fmtGram(ringkasan.total_gram)" />
        <StatCard label="Total poin" :value="fmtNum(ringkasan.total_poin)" />
        <StatCard label="Jumlah transaksi" :value="fmtNum(ringkasan.jumlah_transaksi)" />
        <StatCard label="Transaksi hari ini" :value="fmtNum(hariIni)" />
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card p-5">
            <h2 class="text-lg font-bold text-brand-900">Per kategori sampah</h2>
            <ul class="mt-4 space-y-4">
                <li v-for="k in kategori" :key="k.kategori">
                    <div class="flex items-center justify-between text-sm">
                        <span :class="['badge', kategoriClass[k.kategori]]">{{ kategoriLabel[k.kategori] }}</span>
                        <span class="font-semibold tabular-nums text-slate-700">{{ fmtGram(k.gram) }} · {{ fmtNum(k.poin) }} poin</span>
                    </div>
                    <div class="bar-track mt-1.5" role="img" :aria-label="`${kategoriLabel[k.kategori]}: ${fmtGram(k.gram)}`">
                        <div class="bar-fill" :style="{ width: `${(k.gram / maks) * 100}%` }" />
                    </div>
                </li>
            </ul>
        </section>

        <section class="card p-5">
            <h2 class="text-lg font-bold text-brand-900">Peringkat kelas (5 teratas)</h2>
            <ol v-if="peringkat.length" class="mt-4 divide-y divide-brand-100">
                <li v-for="k in peringkat" :key="k.id" class="flex items-center justify-between py-2.5 text-sm">
                    <span class="flex items-center gap-3">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-900">{{ k.rank }}</span>
                        <span class="font-medium text-slate-800">{{ k.nama }}</span>
                    </span>
                    <span class="font-bold tabular-nums text-brand-800">{{ fmtNum(k.total_poin) }} poin</span>
                </li>
            </ol>
            <p v-else class="mt-4 text-sm text-slate-500">Belum ada kelas.</p>
        </section>
    </div>

    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-bold text-brand-900">Setoran terbaru</h2>
            <Link href="/admin/setoran" class="text-sm font-semibold text-brand-700 hover:underline">Kelola setoran →</Link>
        </div>
        <SetoranTable :rows="terbaru" mode="staff" @detail="detail = $event" />
    </section>

    <SetoranDetail :setoran="detail" @close="detail = null" />
</template>
