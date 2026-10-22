<script setup>
import { reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Modal from '../../../Components/Modal.vue';
import Pagination from '../../../Components/Pagination.vue';
import SetoranDetail from '../../../Components/SetoranDetail.vue';
import SetoranForm from '../../../Components/SetoranForm.vue';
import SetoranTable from '../../../Components/SetoranTable.vue';
import { fmtGram, fmtTanggal } from '../../../helpers';

const props = defineProps({
    setoran: Object,
    filters: Object,
    kelasOptions: Array,
    jenisOptions: Array,
    bisaKoreksi: Boolean,
});

const f = reactive({ ...props.filters });
const detail = ref(null);
const diedit = ref(null);

const cari = () =>
    router.get('/admin/setoran', Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '' && v !== null)), {
        preserveState: true,
        preserveScroll: true,
    });

const reset = () => {
    Object.assign(f, { dari: '', sampai: '', kelas_id: '', q: '' });
    cari();
};

const hapus = (s) => {
    const ok = window.confirm(
        `Hapus setoran #${s.id} (${s.penyetor}, ${fmtGram(s.berat_gram)}, ${fmtTanggal(s.tanggal)})?\n\nPoin dan sisa berat akan dihitung ulang otomatis.`,
    );
    if (ok) router.delete(`/admin/setoran/${s.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Kelola Setoran" />

    <h1 class="text-2xl font-extrabold tracking-tight text-brand-900">Kelola Setoran</h1>

    <section class="card p-5">
        <h2 class="mb-4 text-lg font-bold text-brand-900">Catat setoran baru</h2>
        <SetoranForm :kelas-options="kelasOptions" :jenis-options="jenisOptions" />
    </section>

    <section>
        <h2 class="mb-3 text-lg font-bold text-brand-900">Riwayat setoran</h2>

        <form class="card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="cari">
            <div>
                <label class="label" for="f-dari">Dari tanggal</label>
                <input id="f-dari" v-model="f.dari" type="date" class="input" />
            </div>
            <div>
                <label class="label" for="f-sampai">Sampai tanggal</label>
                <input id="f-sampai" v-model="f.sampai" type="date" class="input" />
            </div>
            <div>
                <label class="label" for="f-kelas">Kelas</label>
                <select id="f-kelas" v-model="f.kelas_id" class="input">
                    <option value="">Semua kelas</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id">{{ k.nama }}</option>
                </select>
            </div>
            <div>
                <label class="label" for="f-q">Nama penyetor</label>
                <input id="f-q" v-model="f.q" type="search" class="input" placeholder="Cari nama…" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <button type="button" class="btn btn-outline" @click="reset">Reset</button>
            </div>
        </form>

        <div class="mt-4">
            <SetoranTable
                :rows="setoran.data"
                mode="staff"
                :can-edit="bisaKoreksi"
                @detail="detail = $event"
                @edit="diedit = $event"
                @delete="hapus"
            />
        </div>

        <div class="mt-4 flex flex-col items-center gap-2">
            <p class="text-xs text-slate-500">
                Menampilkan {{ setoran.from ?? 0 }}–{{ setoran.to ?? 0 }} dari {{ setoran.total }} transaksi
            </p>
            <Pagination :links="setoran.links" />
        </div>
    </section>

    <SetoranDetail :setoran="detail" @close="detail = null" />

    <Modal :show="!!diedit" :title="`Koreksi setoran #${diedit?.id}`" max-width="max-w-2xl" @close="diedit = null">
        <SetoranForm
            v-if="diedit"
            :key="diedit.id"
            :kelas-options="kelasOptions"
            :jenis-options="jenisOptions"
            :setoran="diedit"
            @selesai="diedit = null"
        />
    </Modal>
</template>
