<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ username: '', password: '', remember: false });

const kirim = () =>
    form.post('/masuk', {
        onFinish: () => form.reset('password'),
    });
</script>

<template>
    <Head title="Masuk" />

    <div class="mx-auto mt-6 max-w-md">
        <div class="card p-6 sm:p-8">
            <div class="mb-6 text-center">
                <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-brand-700 text-2xl text-white" aria-hidden="true">♻</span>
                <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-brand-900">Masuk ke JATIZI ECO-BANK</h1>
                <p class="mt-1 text-sm text-slate-600">Admin, petugas, siswa, guru, dan tenaga administrasi.</p>
            </div>

            <form class="space-y-4" @submit.prevent="kirim">
                <div>
                    <label class="label" for="username">Username</label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        class="input"
                        autocomplete="username"
                        autofocus
                        required
                    />
                    <p v-if="form.errors.username" class="field-error">{{ form.errors.username }}</p>
                </div>

                <div>
                    <label class="label" for="password">Kata sandi</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="input"
                        autocomplete="current-password"
                        required
                    />
                    <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500" />
                    Ingat saya di perangkat ini
                </label>

                <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
                    {{ form.processing ? 'Memeriksa…' : 'Masuk' }}
                </button>
            </form>
        </div>
    </div>
</template>
