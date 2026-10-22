<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { fmtNum } from '../../helpers';

const props = defineProps({ aturan: Object });

const form = useForm({
    gram_per_unit: props.aturan.gram_per_unit,
    poin_per_unit: props.aturan.poin_per_unit,
});

const simpan = () => form.put('/admin/pengaturan-poin', { preserveScroll: true });
</script>

<template>
    <Head title="Pengaturan Poin" />

    <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Pengaturan Poin</h1>

    <section class="card max-w-2xl p-5">
        <p class="text-sm text-slate-600">
            Konversi berat menjadi poin. Setiap akumulasi <strong>{{ fmtNum(form.gram_per_unit) }} gram</strong> mendapat
            <strong>{{ fmtNum(form.poin_per_unit) }} poin</strong>. Sisa berat di bawah angka itu tetap disimpan dan digabung dengan
            setoran berikutnya.
        </p>

        <form class="mt-5 grid gap-4 sm:grid-cols-2" @submit.prevent="simpan">
            <div>
                <label class="label" for="gram">Berat per unit (gram)</label>
                <input id="gram" v-model.number="form.gram_per_unit" type="number" min="1" max="100000" step="1" class="input" required />
                <p v-if="form.errors.gram_per_unit" class="field-error">{{ form.errors.gram_per_unit }}</p>
            </div>
            <div>
                <label class="label" for="poin">Poin per unit</label>
                <input id="poin" v-model.number="form.poin_per_unit" type="number" min="0" max="1000" step="1" class="input" required />
                <p v-if="form.errors.poin_per_unit" class="field-error">{{ form.errors.poin_per_unit }}</p>
            </div>

            <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900 sm:col-span-2">
                Aturan baru hanya berlaku untuk setoran yang dicatat sesudah disimpan. Setoran lama tetap memakai aturan saat
                dicatat, sehingga poin yang sudah dibagikan tidak berubah diam-diam.
            </p>

            <div class="flex justify-end sm:col-span-2">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan aturan</button>
            </div>
        </form>
    </section>
</template>
