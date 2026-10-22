<script setup>
import { computed, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StatCard from '../../Components/StatCard.vue';
import { fmtGram, fmtNum, kategoriClass, kategoriLabel } from '../../helpers';

const props = defineProps({
    filters: Object,
    kelasOptions: Array,
    ringkasan: Object,
    perKelas: Array,
    perKategori: Array,
});

const f = reactive({ ...props.filters, kelas_id: props.filters.kelas_id ?? '' });

const params = () => Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '' && v !== null));

const tampilkan = () => router.get('/admin/laporan', params(), { preserveState: true, preserveScroll: true });

// PDF dibuat di server dari filter yang sama. Dibuka di tab baru (siap cetak) atau diunduh.
const urlPdf = (unduh) => `/admin/laporan/pdf?${new URLSearchParams({ ...params(), ...(unduh ? { unduh: 1 } : {}) })}`;

const maks = computed(() => Math.max(1, ...props.perKelas.map((k) => k.poin)));
</script>

<template>
    <Head title="Laporan" />

    <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Laporan Setoran</h1>

    <form class="card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4" @submit.prevent="tampilkan">
        <div>
            <label class="label" for="l-dari">Dari tanggal</label>
            <input id="l-dari" v-model="f.dari" type="date" class="input" required />
        </div>
        <div>
            <label class="label" for="l-sampai">Sampai tanggal</label>
            <input id="l-sampai" v-model="f.sampai" type="date" class="input" required />
        </div>
        <div>
            <label class="label" for="l-kelas">Kelas</label>
            <select id="l-kelas" v-model="f.kelas_id" class="input">
                <option value="">Semua kelas</option>
                <option v-for="k in kelasOptions" :key="k.id" :value="k.id">{{ k.nama }}</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn btn-primary w-full">Tampilkan</button>
        </div>
    </form>

    <div class="flex flex-wrap gap-2">
        <a :href="urlPdf(false)" target="_blank" rel="noopener" class="btn btn-soft">🖨 Cetak (PDF)</a>
        <a :href="urlPdf(true)" class="btn btn-outline">⬇ Unduh PDF</a>
    </div>

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Ringkasan laporan">
        <StatCard label="Jumlah transaksi" :value="fmtNum(ringkasan.jumlah_transaksi)" />
        <StatCard label="Total berat" :value="fmtGram(ringkasan.total_gram)" />
        <StatCard label="Total poin" :value="fmtNum(ringkasan.total_poin)" />
    </section>

    <section>
        <h2 class="mb-3 text-lg font-bold text-brand-900">Ringkasan kontribusi per kelas</h2>
        <div class="table-wrap bg-white">
            <table class="min-w-full divide-y divide-brand-100">
                <thead class="bg-brand-100">
                    <tr>
                        <th class="th w-12">#</th>
                        <th class="th">Kelas</th>
                        <th class="th w-1/4">Kontribusi poin</th>
                        <th class="th text-right">Transaksi</th>
                        <th class="th text-right">Berat</th>
                        <th class="th text-right">Poin</th>
                        <th class="th text-right">Sisa berat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    <tr v-if="!perKelas.length">
                        <td colspan="7" class="td py-10 text-center text-slate-500">Tidak ada setoran pada periode ini.</td>
                    </tr>
                    <tr v-for="(k, i) in perKelas" :key="k.id">
                        <td class="td tabular-nums">{{ i + 1 }}</td>
                        <td class="td font-semibold">{{ k.nama }}</td>
                        <td class="td">
                            <div class="bar-track" role="img" :aria-label="`${fmtNum(k.poin)} poin`">
                                <div class="bar-fill" :style="{ width: `${(k.poin / maks) * 100}%` }" />
                            </div>
                        </td>
                        <td class="td text-right tabular-nums">{{ fmtNum(k.jumlah) }}</td>
                        <td class="td text-right tabular-nums">{{ fmtGram(k.gram) }}</td>
                        <td class="td text-right font-semibold tabular-nums text-brand-800">{{ fmtNum(k.poin) }}</td>
                        <td class="td text-right tabular-nums">{{ fmtNum(k.sisa_gram) }} g</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="mb-3 text-lg font-bold text-brand-900">Rekap per kategori</h2>
        <div class="table-wrap bg-white">
            <table class="min-w-full divide-y divide-brand-100">
                <thead class="bg-brand-100">
                    <tr>
                        <th class="th">Kategori</th>
                        <th class="th text-right">Transaksi</th>
                        <th class="th text-right">Berat</th>
                        <th class="th text-right">Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    <tr v-for="k in perKategori" :key="k.kategori">
                        <td class="td"><span :class="['badge', kategoriClass[k.kategori]]">{{ kategoriLabel[k.kategori] }}</span></td>
                        <td class="td text-right tabular-nums">{{ fmtNum(k.jumlah) }}</td>
                        <td class="td text-right tabular-nums">{{ fmtGram(k.gram) }}</td>
                        <td class="td text-right font-semibold tabular-nums text-brand-800">{{ fmtNum(k.poin) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            Poin pada laporan adalah poin dari setoran pada rentang tanggal terpilih. Sisa berat adalah posisi saat ini.
            PDF memuat ringkasan ini ditambah rincian transaksi.
        </p>
    </section>
</template>
