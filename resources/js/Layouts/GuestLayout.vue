<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-[#f8fafc] text-slate-600 font-sans selection:bg-slate-200 selection:text-slate-950">
        
        <!-- Left Side: Image (Hidden on mobile) -->
        <div class="hidden md:flex md:w-1/2 bg-slate-900 relative overflow-hidden">
            <div class="absolute inset-0">
                <img src="/images/login_bg.png" alt="Premium Car" class="w-full h-full object-cover object-center opacity-60" />
                <!-- overlay gradient -->
                <div class="absolute inset-0 bg-gradient-to-tr from-slate-900 via-slate-900/50 to-transparent"></div>
            </div>
            
            <!-- Glowing background element -->
            <div class="absolute top-1/4 left-1/4 w-[300px] h-[300px] bg-blue-500/10 blur-[100px] rounded-full pointer-events-none"></div>
            
            <!-- Branding & Text -->
            <div class="relative z-10 p-16 flex flex-col justify-between w-full h-full text-white">
                <div>
                    <Link href="/" class="text-[22px] font-bold tracking-tight text-white flex items-center">
                        <i class="fa-solid fa-car-side mr-2 text-slate-400"></i>Rizkya Motor
                    </Link>
                </div>
                
                <div>
                    <h2 class="text-[36px] font-bold leading-tight mb-4 tracking-tight">
                        Temukan Kendaraan <br>Impian Anda Bersama Kami.
                    </h2>
                    <p class="text-slate-400 text-[15px] max-w-md leading-relaxed">
                        Akses katalog eksklusif, kelola jadwal temu, dan nikmati pengalaman bertransaksi mobil bekas terbaik dengan standar transparansi tertinggi.
                    </p>
                </div>
                
                <div class="text-[13px] text-slate-500">
                    © 2026 Rizkya Motor. All rights reserved.
                </div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8 sm:p-16 bg-white relative">
            <!-- Subtle background grid or pattern if needed, keeping it clean for now -->
            
            <div class="w-full max-w-md">
                <!-- Mobile Branding -->
                <div class="md:hidden text-center mb-10">
                    <Link href="/" class="text-[24px] font-bold text-slate-900 flex items-center justify-center">
                        <i class="fa-solid fa-car-side mr-2 text-slate-800"></i>Rizkya Motor
                    </Link>
                </div>
                
                <!-- Content slot (Login/Register/Forgot forms) -->
                <slot />
            </div>
        </div>
    </div>
</template>
