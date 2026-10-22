<script setup>
import {
    fmtGram,
    fmtNum,
    fmtTanggal,
    kategoriClass,
    kategoriLabel,
} from "../helpers";

defineProps({
    rows: { type: Array, required: true },
    mode: { type: String, default: "publik" }, // publik | pribadi | staff
    canEdit: { type: Boolean, default: false },
});
const emit = defineEmits(["detail", "edit", "delete"]);
</script>

<template>
    <div class="table-wrap bg-white">
        <table class="min-w-full divide-y divide-brand-100">
            <thead class="bg-brand-100">
                <tr>
                    <th class="th">Tanggal</th>
                    <th class="th">Penyetor</th>
                    <!-- <th class="th">Kelas</th>  -->
                    <th class="th">Jenis sampah</th>
                    <th class="th text-right">Berat</th>
                    <th class="th text-right">Poin</th>
                    <th class="th">Status</th>
                    <th class="th text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                <tr v-if="!rows.length">
                    <td colspan="8" class="td py-10 text-center text-slate-500">
                        Belum ada setoran yang cocok.
                    </td>
                </tr>
                <tr v-for="s in rows" :key="s.id" class="hover:bg-brand-50/60">
                    <td class="td whitespace-nowrap">
                        {{ fmtTanggal(s.tanggal) }}
                    </td>
                    <td class="td">{{ s.penyetor }}</td>
                    <td class="td whitespace-nowrap">{{ s.kelas }}</td>
                    <td class="td">
                        <span class="mr-1">{{ s.jenis }}</span>
                        <span :class="['badge', kategoriClass[s.kategori]]">{{
                            kategoriLabel[s.kategori]
                        }}</span>
                    </td>
                    <td class="td whitespace-nowrap text-right tabular-nums">
                        {{ fmtGram(s.berat_gram) }}
                    </td>
                    <td
                        class="td text-right font-semibold tabular-nums text-brand-800"
                    >
                        {{ fmtNum(s.poin) }}
                    </td>
                    <td class="td">
                        <span
                            :class="[
                                'badge',
                                s.status === 'dikoreksi'
                                    ? 'bg-amber-100 text-amber-900'
                                    : 'bg-brand-100 text-brand-900',
                            ]"
                        >
                            {{
                                s.status === "dikoreksi" ? "Dikoreksi" : "Valid"
                            }}
                        </span>
                    </td>
                    <td class="td whitespace-nowrap text-right">
                        <button
                            type="button"
                            class="btn btn-soft btn-sm"
                            @click="emit('detail', s)"
                        >
                            Detail
                        </button>
                        <template v-if="canEdit">
                            <button
                                type="button"
                                class="btn btn-outline btn-sm ml-1"
                                @click="emit('edit', s)"
                            >
                                Koreksi
                            </button>
                            <button
                                type="button"
                                class="btn btn-danger btn-sm ml-1"
                                @click="emit('delete', s)"
                            >
                                Hapus
                            </button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
