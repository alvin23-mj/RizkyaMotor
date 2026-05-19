<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk - Rizkya Motor" />

        <!-- Header -->
        <div class="mb-10 text-center md:text-left">
            <h1 class="text-[28px] font-bold text-slate-900 mb-2 tracking-tight">Selamat Datang Kembali</h1>
            <p class="text-[14px] text-slate-500">Silakan masuk ke akun Anda untuk melanjutkan.</p>
        </div>

        <div v-if="status" class="mb-6 text-sm font-medium text-emerald-600 bg-emerald-50 p-3 border border-emerald-100">
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

            <!-- Password -->
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="text-[13px] font-medium text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[13px] text-slate-500 hover:text-slate-800 transition-colors"
                    >
                        Lupa sandi?
                    </Link>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-lock text-slate-400 absolute left-4 top-3.5"></i>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        placeholder="••••••••"
                        class="w-full bg-slate-50 border border-slate-200 rounded-none pl-11 pr-12 py-3 text-[14px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors font-normal"
                        required
                        autocomplete="current-password"
                    />
                    <button 
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                <div v-if="form.errors.password" class="text-red-500 text-[12px] mt-1">{{ form.errors.password }}</div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input
                    id="remember"
                    type="checkbox"
                    v-model="form.remember"
                    class="w-4 h-4 text-slate-800 border-slate-300 rounded-none focus:ring-slate-800 focus:ring-offset-0"
                />
                <label for="remember" class="ml-2 text-[13px] text-slate-600">Ingat saya</label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
                class="w-full bg-slate-900 text-white hover:bg-slate-800 py-3.5 px-4 rounded-none text-[14px] font-medium transition duration-300 shadow-md flex items-center justify-center tracking-wider uppercase"
            >
                <span v-if="form.processing">Memproses...</span>
                <span v-else>Masuk ke Akun</span>
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-[14px] text-slate-500">
                Belum punya akun?
                <Link
                    :href="route('register')"
                    class="text-slate-800 font-medium hover:underline transition-colors"
                >
                    Daftar Sekarang
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
