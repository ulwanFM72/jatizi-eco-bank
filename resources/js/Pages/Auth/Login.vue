<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({ username: "", password: "", remember: false });

const tampilkanSandi = ref(false);

const kirim = () =>
    form.post("/masuk", {
        onFinish: () => form.reset("password"),
    });
</script>

<template>
    <Head title="Masuk" />

    <div class="mx-auto mt-6 max-w-md">
        <div class="card p-6 sm:p-8">
            <div class="mb-6 text-center">
                <span
                    class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-brand-700 text-2xl text-white"
                    aria-hidden="true"
                    >♻</span
                >
                <h1
                    class="mt-3 text-2xl font-extrabold tracking-tight text-brand-900"
                >
                    Masuk ke JATIZI ECO-BANK
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Admin, petugas, siswa, guru, dan tenaga administrasi.
                </p>
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
                    <p v-if="form.errors.username" class="field-error">
                        {{ form.errors.username }}
                    </p>
                </div>

                <div>
                    <label class="label" for="password">Kata sandi</label>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="tampilkanSandi ? 'text' : 'password'"
                            class="input pr-11"
                            autocomplete="current-password"
                            required
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-500 hover:text-brand-700 focus:outline-none focus-visible:text-brand-700"
                            :aria-label="
                                tampilkanSandi
                                    ? 'Sembunyikan kata sandi'
                                    : 'Tampilkan kata sandi'
                            "
                            :aria-pressed="tampilkanSandi"
                            @click="tampilkanSandi = !tampilkanSandi"
                        >
                            <svg
                                v-if="!tampilkanSandi"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5s8.57 3.01 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.64 19.5 12 19.5s-8.57-3.01-9.96-7.18Z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                />
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.98 8.22A10.48 10.48 0 0 0 1.93 12c1.29 4.16 5.12 7.18 9.67 7.18 1.99 0 3.84-.58 5.4-1.58M6.23 6.23A9.75 9.75 0 0 1 12 4.82c4.55 0 8.38 3.02 9.67 7.18a10.5 10.5 0 0 1-4.29 5.57M6.23 6.23 3 3m3.23 3.23 3.65 3.65m7.5 7.5L21 21m-3.62-3.62-3.26-3.26m0 0a3 3 0 1 0-4.24-4.24m4.24 4.24L9.88 9.88"
                                />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="field-error">
                        {{ form.errors.password }}
                    </p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-500"
                    />
                    Ingat saya di perangkat ini
                </label>

                <button
                    type="submit"
                    class="btn btn-primary w-full"
                    :disabled="form.processing"
                >
                    {{ form.processing ? "Memeriksa…" : "Masuk" }}
                </button>
            </form>
        </div>
    </div>
</template>
