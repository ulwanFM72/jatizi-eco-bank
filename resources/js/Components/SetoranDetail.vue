<script setup>
import Modal from './Modal.vue';
import { fmtGram, fmtNum, fmtTanggal, kategoriLabel } from '../helpers';

defineProps({ setoran: Object }); // null = tertutup
const emit = defineEmits(['close']);

const fmtWaktu = (iso) =>
    iso ? new Date(iso).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : null;
</script>

<template>
    <Modal :show="!!setoran" title="Detail transaksi" max-width="max-w-lg" @close="emit('close')">
        <dl v-if="setoran" class="grid grid-cols-3 gap-x-4 gap-y-3 text-sm">
            <dt class="text-slate-500">No. transaksi</dt>
            <dd class="col-span-2 font-medium">#{{ setoran.id }}</dd>

            <dt class="text-slate-500">Tanggal</dt>
            <dd class="col-span-2">{{ fmtTanggal(setoran.tanggal) }}</dd>

            <dt class="text-slate-500">Penyetor</dt>
            <dd class="col-span-2">{{ setoran.penyetor }}</dd>

            <dt class="text-slate-500">Kelas</dt>
            <dd class="col-span-2">{{ setoran.kelas }}</dd>

            <dt class="text-slate-500">Jenis sampah</dt>
            <dd class="col-span-2">{{ setoran.jenis }} ({{ kategoriLabel[setoran.kategori] }})</dd>

            <dt class="text-slate-500">Berat</dt>
            <dd class="col-span-2">{{ fmtGram(setoran.berat_gram) }} <span class="text-slate-500">({{ fmtNum(setoran.berat_gram) }} g)</span></dd>

            <dt class="text-slate-500">Poin</dt>
            <dd class="col-span-2 font-semibold text-brand-800">{{ fmtNum(setoran.poin) }}</dd>

            <dt class="text-slate-500">Status</dt>
            <dd class="col-span-2">{{ setoran.status === 'dikoreksi' ? 'Dikoreksi admin' : 'Valid (sesuai catatan awal)' }}</dd>

            <template v-if="setoran.petugas">
                <dt class="text-slate-500">Petugas penerima</dt>
                <dd class="col-span-2">{{ setoran.petugas }}</dd>
            </template>

            <template v-if="setoran.catatan">
                <dt class="text-slate-500">Catatan</dt>
                <dd class="col-span-2">{{ setoran.catatan }}</dd>
            </template>

            <template v-if="fmtWaktu(setoran.dicatat_pada)">
                <dt class="text-slate-500">Dicatat pada</dt>
                <dd class="col-span-2">{{ fmtWaktu(setoran.dicatat_pada) }}</dd>
            </template>

            <template v-if="fmtWaktu(setoran.dikoreksi_pada)">
                <dt class="text-slate-500">Dikoreksi pada</dt>
                <dd class="col-span-2">{{ fmtWaktu(setoran.dikoreksi_pada) }}</dd>
            </template>
        </dl>
    </Modal>
</template>
