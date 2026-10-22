<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import StatCard from '../Components/StatCard.vue';
import { fmtGram, fmtNum, kategoriClass, kategoriLabel } from '../helpers';

const props = defineProps({
    ringkasan: Object,
    kategori: Array,
    teratas: Array,
});

const page = usePage();
const aturan = computed(() => page.props.aturan);
const user = computed(() => page.props.auth.user);

const maksKategori = computed(() => Math.max(1, ...props.kategori.map((k) => k.gram)));
const medali = ['🥇', '🥈', '🥉'];
</script>

<template>
    <Head title="Beranda" />

    <section class="hero rounded-3xl px-6 py-10 sm:px-10">
        <p class="text-sm font-semibold uppercase tracking-widest text-brand-800">Bank Sampah Digital</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-brand-900 sm:text-4xl">JATIZI ECO-BANK</h1>
        <p class="mt-3 max-w-2xl text-base text-brand-900/90">
            Catat setoran sampah siswa, guru, dan tenaga administrasi SMK Negeri 1 Cijati secara tertib dan transparan.
            Setiap kilogram sampah yang terkumpul menjadi poin kepedulian lingkungan untuk kelasmu.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            <Link href="/peringkat" class="btn btn-primary">Lihat peringkat kelas</Link>
            <Link v-if="!user" href="/masuk" class="btn btn-outline">Masuk</Link>
            <Link v-else :href="user.is_staff ? '/admin' : '/akun'" class="btn btn-outline">
                {{ user.is_staff ? 'Buka panel admin' : 'Lihat poin saya' }}
            </Link>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-3" aria-label="Ringkasan">
        <StatCard label="Total sampah terkumpul" :value="fmtGram(ringkasan.total_gram)" hint="Seluruh kategori" />
        <StatCard label="Total poin diperoleh" :value="fmtNum(ringkasan.total_poin)" hint="Dihitung otomatis dari berat" />
        <StatCard label="Jumlah transaksi setoran" :value="fmtNum(ringkasan.jumlah_transaksi)" hint="Tercatat di sistem" />
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-5">
        <section class="card p-5 lg:col-span-3">
            <h2 class="text-lg font-bold text-brand-900">Sampah menurut kategori</h2>
            <ul class="mt-4 space-y-4">
                <li v-for="k in kategori" :key="k.kategori">
                    <div class="flex items-center justify-between text-sm">
                        <span :class="['badge', kategoriClass[k.kategori]]">{{ kategoriLabel[k.kategori] }}</span>
                        <span class="font-semibold tabular-nums text-slate-700">
                            {{ fmtGram(k.gram) }} · {{ fmtNum(k.jumlah) }} setoran
                        </span>
                    </div>
                    <div class="bar-track mt-1.5" role="img" :aria-label="`${kategoriLabel[k.kategori]}: ${fmtGram(k.gram)}`">
                        <div class="bar-fill" :style="{ width: `${(k.gram / maksKategori) * 100}%` }" />
                    </div>
                </li>
            </ul>
        </section>

        <section class="card p-5 lg:col-span-2">
            <h2 class="text-lg font-bold text-brand-900">Kelas terdepan</h2>
            <ol v-if="teratas.length" class="mt-4 space-y-3">
                <li
                    v-for="(k, i) in teratas"
                    :key="k.id"
                    class="flex items-center justify-between rounded-xl bg-brand-50 px-4 py-3"
                >
                    <span class="flex items-center gap-3">
                        <span class="text-2xl" aria-hidden="true">{{ medali[i] }}</span>
                        <span>
                            <span class="block font-semibold text-slate-800">{{ k.nama }}</span>
                            <span class="block text-xs text-slate-500">Peringkat {{ k.rank }} · {{ fmtGram(k.total_gram) }}</span>
                        </span>
                    </span>
                    <span class="text-lg font-bold tabular-nums text-brand-800">{{ fmtNum(k.total_poin) }} <span class="text-xs font-medium">poin</span></span>
                </li>
            </ol>
            <p v-else class="mt-4 text-sm text-slate-500">Belum ada kelas terdaftar.</p>
            <Link href="/peringkat" class="mt-4 inline-block text-sm font-semibold text-brand-700 hover:underline">Peringkat lengkap →</Link>
        </section>
    </div>

    <section class="card mt-6 p-5">
        <h2 class="text-lg font-bold text-brand-900">Cara kerja program</h2>
        <ol class="mt-4 grid gap-4 sm:grid-cols-3">
            <li class="rounded-xl bg-brand-50 p-4">
                <p class="text-2xl" aria-hidden="true">🧺</p>
                <p class="mt-1 font-semibold text-brand-900">1. Setor sampah</p>
                <p class="mt-1 text-sm text-slate-600">Bawa sampah organik, an-organik, atau B3 ke petugas bank sampah.</p>
            </li>
            <li class="rounded-xl bg-brand-50 p-4">
                <p class="text-2xl" aria-hidden="true">⚖️</p>
                <p class="mt-1 font-semibold text-brand-900">2. Ditimbang &amp; dicatat</p>
                <p class="mt-1 text-sm text-slate-600">Petugas menimbang dan mencatat setoran ke sistem.</p>
            </li>
            <li class="rounded-xl bg-brand-50 p-4">
                <p class="text-2xl" aria-hidden="true">⭐</p>
                <p class="mt-1 font-semibold text-brand-900">3. Dapat poin</p>
                <p class="mt-1 text-sm text-slate-600">
                    Setiap akumulasi {{ fmtNum(aturan.gram_per_unit) }} gram = {{ fmtNum(aturan.poin_per_unit) }} poin.
                    Sisa berat tidak hilang: digabung dengan setoran berikutnya.
                </p>
            </li>
        </ol>
    </section>
</template>
