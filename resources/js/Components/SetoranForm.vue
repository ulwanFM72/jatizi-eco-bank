<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { hariIni, kategoriLabel, kunciBaru } from '../helpers';

const props = defineProps({
    kelasOptions: { type: Array, required: true },
    jenisOptions: { type: Array, required: true },
    setoran: { type: Object, default: null }, // null = catat baru; objek = koreksi
});
const emit = defineEmits(['selesai']);

const page = usePage();
const petugas = computed(() => page.props.auth.user?.name);
const edit = computed(() => !!props.setoran);

const awal = () => ({
    penyetor_id: props.setoran?.penyetor_id ?? null,
    nama_penyetor: props.setoran?.nama_penyetor ?? props.setoran?.penyetor ?? '',
    kelas_id: props.setoran?.kelas_id ?? '',
    jenis_sampah_id: props.setoran?.jenis_sampah_id ?? '',
    berat: props.setoran ? String(props.setoran.berat_gram) : '',
    satuan: props.setoran ? 'gram' : 'kg',
    tanggal: props.setoran?.tanggal ?? hariIni(),
    catatan: props.setoran?.catatan ?? '',
    idempotency_key: kunciBaru(),
});

const form = useForm(awal());

// Akun penyetor terpilih (nama dan kelas diambil dari akun, tidak diketik manual)
const terpilih = ref(
    props.setoran?.penyetor_id
        ? { id: props.setoran.penyetor_id, name: props.setoran.penyetor, kelas_id: props.setoran.kelas_id }
        : null,
);
const kelasTerkunci = computed(() => !!terpilih.value?.kelas_id);

// ---- Pencarian akun penyetor (siswa / guru / tendik) ----
const q = ref('');
const hasil = ref([]);
const mencari = ref(false);
let timer;
let kontrol;

watch(q, (v) => {
    clearTimeout(timer);
    kontrol?.abort();
    if (v.trim().length < 2) {
        hasil.value = [];
        return;
    }
    timer = setTimeout(async () => {
        kontrol = new AbortController();
        mencari.value = true;
        try {
            const r = await fetch(`/admin/penyetor/cari?q=${encodeURIComponent(v.trim())}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: kontrol.signal,
            });
            if (r.ok) hasil.value = await r.json();
        } catch (e) {
            if (e.name !== 'AbortError') hasil.value = [];
        } finally {
            mencari.value = false;
        }
    }, 250);
});
onUnmounted(() => {
    clearTimeout(timer);
    kontrol?.abort();
});

const pilih = (u) => {
    terpilih.value = u;
    form.penyetor_id = u.id;
    form.nama_penyetor = u.name;
    if (u.kelas_id) form.kelas_id = u.kelas_id;
    q.value = '';
    hasil.value = [];
};

const lepas = () => {
    terpilih.value = null;
    form.penyetor_id = null;
    form.nama_penyetor = '';
};

const kirim = () => {
    const opsi = {
        preserveScroll: true,
        onSuccess: () => {
            if (edit.value) {
                emit('selesai');
                return;
            }
            // Siap untuk setoran berikutnya; kunci baru supaya setoran berikutnya tidak dianggap kirim ulang.
            form.reset('berat', 'catatan');
            lepas();
            form.kelas_id = '';
            form.idempotency_key = kunciBaru();
            form.clearErrors();
        },
    };
    if (edit.value) form.put(`/admin/setoran/${props.setoran.id}`, opsi);
    else form.post('/admin/setoran', opsi);
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="kirim">
        <!-- Penyetor -->
        <div>
            <label class="label" for="cari-penyetor">Penyetor (akun)</label>
            <div v-if="terpilih" class="flex items-center justify-between rounded-xl border border-brand-300 bg-brand-50 px-3 py-2 text-sm">
                <span>
                    <strong>{{ terpilih.name }}</strong>
                    <span v-if="terpilih.kelas" class="text-slate-600"> · {{ terpilih.kelas }}</span>
                </span>
                <button type="button" class="text-xs font-semibold text-red-700 hover:underline" @click="lepas">Lepas</button>
            </div>
            <div v-else class="relative">
                <input
                    id="cari-penyetor"
                    v-model="q"
                    type="search"
                    class="input"
                    placeholder="Cari nama atau nomor induk (opsional)…"
                    autocomplete="off"
                />
                <ul
                    v-if="hasil.length"
                    class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-xl border border-brand-200 bg-white shadow-lg"
                >
                    <li v-for="u in hasil" :key="u.id">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-brand-50"
                            @click="pilih(u)"
                        >
                            <span>{{ u.name }}</span>
                            <span class="text-xs text-slate-500">{{ u.kelas ?? u.role }}</span>
                        </button>
                    </li>
                </ul>
                <p v-else-if="mencari" class="mt-1 text-xs text-slate-500">Mencari…</p>
            </div>
            <p v-if="form.errors.penyetor_id" class="field-error">{{ form.errors.penyetor_id }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="nama_penyetor">Nama penyetor</label>
                <input
                    id="nama_penyetor"
                    v-model="form.nama_penyetor"
                    type="text"
                    class="input"
                    :disabled="!!terpilih"
                    placeholder="Diisi otomatis bila akun dipilih"
                    maxlength="100"
                />
                <p v-if="form.errors.nama_penyetor" class="field-error">{{ form.errors.nama_penyetor }}</p>
            </div>

            <div>
                <label class="label" for="kelas_id">Kelas</label>
                <select id="kelas_id" v-model="form.kelas_id" class="input" :disabled="kelasTerkunci">
                    <option value="" disabled>Pilih kelas…</option>
                    <option v-for="k in kelasOptions" :key="k.id" :value="k.id">{{ k.nama }}</option>
                </select>
                <p v-if="kelasTerkunci" class="mt-1 text-xs text-slate-500">Mengikuti kelas pada akun penyetor.</p>
                <p v-if="form.errors.kelas_id" class="field-error">{{ form.errors.kelas_id }}</p>
            </div>
        </div>

        <div>
            <span class="label">Jenis sampah</span>
            <div class="grid gap-2 sm:grid-cols-3">
                <label
                    v-for="j in jenisOptions"
                    :key="j.id"
                    :class="[
                        'flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-sm transition',
                        form.jenis_sampah_id === j.id ? 'border-brand-700 bg-brand-100' : 'border-slate-300 bg-white hover:bg-brand-50',
                    ]"
                >
                    <input v-model="form.jenis_sampah_id" type="radio" name="jenis_sampah_id" :value="j.id" class="text-brand-700 focus:ring-brand-500" />
                    <span>
                        {{ j.nama }}
                        <span class="block text-[11px] text-slate-500">{{ kategoriLabel[j.kategori] }}</span>
                    </span>
                </label>
            </div>
            <p v-if="form.errors.jenis_sampah_id" class="field-error">{{ form.errors.jenis_sampah_id }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="label" for="berat">Berat</label>
                <div class="flex gap-2">
                    <input
                        id="berat"
                        v-model="form.berat"
                        type="number"
                        inputmode="decimal"
                        :step="form.satuan === 'kg' ? '0.001' : '1'"
                        min="0"
                        class="input"
                        placeholder="0"
                        required
                    />
                    <select v-model="form.satuan" class="input w-24" aria-label="Satuan berat">
                        <option value="kg">kg</option>
                        <option value="gram">gram</option>
                    </select>
                </div>
                <p v-if="form.errors.berat" class="field-error">{{ form.errors.berat }}</p>
                <p v-if="form.errors.satuan" class="field-error">{{ form.errors.satuan }}</p>
            </div>

            <div>
                <label class="label" for="tanggal">Tanggal setoran</label>
                <input id="tanggal" v-model="form.tanggal" type="date" :max="hariIni()" class="input" required />
                <p v-if="form.errors.tanggal" class="field-error">{{ form.errors.tanggal }}</p>
            </div>

            <div>
                <label class="label" for="petugas">Petugas penerima</label>
                <input id="petugas" type="text" class="input" :value="setoran?.petugas ?? petugas" disabled />
            </div>
        </div>

        <div>
            <label class="label" for="catatan">Catatan (opsional)</label>
            <input id="catatan" v-model="form.catatan" type="text" class="input" maxlength="255" />
            <p v-if="form.errors.catatan" class="field-error">{{ form.errors.catatan }}</p>
        </div>

        <p class="rounded-xl bg-brand-50 px-3 py-2 text-xs text-slate-600">
            Poin dan sisa berat dihitung otomatis oleh server setelah disimpan.
        </p>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan…' : edit ? 'Simpan koreksi' : 'Simpan setoran' }}
            </button>
        </div>
    </form>
</template>
