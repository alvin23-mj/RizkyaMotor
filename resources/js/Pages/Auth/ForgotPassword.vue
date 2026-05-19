<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi - Rizkya Motor" />

        <!-- Header -->
        <div class="mb-6 text-center md:text-left">
            <h1 class="text-[28px] font-bold text-slate-900 mb-2 tracking-tight">Lupa Kata Sandi?</h1>
            <p class="text-[14px] text-slate-500">
                Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 text-sm font-medium text-emerald-600 bg-emerald-50 p-3 border border-emerald-100"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div class="flex flex-col">
                <label for="email" class="text-[13px] font-medium text-slate-700 mb-2 uppercase tracking-wider">Alamat Email</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope text-slate-400 absolute left-4 top-3.5"></i>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="nama@email.com"
                        class="w-full bg-slate-50 border border-slate-200 rounded-none pl-11 pr-4 py-3 text-[14px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors font-normal"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <div v-if="form.errors.email" class="text-red-500 text-[12px] mt-1">{{ form.errors.email }}</div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
                class="w-full bg-slate-900 text-white hover:bg-slate-800 py-3.5 px-4 rounded-none text-[14px] font-medium transition duration-300 shadow-md flex items-center justify-center tracking-wider uppercase"
            >
                <span v-if="form.processing">Memproses...</span>
                <span v-else>Kirim Tautan Atur Ulang</span>
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-[14px] text-slate-500">
                Kembali ke
                <Link
                    :href="route('login')"
                    class="text-slate-800 font-medium hover:underline transition-colors"
                >
                    Halaman Masuk
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
