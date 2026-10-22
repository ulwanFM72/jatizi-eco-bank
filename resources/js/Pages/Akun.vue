<script setup>
import { computed, ref } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import StatCard from "../Components/StatCard.vue";
import SetoranTable from "../Components/SetoranTable.vue";
import SetoranDetail from "../Components/SetoranDetail.vue";
import { fmtGram, fmtNum, peranLabel } from "../helpers";

const props = defineProps({ profil: Object, ringkas: Object, terbaru: Array });

const page = usePage();
const aturan = computed(() => page.props.aturan);
const detail = ref(null);

// Progres menuju poin berikutnya (sisa berat dibandingkan jumlah gram per unit)
const persen = computed(() =>
    Math.min(
        100,
        Math.round(
            (props.ringkas.sisa_gram / aturan.value.gram_per_unit) * 100,
        ),
    ),
);
const kurang = computed(() =>
    Math.max(0, aturan.value.gram_per_unit - props.ringkas.sisa_gram),
);
</script>

<template>
    <Head title="Akun Saya" />

    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">
                Halo, {{ profil.name }}
            </h1>
            <p class="text-sm text-slate-600">
                {{ peranLabel[profil.role] }}
                <template v-if="profil.kelas"> · {{ profil.kelas }}</template>
                <template v-if="profil.nomor_induk">
                    · No. induk {{ profil.nomor_induk }}</template
                >
            </p>
        </div>
        <Link href="/riwayat" class="btn btn-soft btn-sm"
            >Semua riwayat setoran</Link
        >
    </div>

    <section
        class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
        aria-label="Ringkasan pribadi"
    >
        <StatCard
            label="Akumulasi sampah"
            :value="fmtGram(ringkas.total_gram)"
        />
        <StatCard
            label="Nilai setoran (poin)"
            :value="fmtNum(ringkas.total_poin)"
        />
        <StatCard
            label="Peringkat kepedulian"
            :value="ringkas.peringkat ? `#${ringkas.peringkat}` : '-'"
            :hint="
                ringkas.peringkat
                    ? `dari ${fmtNum(ringkas.dari_penyetor)} penyetor`
                    : 'Belum ada setoran'
            "
        />
        <StatCard
            label="Peringkat kelas"
            :value="
                ringkas.peringkat_kelas ? `#${ringkas.peringkat_kelas}` : '-'
            "
            :hint="profil.kelas ?? 'Tanpa kelas'"
        />
    </section>

    <section class="card mt-5 p-5">
        <h2 class="text-lg font-bold text-brand-900">Menuju poin berikutnya</h2>
        <div
            class="bar-track mt-3"
            role="img"
            :aria-label="`${persen}% menuju poin berikutnya`"
        >
            <div class="bar-fill" :style="{ width: `${persen}%` }" />
        </div>
        <p class="mt-2 text-sm text-slate-600">
            Sisa berat tersimpan:
            <strong>{{ fmtNum(ringkas.sisa_gram) }} g</strong>. Setor
            <strong>{{ fmtNum(kurang) }} g</strong> lagi untuk mendapat
            {{ fmtNum(aturan.poin_per_unit) }} poin berikutnya (setiap
            {{ fmtNum(aturan.gram_per_unit) }} g =
            {{ fmtNum(aturan.poin_per_unit) }} poin).
        </p>
    </section>

    <section class="mt-5">
        <h2 class="mb-3 text-lg font-bold text-brand-900">Setoran terbaru</h2>
        <SetoranTable
            :rows="terbaru"
            mode="pribadi"
            @detail="detail = $event"
        />
    </section>

    <SetoranDetail :setoran="detail" @close="detail = null" />
</template>
