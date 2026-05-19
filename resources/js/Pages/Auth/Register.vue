<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun - Rizkya Motor" />

        <!-- Header -->
        <div class="mb-8 text-center md:text-left">
            <h1 class="text-[28px] font-bold text-slate-900 mb-2 tracking-tight">Buat Akun Baru</h1>
            <p class="text-[14px] text-slate-500">Daftar untuk menikmati semua fitur kami.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
            <div class="flex flex-col">
                <label for="name" class="text-[13px] font-medium text-slate-700 mb-2 uppercase tracking-wider">Nama Lengkap</label>
                <div class="relative">
                    <i class="fa-solid fa-user text-slate-400 absolute left-4 top-3.5"></i>
                    <input
                        id="name"
                        type="text"
                        v-model="form.name"
                        placeholder="Nama lengkap Anda"
                        class="w-full bg-slate-50 border border-slate-200 rounded-none pl-11 pr-4 py-3 text-[14px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors font-normal"
                        required
                        autofocus
                        autocomplete="name"
                    />
                </div>
                <div v-if="form.errors.name" class="text-red-500 text-[12px] mt-1">{{ form.errors.name }}</div>
            </div>

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
                        autocomplete="username"
                    />
                </div>
                <div v-if="form.errors.email" class="text-red-500 text-[12px] mt-1">{{ form.errors.email }}</div>
            </div>

            <!-- Password -->
            <div class="flex flex-col">
                <label for="password" class="text-[13px] font-medium text-slate-700 mb-2 uppercase tracking-wider">Kata Sandi</label>
                <div class="relative">
                    <i class="fa-solid fa-lock text-slate-400 absolute left-4 top-3.5"></i>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        placeholder="••••••••"
                        class="w-full bg-slate-50 border border-slate-200 rounded-none pl-11 pr-12 py-3 text-[14px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors font-normal"
                        required
                        autocomplete="new-password"
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

            <!-- Confirm Password -->
            <div class="flex flex-col">
                <label for="password_confirmation" class="text-[13px] font-medium text-slate-700 mb-2 uppercase tracking-wider">Konfirmasi Sandi</label>
                <div class="relative">
                    <i class="fa-solid fa-lock text-slate-400 absolute left-4 top-3.5"></i>
                    <input
                        id="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        v-model="form.password_confirmation"
                        placeholder="••••••••"
                        class="w-full bg-slate-50 border border-slate-200 rounded-none pl-11 pr-12 py-3 text-[14px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors font-normal"
                        required
                        autocomplete="new-password"
                    />
                    <button 
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <i class="fa-solid" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                <div v-if="form.errors.password_confirmation" class="text-red-500 text-[12px] mt-1">{{ form.errors.password_confirmation }}</div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
                class="w-full bg-slate-900 text-white hover:bg-slate-800 py-3.5 px-4 rounded-none text-[14px] font-medium transition duration-300 shadow-md flex items-center justify-center tracking-wider uppercase"
            >
                <span v-if="form.processing">Memproses...</span>
                <span v-else>Daftar Akun</span>
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-[14px] text-slate-500">
                Sudah punya akun?
                <Link
                    :href="route('login')"
                    class="text-slate-800 font-medium hover:underline transition-colors"
                >
                    Masuk di sini
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
