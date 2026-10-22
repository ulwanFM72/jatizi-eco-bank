<script setup>
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '../Components/Pagination.vue';
import SetoranTable from '../Components/SetoranTable.vue';
import SetoranDetail from '../Components/SetoranDetail.vue';

const props = defineProps({
    setoran: Object,
    filters: Object,
    kelasOptions: Array,
    mode: String, // publik | pribadi | staff
});

const f = reactive({ ...props.filters });
const detail = ref(null);

const cari = () =>
    router.get('/riwayat', Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '' && v !== null)), {
        preserveState: true,
        preserveScroll: true,
    });

const reset = () => {
    Object.assign(f, { dari: '', sampai: '', kelas_id: '', q: '' });
    cari();
};

const judul = {
    publik: 'Riwayat Setoran',
    pribadi: 'Riwayat Setoran Saya',
    staff: 'Riwayat Setoran',
};
</script>

<template>
    <Head title="Riwayat Setoran" />

    <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">{{ judul[mode] }}</h1>

    <p v-if="mode === 'publik'" class="mt-2 rounded-xl border border-brand-200 bg-white px-4 py-3 text-sm text-slate-600">
        Anda melihat data ringkas dengan nama penyetor disamarkan.
        <Link href="/masuk" class="font-semibold text-brand-700 hover:underline">Masuk</Link>
        untuk melihat riwayat setoran pribadi.
    </p>
    <p v-else-if="mode === 'pribadi'" class="mt-2 text-sm text-slate-600">Hanya setoran atas nama Anda yang ditampilkan.</p>

    <form class="card mt-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="cari">
        <div>
            <label class="label" for="dari">Dari tanggal</label>
            <input id="dari" v-model="f.dari" type="date" class="input" />
        </div>
        <div>
            <label class="label" for="sampai">Sampai tanggal</label>
            <input id="sampai" v-model="f.sampai" type="date" class="input" />
        </div>
        <div v-if="mode !== 'pribadi'">
            <label class="label" for="kelas">Kelas</label>
            <select id="kelas" v-model="f.kelas_id" class="input">
                <option value="">Semua kelas</option>
                <option v-for="k in kelasOptions" :key="k.id" :value="k.id">{{ k.nama }}</option>
            </select>
        </div>
        <div v-if="mode === 'staff'">
            <label class="label" for="q">Nama penyetor</label>
            <input id="q" v-model="f.q" type="search" class="input" placeholder="Cari nama…" />
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <button type="button" class="btn btn-outline" @click="reset">Reset</button>
        </div>
    </form>

    <div class="mt-4">
        <SetoranTable :rows="setoran.data" :mode="mode" @detail="detail = $event" />
    </div>

    <div class="mt-4 flex flex-col items-center gap-2">
        <p class="text-xs text-slate-500">
            Menampilkan {{ setoran.from ?? 0 }}–{{ setoran.to ?? 0 }} dari {{ setoran.total }} transaksi
        </p>
        <Pagination :links="setoran.links" />
    </div>

    <SetoranDetail :setoran="detail" @close="detail = null" />
</template>
