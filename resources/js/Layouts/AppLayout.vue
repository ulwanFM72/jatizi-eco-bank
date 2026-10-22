<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FlashBanner from '../Components/FlashBanner.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const menuBuka = ref(false);

const menu = [
    { href: '/', label: 'Beranda' },
    { href: '/peringkat', label: 'Peringkat Kelas' },
    { href: '/riwayat', label: 'Riwayat Setoran' },
];

const aktif = (href) => {
    const path = page.url.split('?')[0];
    return href === '/' ? path === '/' : path.startsWith(href);
};

const keluar = () => router.post('/keluar');
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-40 border-b border-brand-300 bg-brand-200/95 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                <Link href="/" class="flex items-center gap-2 text-brand-900">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-700 text-lg text-white" aria-hidden="true">♻</span>
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold tracking-tight">JATIZI ECO-BANK</span>
                        <span class="block text-[11px] font-medium text-brand-800">SMK Negeri 1 Cijati</span>
                    </span>
                </Link>

                <nav class="hidden items-center gap-1 md:flex" aria-label="Menu utama">
                    <Link
                        v-for="m in menu"
                        :key="m.href"
                        :href="m.href"
                        :class="[
                            'rounded-lg px-3 py-2 text-sm font-semibold transition',
                            aktif(m.href) ? 'bg-brand-700 text-white' : 'text-brand-900 hover:bg-brand-100',
                        ]"
                    >
                        {{ m.label }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    <template v-if="user">
                        <Link :href="user.is_staff ? '/admin' : '/akun'" class="btn btn-soft btn-sm">
                            {{ user.is_staff ? 'Panel Admin' : 'Akun Saya' }}
                        </Link>
                        <button type="button" class="btn btn-outline btn-sm" @click="keluar">Keluar</button>
                    </template>
                    <Link v-else href="/masuk" class="btn btn-primary btn-sm">Masuk</Link>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 text-brand-900 hover:bg-brand-100 md:hidden"
                    aria-label="Buka menu"
                    :aria-expanded="menuBuka"
                    @click="menuBuka = !menuBuka"
                >
                    ☰
                </button>
            </div>

            <div v-if="menuBuka" class="border-t border-brand-300 bg-brand-100 px-4 py-3 md:hidden">
                <nav class="flex flex-col gap-1" aria-label="Menu seluler">
                    <Link
                        v-for="m in menu"
                        :key="m.href"
                        :href="m.href"
                        :class="[
                            'rounded-lg px-3 py-2 text-sm font-semibold',
                            aktif(m.href) ? 'bg-brand-700 text-white' : 'text-brand-900',
                        ]"
                        @click="menuBuka = false"
                    >
                        {{ m.label }}
                    </Link>
                    <template v-if="user">
                        <Link :href="user.is_staff ? '/admin' : '/akun'" class="rounded-lg px-3 py-2 text-sm font-semibold text-brand-900">
                            {{ user.is_staff ? 'Panel Admin' : 'Akun Saya' }}
                        </Link>
                        <button type="button" class="rounded-lg px-3 py-2 text-left text-sm font-semibold text-brand-900" @click="keluar">
                            Keluar
                        </button>
                    </template>
                    <Link v-else href="/masuk" class="rounded-lg bg-brand-700 px-3 py-2 text-center text-sm font-semibold text-white">Masuk</Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6">
            <FlashBanner class="mb-4" />
            <slot />
        </main>

        <footer class="border-t border-brand-200 bg-white py-5 text-center text-xs text-slate-500">
            © {{ new Date().getFullYear() }} JATIZI ECO-BANK · Bank Sampah SMK Negeri 1 Cijati
        </footer>
    </div>
</template>
