<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Modal from '../../../Components/Modal.vue';
import { fmtNum, kategoriClass, kategoriLabel } from '../../../helpers';

defineProps({ jenis: Array });

const tampil = ref(false);
const form = useForm({ id: null, nama: '', kategori: 'organik', keterangan: '', aktif: true });

const tambah = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    tampil.value = true;
};

const ubah = (j) => {
    form.clearErrors();
    Object.assign(form, { id: j.id, nama: j.nama, kategori: j.kategori, keterangan: j.keterangan ?? '', aktif: !!j.aktif });
    tampil.value = true;
};

const simpan = () => {
    const opsi = { preserveScroll: true, onSuccess: () => (tampil.value = false) };
    if (form.id) form.put(`/admin/jenis-sampah/${form.id}`, opsi);
    else form.post('/admin/jenis-sampah', opsi);
};

const hapus = (j) => {
    if (window.confirm(`Hapus jenis sampah "${j.nama}"?`)) router.delete(`/admin/jenis-sampah/${j.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Jenis Sampah" />

    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Manajemen Jenis Sampah</h1>
            <p class="text-sm text-slate-600">Tiga kategori: organik, an-organik, dan B3. Poin dihitung dari berat, bukan dari jenis.</p>
        </div>
        <button type="button" class="btn btn-primary" @click="tambah">+ Tambah jenis</button>
    </div>

    <div class="table-wrap bg-white">
        <table class="min-w-full divide-y divide-brand-100">
            <thead class="bg-brand-100">
                <tr>
                    <th class="th">Nama</th>
                    <th class="th">Kategori</th>
                    <th class="th">Keterangan</th>
                    <th class="th">Status</th>
                    <th class="th text-right">Setoran</th>
                    <th class="th text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                <tr v-for="j in jenis" :key="j.id" class="hover:bg-brand-50/60">
                    <td class="td font-semibold">{{ j.nama }}</td>
                    <td class="td"><span :class="['badge', kategoriClass[j.kategori]]">{{ kategoriLabel[j.kategori] }}</span></td>
                    <td class="td text-slate-600">{{ j.keterangan || '-' }}</td>
                    <td class="td">
                        <span :class="['badge', j.aktif ? 'bg-brand-100 text-brand-900' : 'bg-slate-200 text-slate-700']">
                            {{ j.aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="td text-right tabular-nums">{{ fmtNum(j.setoran_count) }}</td>
                    <td class="td whitespace-nowrap text-right">
                        <button type="button" class="btn btn-outline btn-sm" @click="ubah(j)">Ubah</button>
                        <button type="button" class="btn btn-danger btn-sm ml-1" @click="hapus(j)">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Modal :show="tampil" :title="form.id ? 'Ubah jenis sampah' : 'Tambah jenis sampah'" max-width="max-w-md" @close="tampil = false">
        <form class="space-y-4" @submit.prevent="simpan">
            <div>
                <label class="label" for="j-nama">Nama</label>
                <input id="j-nama" v-model="form.nama" type="text" class="input" maxlength="80" required />
                <p v-if="form.errors.nama" class="field-error">{{ form.errors.nama }}</p>
            </div>
            <div>
                <label class="label" for="j-kat">Kategori</label>
                <select id="j-kat" v-model="form.kategori" class="input">
                    <option v-for="(label, v) in kategoriLabel" :key="v" :value="v">{{ label }}</option>
                </select>
                <p v-if="form.errors.kategori" class="field-error">{{ form.errors.kategori }}</p>
            </div>
            <div>
                <label class="label" for="j-ket">Keterangan</label>
                <input id="j-ket" v-model="form.keterangan" type="text" class="input" maxlength="255" />
                <p v-if="form.errors.keterangan" class="field-error">{{ form.errors.keterangan }}</p>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input v-model="form.aktif" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500" />
                Aktif (tampil di form setoran)
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-outline" @click="tampil = false">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan</button>
            </div>
        </form>
    </Modal>
</template>
