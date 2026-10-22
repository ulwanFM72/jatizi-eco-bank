<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FlashBanner from '../Components/FlashBanner.vue';
import { peranLabel } from '../helpers';

const page = usePage();
const user = computed(() => page.props.auth.user);
const menuBuka = ref(false);

const semuaMenu = [
    { href: '/admin', label: 'Dashboard', ikon: '▦', exact: true },
    { href: '/admin/setoran', label: 'Kelola Setoran', ikon: '⚖' },
    { href: '/admin/kelas', label: 'Kelas', ikon: '🏫', adminOnly: true },
    { href: '/admin/jenis-sampah', label: 'Jenis Sampah', ikon: '♻', adminOnly: true },
    { href: '/admin/pengguna', label: 'Pengguna', ikon: '👥', adminOnly: true },
    { href: '/admin/pengaturan-poin', label: 'Pengaturan Poin', ikon: '★', adminOnly: true },
    { href: '/admin/laporan', label: 'Laporan', ikon: '🗎', adminOnly: true },
];

const menu = computed(() => semuaMenu.filter((m) => !m.adminOnly || user.value?.is_admin));

const aktif = (m) => {
    const path = page.url.split('?')[0];
    return m.exact ? path === m.href : path.startsWith(m.href);
};

const keluar = () => router.post('/keluar');
</script>

<template>
    <div class="min-h-screen lg:flex">
        <!-- Sidebar -->
        <aside
            :class="[
                'bg-brand-200 lg:sticky lg:top-0 lg:block lg:h-screen lg:w-64 lg:shrink-0 lg:overflow-y-auto lg:border-r lg:border-brand-300',
                menuBuka ? 'block' : 'hidden',
            ]"
        >
            <div class="hidden items-center gap-2 px-5 py-5 lg:flex">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-700 text-xl text-white" aria-hidden="true">♻</span>
                <span class="leading-tight">
                    <span class="block text-base font-extrabold tracking-tight text-brand-900">JATIZI ECO-BANK</span>
                    <span class="block text-[11px] font-medium text-brand-800">Panel pengelola</span>
                </span>
            </div>

            <nav class="flex flex-col gap-1 px-3 pb-4 pt-2" aria-label="Menu admin">
                <Link
                    v-for="m in menu"
                    :key="m.href"
                    :href="m.href"
                    :class="[
                        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                        aktif(m) ? 'bg-brand-700 text-white shadow-sm' : 'text-brand-900 hover:bg-brand-100',
                    ]"
                    @click="menuBuka = false"
                >
                    <span class="w-5 text-center" aria-hidden="true">{{ m.ikon }}</span>
                    {{ m.label }}
                </Link>

                <hr class="my-2 border-brand-300" />

                <Link href="/" class="rounded-xl px-3 py-2 text-sm font-medium text-brand-900 hover:bg-brand-100">Lihat situs publik</Link>
                <button
                    type="button"
                    class="rounded-xl px-3 py-2 text-left text-sm font-medium text-brand-900 hover:bg-brand-100"
                    @click="keluar"
                >
                    Keluar
                </button>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-brand-200 bg-white/90 px-4 py-3 backdrop-blur">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-brand-900 hover:bg-brand-100 lg:hidden"
                        aria-label="Buka menu"
                        :aria-expanded="menuBuka"
                        @click="menuBuka = !menuBuka"
                    >
                        ☰
                    </button>
                    <span class="text-sm font-bold text-brand-900 lg:hidden">JATIZI ECO-BANK</span>
                </div>
                <div v-if="user" class="text-right text-sm">
                    <p class="font-semibold text-slate-800">{{ user.name }}</p>
                    <p class="text-xs text-slate-500">{{ peranLabel[user.role] }}</p>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl space-y-4 px-4 py-6">
                <FlashBanner />
                <slot />
            </main>
        </div>
    </div>
</template>
