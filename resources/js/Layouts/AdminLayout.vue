<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeTab: String,
    title: String
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden bg-slate-50 font-normal">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col h-full shrink-0">
            <div class="h-16 flex items-center px-6 border-b border-slate-800 shrink-0">
                <i class="fa-solid fa-shield-halved mr-3 text-blue-500 text-xl"></i>
                <span class="text-[18px] font-bold tracking-tight">Rizkya Admin</span>
            </div>
            <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto">
                <Link :href="route('cars.index', { admin_tab: 'dashboard' })" :class="[activeTab === 'dashboard' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-chart-line w-6 mr-2"></i> Dashboard
                </Link>
                <Link :href="route('cars.index', { admin_tab: 'kelola_mobil' })" :class="[activeTab === 'kelola_mobil' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-car w-6 mr-2"></i> Kelola Mobil
                </Link>
                <Link :href="route('cars.index', { admin_tab: 'kelola_jadwal' })" :class="[activeTab === 'kelola_jadwal' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-clipboard-list w-6 mr-2"></i> Janji Temu
                </Link>
                <Link :href="route('cars.index', { admin_tab: 'laporan' })" :class="[activeTab === 'laporan' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-file-invoice w-6 mr-2"></i> Laporan
                </Link>
                <Link :href="route('brands.index')" :class="[activeTab === 'brands' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-tags w-6 mr-2"></i> Kelola Merek
                </Link>
                <Link :href="route('testimonials.index')" :class="[activeTab === 'testimonials' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-comment-dots w-6 mr-2"></i> Kelola Testimoni
                </Link>
                <Link :href="route('partners.index')" :class="[activeTab === 'partners' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-handshake w-6 mr-2"></i> Kelola Mitra
                </Link>
                <Link :href="route('settings.index')" :class="[activeTab === 'settings' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white']" class="w-full flex items-center px-4 py-3 rounded-md transition-colors text-[15px]">
                    <i class="fa-solid fa-sliders w-6 mr-2"></i> Pengaturan Web
                </Link>
            </nav>
            <div class="p-4 border-t border-slate-800 shrink-0">
                <slot name="footer">
                    <Link :href="route('cars.index', { mode: 'user' })" class="w-full flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 py-2.5 rounded-md transition-colors text-[13px] font-medium">
                        <i class="fa-solid fa-arrow-right-arrow-left w-6 mr-2"></i> <span>Kembali ke User Mode</span>
                    </Link>
                </slot>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 shrink-0 shadow-sm z-10">
                <h1 class="text-[18px] font-medium text-slate-800">
                    {{ title }}
                </h1>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-3 border-r border-slate-200 pr-6">
                        <div class="flex flex-col text-right">
                            <div class="text-[14px] font-medium text-slate-800">{{ user?.name }}</div>
                            <div class="text-[12px] text-slate-500">Administrator</div>
                        </div>
                        <div class="w-10 h-10 bg-slate-100 rounded-md flex items-center justify-center border border-slate-200">
                            <i class="fa-solid fa-user-shield text-emerald-500"></i>
                        </div>
                    </div>
                    <button @click="logout" class="text-slate-500 hover:text-red-500 flex items-center space-x-2 text-[14px] transition-colors">
                        <i class="fa-solid fa-right-from-bracket"></i> <span>Keluar</span>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8 font-normal">
                <slot />
            </div>
        </div>
    </div>
</template>
