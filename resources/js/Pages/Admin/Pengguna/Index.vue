<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Modal from '../../../Components/Modal.vue';
import Pagination from '../../../Components/Pagination.vue';
import { fmtNum, peranLabel } from '../../../helpers';

const props = defineProps({
    pengguna: Object,
    filters: Object,
    kelasOptions: Array,
    roles: Array,
});

const f = reactive({ ...props.filters });
const cari = () =>
    router.get('/admin/pengguna', Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '' && v !== null)), {
        preserveState: true,
        preserveScroll: true,
    });
const reset = () => {
    Object.assign(f, { q: '', role: '' });
    cari();
};

const tampil = ref(false);
const form = useForm({
    id: null,
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'siswa',
    nomor_induk: '',
    kelas_id: '',
    is_active: true,
});

const sunting = computed(() => form.id !== null);

const tambah = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    tampil.value = true;
};

const ubah = (u) => {
    form.clearErrors();
    Object.assign(form, {
        id: u.id,
        name: u.name,
        username: u.username ?? '',
        email: u.email?.endsWith('@jatizi.local') ? '' : (u.email ?? ''),
        password: '',
        role: u.role,
        nomor_induk: u.nomor_induk ?? '',
        kelas_id: u.kelas_id ?? '',
        is_active: !!u.is_active,
    });
    tampil.value = true;
};

const simpan = () => {
    const opsi = { preserveScroll: true, onSuccess: () => (tampil.value = false) };
    if (sunting.value) form.put(`/admin/pengguna/${form.id}`, opsi);
    else form.post('/admin/pengguna', opsi);
};

const hapus = (u) => {
    if (window.confirm(`Hapus akun ${u.name}?`)) router.delete(`/admin/pengguna/${u.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Pengguna" />

    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-600">Atur akun dan hak akses: admin, petugas, siswa, guru, dan tenaga administrasi.</p>
        </div>
        <button type="button" class="btn btn-primary" @click="tambah">+ Tambah pengguna</button>
    </div>

    <form class="card grid gap-3 p-4 sm:grid-cols-3" @submit.prevent="cari">
        <div>
            <label class="label" for="u-q">Cari</label>
            <input id="u-q" v-model="f.q" type="search" class="input" placeholder="Nama, username, atau nomor induk" />
        </div>
        <div>
            <label class="label" for="u-role">Peran</label>
            <select id="u-role" v-model="f.role" class="input">
                <option value="">Semua peran</option>
                <option v-for="r in roles" :key="r" :value="r">{{ peranLabel[r] }}</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <button type="button" class="btn btn-outline" @click="reset">Reset</button>
        </div>
    </form>

    <div class="table-wrap bg-white">
        <table class="min-w-full divide-y divide-brand-100">
            <thead class="bg-brand-100">
                <tr>
                    <th class="th">Nama</th>
                    <th class="th">Username</th>
                    <th class="th">Peran</th>
                    <th class="th">Kelas</th>
                    <th class="th text-right">Poin</th>
                    <th class="th">Status</th>
                    <th class="th text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                <tr v-if="!pengguna.data.length">
                    <td colspan="7" class="td py-10 text-center text-slate-500">Tidak ada pengguna.</td>
                </tr>
                <tr v-for="u in pengguna.data" :key="u.id" class="hover:bg-brand-50/60">
                    <td class="td">
                        <span class="font-semibold">{{ u.name }}</span>
                        <span v-if="u.nomor_induk" class="block text-xs text-slate-500">{{ u.nomor_induk }}</span>
                    </td>
                    <td class="td">{{ u.username }}</td>
                    <td class="td"><span class="badge bg-brand-100 text-brand-900">{{ peranLabel[u.role] }}</span></td>
                    <td class="td">{{ u.kelas ?? '-' }}</td>
                    <td class="td text-right tabular-nums">{{ fmtNum(u.total_poin) }}</td>
                    <td class="td">
                        <span :class="['badge', u.is_active ? 'bg-brand-100 text-brand-900' : 'bg-slate-200 text-slate-700']">
                            {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="td whitespace-nowrap text-right">
                        <button type="button" class="btn btn-outline btn-sm" @click="ubah(u)">Ubah</button>
                        <button type="button" class="btn btn-danger btn-sm ml-1" @click="hapus(u)">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col items-center gap-2">
        <p class="text-xs text-slate-500">
            Menampilkan {{ pengguna.from ?? 0 }}–{{ pengguna.to ?? 0 }} dari {{ pengguna.total }} pengguna
        </p>
        <Pagination :links="pengguna.links" />
    </div>

    <Modal :show="tampil" :title="sunting ? 'Ubah pengguna' : 'Tambah pengguna'" max-width="max-w-2xl" @close="tampil = false">
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="simpan">
            <div>
                <label class="label" for="p-name">Nama lengkap</label>
                <input id="p-name" v-model="form.name" type="text" class="input" maxlength="100" required />
                <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="label" for="p-username">Username</label>
                <input id="p-username" v-model="form.username" type="text" class="input" maxlength="50" autocomplete="off" required />
                <p v-if="form.errors.username" class="field-error">{{ form.errors.username }}</p>
            </div>
            <div>
                <label class="label" for="p-role">Peran</label>
                <select id="p-role" v-model="form.role" class="input">
                    <option v-for="r in roles" :key="r" :value="r">{{ peranLabel[r] }}</option>
                </select>
                <p v-if="form.errors.role" class="field-error">{{ form.errors.role }}</p>
            </div>
            <div>
                <label class="label" for="p-kelas">Kelas / kelompok</label>
                <select id="p-kelas" v-model="form.kelas_id" class="input">
                    <option value="">{{ form.role === 'siswa' ? 'Pilih kelas…' : 'Tanpa kelas' }}</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id">{{ k.nama }}</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Guru &amp; tendik: pilih kelompok "Guru" atau "Tenaga Administrasi".</p>
                <p v-if="form.errors.kelas_id" class="field-error">{{ form.errors.kelas_id }}</p>
            </div>
            <div>
                <label class="label" for="p-induk">Nomor induk (NIS / NIP)</label>
                <input id="p-induk" v-model="form.nomor_induk" type="text" class="input" maxlength="30" />
                <p v-if="form.errors.nomor_induk" class="field-error">{{ form.errors.nomor_induk }}</p>
            </div>
            <div>
                <label class="label" for="p-email">Email (opsional)</label>
                <input id="p-email" v-model="form.email" type="email" class="input" maxlength="150" />
                <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="p-pass">Kata sandi {{ sunting ? '(kosongkan bila tidak diubah)' : '' }}</label>
                <input
                    id="p-pass"
                    v-model="form.password"
                    type="password"
                    class="input"
                    autocomplete="new-password"
                    minlength="8"
                    :required="!sunting"
                />
                <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500" />
                Akun aktif (dapat masuk)
            </label>
            <div class="flex justify-end gap-2 sm:col-span-2">
                <button type="button" class="btn btn-outline" @click="tampil = false">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan</button>
            </div>
        </form>
    </Modal>
</template>
