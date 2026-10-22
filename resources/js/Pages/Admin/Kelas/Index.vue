<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Modal from '../../../Components/Modal.vue';
import { fmtGram, fmtNum } from '../../../helpers';

defineProps({ kelas: Array });

const tipeLabel = { kelas: 'Kelas', guru: 'Guru', tendik: 'Tenaga administrasi' };

const tampil = ref(false);
const form = useForm({ id: null, nama: '', tipe: 'kelas' });

const tambah = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    tampil.value = true;
};

const ubah = (k) => {
    form.clearErrors();
    form.id = k.id;
    form.nama = k.nama;
    form.tipe = k.tipe;
    tampil.value = true;
};

const simpan = () => {
    const opsi = { preserveScroll: true, onSuccess: () => (tampil.value = false) };
    if (form.id) form.put(`/admin/kelas/${form.id}`, opsi);
    else form.post('/admin/kelas', opsi);
};

const hapus = (k) => {
    if (window.confirm(`Hapus ${k.nama}?`)) router.delete(`/admin/kelas/${k.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Kelola Kelas" />

    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Manajemen Kelas</h1>
            <p class="text-sm text-slate-600">
                Tipe <strong>Guru</strong> dan <strong>Tenaga administrasi</strong> menampung setoran guru dan tendik, dan tidak ikut peringkat kelas.
            </p>
        </div>
        <button type="button" class="btn btn-primary" @click="tambah">+ Tambah kelas</button>
    </div>

    <div class="table-wrap bg-white">
        <table class="min-w-full divide-y divide-brand-100">
            <thead class="bg-brand-100">
                <tr>
                    <th class="th">Nama</th>
                    <th class="th">Tipe</th>
                    <th class="th text-right">Anggota</th>
                    <th class="th text-right">Setoran</th>
                    <th class="th text-right">Total berat</th>
                    <th class="th text-right">Poin</th>
                    <th class="th text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                <tr v-if="!kelas.length">
                    <td colspan="7" class="td py-10 text-center text-slate-500">Belum ada kelas.</td>
                </tr>
                <tr v-for="k in kelas" :key="k.id" class="hover:bg-brand-50/60">
                    <td class="td font-semibold">{{ k.nama }}</td>
                    <td class="td"><span class="badge bg-brand-100 text-brand-900">{{ tipeLabel[k.tipe] }}</span></td>
                    <td class="td text-right tabular-nums">{{ fmtNum(k.users_count) }}</td>
                    <td class="td text-right tabular-nums">{{ fmtNum(k.setoran_count) }}</td>
                    <td class="td text-right tabular-nums">{{ fmtGram(k.total_gram) }}</td>
                    <td class="td text-right font-semibold tabular-nums text-brand-800">{{ fmtNum(k.total_poin) }}</td>
                    <td class="td whitespace-nowrap text-right">
                        <button type="button" class="btn btn-outline btn-sm" @click="ubah(k)">Ubah</button>
                        <button type="button" class="btn btn-danger btn-sm ml-1" @click="hapus(k)">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Modal :show="tampil" :title="form.id ? 'Ubah kelas' : 'Tambah kelas'" max-width="max-w-md" @close="tampil = false">
        <form class="space-y-4" @submit.prevent="simpan">
            <div>
                <label class="label" for="k-nama">Nama kelas</label>
                <input id="k-nama" v-model="form.nama" type="text" class="input" maxlength="50" required placeholder="Contoh: X PPLG 1" />
                <p v-if="form.errors.nama" class="field-error">{{ form.errors.nama }}</p>
            </div>
            <div>
                <label class="label" for="k-tipe">Tipe</label>
                <select id="k-tipe" v-model="form.tipe" class="input">
                    <option v-for="(label, v) in tipeLabel" :key="v" :value="v">{{ label }}</option>
                </select>
                <p v-if="form.errors.tipe" class="field-error">{{ form.errors.tipe }}</p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-outline" @click="tampil = false">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan</button>
            </div>
        </form>
    </Modal>
</template>
