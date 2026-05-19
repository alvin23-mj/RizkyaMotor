<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
    otherCars: {
        type: Array,
        default: () => [],
    },
    comments: {
        type: Array,
        default: () => [],
    },
});

const capitalizeWords = (text) => {
    if (!text) return '';
    return text.toString().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
};

const getPrimaryImage = (imageStr) => {
    if (Array.isArray(imageStr)) return imageStr[0] || 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-4.0.3';
    try {
        const images = JSON.parse(imageStr);
        return images[0] || 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-4.0.3';
    } catch (e) {
        return 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-4.0.3';
    }
};

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID').format(number);
};

const showBookingModal = ref(false);

const bookingForm = useForm({
    car_id: props.car.id,
    name: '',
    phone: '',
    email: '',
    meeting_date: '',
    meeting_time: '',
    notes: '',
});

const availableTimes = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00']; // Default times

const submitBooking = () => {
    bookingForm.post(route('bookings.store'), {
        onSuccess: () => {
            showBookingModal.value = false;
            bookingForm.reset();
        },
    });
};

const commentForm = useForm({
    name: '',
    message: '',
});

const submitComment = () => {
    commentForm.post(route('cars.comments.store', props.car.id), {
        onSuccess: () => {
            commentForm.reset('message');
        },
    });
};

const replyForms = ref({});

const submitReply = (commentId) => {
    const reply = replyForms.value[commentId];
    if (!reply) return;

    router.patch(route('comments.reply', commentId), { reply }, {
        onSuccess: () => {
            replyForms.value[commentId] = '';
        },
    });
};

const currentRole = ref('pengguna');
const showGlobalSearch = ref(false);
const searchQuery = ref('');
const activeTab = ref('detail'); // We are on detail page
const showProfileDropdown = ref(false);
const adminTab = ref('dashboard');

const page = usePage();
const user = computed(() => page.props.auth.user);

const logout = () => {
    router.post(route('logout'));
};

const parsedFeatures = computed(() => {
    if (!props.car.features) return [];
    if (Array.isArray(props.car.features)) return props.car.features;
    try {
        return JSON.parse(props.car.features);
    } catch (e) {
        return [];
    }
});
</script>

<template>
    <Head :title="`${capitalizeWords(car.brand)} ${capitalizeWords(car.model)} - Rizkya Motor`" />

    <div class="min-h-screen bg-[#f8fafc] text-slate-600 text-[15px] font-normal">
        <!-- header/navigation -->
        <header v-if="currentRole === 'pengguna'" class="sticky top-0 z-30 font-normal shadow-sm">
            <!-- Top Bar Contact Info & Auth -->
            <div class="bg-slate-900 text-slate-300 h-9 flex items-center text-[13px]">
                <div class="w-full px-6 flex items-center justify-between">
                    
                    <!-- Contact Info -->
                    <div class="flex items-center divide-x divide-slate-700/80">
                        <!-- Facebook -->
                        <div class="group flex items-center cursor-pointer h-9 px-4 border-l border-slate-700/80">
                            <i class="fa-brands fa-facebook group-hover:text-blue-400 transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-blue-400">Rizkya Motor</span>
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="group flex items-center cursor-pointer h-9 px-4 border-l border-slate-700/80">
                            <i class="fa-solid fa-envelope group-hover:text-white transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-white">info@rizkya-motor.com</span>
                            </div>
                        </div>
                        <!-- WA -->
                        <div class="group flex items-center cursor-pointer h-9 px-4">
                            <i class="fa-brands fa-whatsapp text-[15px] group-hover:text-emerald-400 transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-emerald-400">+62 812-3456-7890</span>
                            </div>
                        </div>
                        <!-- TikTok -->
                        <div class="group flex items-center cursor-pointer h-9 pl-4">
                            <i class="fa-brands fa-tiktok group-hover:text-white transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-white">@rizkyamotor</span>
                            </div>
                        </div>
                    </div>

                    <!-- Auth section & Switcher (Top Bar) -->
                    <div class="flex items-center font-normal">
                        <!-- Global Search Expanding -->
                        <div v-if="currentRole === 'pengguna'" class="flex items-center px-4 border-r border-slate-700/80">
                            <div class="relative flex items-center">
                                <div 
                                    class="overflow-hidden transition-all duration-300 flex items-center"
                                    :class="showGlobalSearch ? 'w-[180px] opacity-100 mr-2' : 'w-0 opacity-0'"
                                >
                                    <input 
                                        type="text" 
                                        v-model="searchQuery" 
                                        @keyup.enter="router.visit('/?tab=katalog&search=' + searchQuery)"
                                        placeholder="Cari mobil..." 
                                        class="w-full bg-slate-800 border-none rounded-sm px-3 py-1.5 text-[13px] text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-500"
                                    />
                                </div>
                                <button 
                                    @click="showGlobalSearch = !showGlobalSearch" 
                                    class="text-slate-400 hover:text-white transition-colors"
                                    title="Pencarian"
                                >
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tracking Jadwal / Jadwal Saya -->
                        <div v-if="currentRole === 'pengguna'" class="flex items-center px-4 border-r border-slate-700/80">
                            <Link 
                                href="/?tab=jadwal" 
                                class="text-slate-300 hover:text-white text-[13px] transition duration-200 flex items-center relative"
                            >
                                <i class="fa-solid fa-calendar-days mr-1.5"></i>Cek Jadwal
                            </Link>
                        </div>

                        <!-- Guest View: Show login/register links -->
                        <div v-if="!user" class="flex items-center justify-center h-9 px-4 border-r border-slate-700/80">
                            <Link 
                                :href="route('login')" 
                                class="text-slate-300 hover:text-white text-[13px] transition duration-200"
                            >
                                Masuk
                            </Link>
                        </div>

                        <!-- Authenticated View: Show Profile Dropdown -->
                        <div v-else class="relative pl-4 pr-4 border-r border-slate-700/80">
                            <!-- Dropdown Backdrop -->
                            <div v-if="showProfileDropdown" @click="showProfileDropdown = false" class="fixed inset-0 z-40"></div>

                            <button @click="showProfileDropdown = !showProfileDropdown" class="flex items-center space-x-3 relative z-50 transition-colors focus:outline-none group">
                                <div class="flex flex-col text-right group-hover:opacity-80 transition-opacity">
                                    <span class="text-[13px] font-medium text-slate-200">{{ user.name }}</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="{'rotate-180': showProfileDropdown}"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div v-if="showProfileDropdown" class="absolute right-0 mt-1 w-48 bg-slate-800 border border-slate-700 shadow-xl z-50 py-1 origin-top-right rounded-sm">
                                <template v-if="user.role === 'admin'">
                                    <div class="px-4 py-2 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Mode Tampilan</div>
                                    <button 
                                        @click="currentRole = 'pengguna'; showProfileDropdown = false" 
                                        class="w-full text-left px-4 py-2.5 text-[13px] hover:bg-slate-700 transition-colors flex items-center"
                                        :class="currentRole === 'pengguna' ? 'text-blue-400' : 'text-slate-300'"
                                    >
                                        <i class="fa-solid fa-user w-5"></i>User Mode
                                        <i v-if="currentRole === 'pengguna'" class="fa-solid fa-check ml-auto text-[11px]"></i>
                                    </button>
                                    <button 
                                        @click="currentRole = 'admin'; showProfileDropdown = false" 
                                        class="w-full text-left px-4 py-2.5 text-[13px] hover:bg-slate-700 transition-colors flex items-center"
                                        :class="currentRole === 'admin' ? 'text-blue-400' : 'text-slate-300'"
                                    >
                                        <i class="fa-solid fa-user-shield w-5"></i>Admin Mode
                                        <i v-if="currentRole === 'admin'" class="fa-solid fa-check ml-auto text-[11px]"></i>
                                    </button>
                                    <div class="border-t border-slate-700 my-1"></div>
                                </template>

                                <button 
                                    @click="logout" 
                                    class="w-full text-left px-4 py-2.5 text-[13px] text-red-400 hover:bg-slate-700 hover:text-red-300 transition-colors flex items-center"
                                >
                                    <i class="fa-solid fa-right-from-bracket w-5"></i>Keluar
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Main Navigation -->
            <div class="border-b border-slate-200/80 bg-white/80 backdrop-blur-md relative">
                <div class="w-full px-6 h-16 flex items-center justify-between">
                    
                    <!-- Left: branding -->
                    <Link href="/" class="cursor-pointer flex items-center z-10">
                        <span class="text-[22px] font-bold text-slate-800 select-none tracking-tight">
                            Rizkya Motor
                        </span>
                    </Link>

                    <!-- Center: menu items (User mode) -->
                    <div v-if="currentRole === 'pengguna'" class="absolute inset-x-0 flex items-center justify-center pointer-events-none z-0">
                        <nav class="flex items-center space-x-6 lg:space-x-8 pointer-events-auto">
                            <Link 
                                href="/?tab=beranda" 
                                :class="[activeTab === 'beranda' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Beranda
                            </Link>
                            <Link 
                                href="/?tab=katalog" 
                                :class="[activeTab === 'katalog' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Katalog Mobil
                            </Link>
                            <Link 
                                href="/?tab=tentang" 
                                :class="[activeTab === 'tentang' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Tentang Kami
                            </Link>
                            <Link 
                                href="/?tab=testimoni" 
                                :class="[activeTab === 'testimoni' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Testimoni
                            </Link>
                            <Link 
                                href="/?tab=kontak" 
                                :class="[activeTab === 'kontak' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Kontak
                            </Link>
                        </nav>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex items-center z-10 justify-end space-x-3">
                        <Link v-if="currentRole === 'pengguna'"
                            href="/?tab=bandingkan" 
                            class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 px-4 py-2 rounded-none text-[15px] font-medium transition duration-200 shadow-sm flex items-center"
                        >
                            <i class="fa-solid fa-scale-balanced mr-2"></i>Banding
                        </Link>
                        <Link v-if="currentRole === 'pengguna'"
                            href="/?tab=jual" 
                            class="bg-slate-800 text-white hover:bg-slate-900 px-5 py-2 rounded-none text-[15px] font-medium transition duration-200 shadow-sm flex items-center"
                        >
                            <i class="fa-solid fa-tag mr-2"></i>Jual Mobil
                        </Link>
                    </div>

                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Car Detail (Span 2) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Car Detail Card (Adapted from Modal) -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden flex flex-col font-normal">
                <!-- header -->
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 font-normal">
                    <div class="font-normal">
                        <span class="text-[15px] font-normal text-slate-400 uppercase">{{ capitalizeWords(car.brand) }} | {{ car.year }}</span>
                        <h1 class="text-[22px] font-normal text-slate-900 mt-0.5">{{ capitalizeWords(car.model) }}</h1>
                    </div>
                </div>

                <!-- content -->
                <div class="p-6 space-y-6 font-normal">
                    <!-- image -->
                    <div class="aspect-video bg-slate-100 rounded-none overflow-hidden border border-slate-200 shadow-sm font-normal">
                        <img :src="getPrimaryImage(car.image)" :alt="car.brand" class="w-full h-full object-cover pointer-events-none" />
                    </div>

                    <!-- descriptions -->
                    <div class="space-y-2 font-normal">
                        <span class="text-[15px] font-normal text-slate-400 uppercase block border-b border-slate-100 pb-1">Deskripsi Kendaraan</span>
                        <p class="text-[15px] text-slate-600 leading-relaxed font-normal">
                            {{ car.description ? capitalizeWords(car.description) : 'Tidak ada deskripsi tambahan.' }}
                        </p>
                    </div>

                    <!-- specifications grid -->
                    <div class="space-y-3 pt-2 font-normal">
                        <span class="text-[15px] font-normal text-slate-400 uppercase block border-b border-slate-100 pb-1">Spesifikasi Teknis</span>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-[15px] font-normal">
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-tags text-slate-500"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Harga Penawaran</span>
                                    <span class="text-slate-800 font-normal">{{ formatRupiah(car.price) }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-gauge-high text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Jarak Tempuh</span>
                                    <span class="text-slate-800 font-normal">{{ formatNumber(car.mileage) }} Km</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-gears text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Transmisi</span>
                                    <span class="text-slate-800 font-normal">{{ capitalizeWords(car.transmission) }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-gas-pump text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Bahan Bakar</span>
                                    <span class="text-slate-800 font-normal">{{ capitalizeWords(car.fuel) }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-bolt text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Mesin</span>
                                    <span class="text-slate-800 font-normal">{{ capitalizeWords(car.engine) }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-palette text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Warna</span>
                                    <span class="text-slate-800 font-normal">{{ capitalizeWords(car.color) }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-car text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Jenis Mobil</span>
                                    <span class="text-slate-800 font-normal">{{ capitalizeWords(car.car_type) || '-' }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-none border border-slate-200/60 shadow-sm flex items-center space-x-2 font-normal">
                                <i class="fa-solid fa-users text-slate-400"></i>
                                <div class="font-normal">
                                    <span class="text-slate-400 font-normal block text-[15px]">Kapasitas Duduk</span>
                                    <span class="text-slate-800 font-normal">{{ car.seating_capacity || '-' }} Kursi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- features -->
                    <div v-if="parsedFeatures.length > 0" class="space-y-2 font-normal pt-2">
                        <span class="text-[15px] font-normal text-slate-400 uppercase block border-b border-slate-100 pb-1">Fitur Unggulan</span>
                        <div class="flex flex-wrap gap-2 pt-1">
                            <span v-for="feature in parsedFeatures" :key="feature" class="bg-slate-100 text-slate-700 px-3 py-1 rounded-none border border-slate-200 text-[14px]">
                                <i class="fa-solid fa-check text-slate-500 mr-1.5"></i>{{ feature }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- footer actions -->
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end space-x-3 rounded-b-md font-normal">
                    <Link 
                        href="/" 
                        class="border border-slate-200 text-slate-600 hover:border-slate-300 hover:text-slate-800 bg-white hover:bg-slate-50 text-[15px] font-normal py-2 px-4 rounded-none transition-all flex items-center"
                    >
                        <i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali
                    </Link>
                    <button 
                        @click="showBookingModal = true" 
                        class="bg-slate-800 hover:bg-slate-900 text-white text-[15px] font-normal py-2 px-4 rounded-none shadow-sm hover:shadow transition-all flex items-center"
                    >
                        <i class="fa-solid fa-calendar-plus mr-1.5"></i>Atur Jadwal Temu
                    </button>
                </div>
            </div> <!-- End of Card -->
        </div> <!-- End of Left Column -->

            <!-- Right Column: Comments Sidebar (Span 1) -->
            <div class="space-y-6">
                <!-- Comments Section -->
                <div class="bg-white border border-slate-200 p-6 space-y-6 shadow-sm">
                    <div class="border-b border-slate-200 pb-2">
                        <h2 class="text-[18px] font-normal text-slate-900">Pertanyaan Pelanggan</h2>
                    </div>

                    <!-- Comment Form -->
                    <form @submit.prevent="submitComment" class="space-y-4">
                        <div class="flex flex-col font-normal">
                            <label class="text-[14px] font-normal text-slate-500 mb-1">Nama</label>
                            <input 
                                v-model="commentForm.name" 
                                type="text" 
                                required 
                                placeholder="Nama Anda..." 
                                class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[14px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[14px] font-normal text-slate-500 mb-1">Pertanyaan</label>
                            <textarea 
                                v-model="commentForm.message" 
                                rows="3" 
                                required 
                                placeholder="Tulis pertanyaan..." 
                                class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[14px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all resize-none font-normal"
                            ></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="commentForm.processing"
                                class="bg-slate-800 text-white text-[14px] font-normal py-2 px-4 rounded-none shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center"
                            >
                                <i class="fa-solid fa-paper-plane mr-1.5"></i>{{ commentForm.processing ? 'Mengirim...' : 'Kirim' }}
                            </button>
                        </div>
                    </form>

                    <div class="border-b border-slate-200 my-4"></div>

                    <!-- Comments List -->
                    <div v-if="comments && comments.length > 0" class="space-y-4 mt-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="comment in comments" :key="comment.id" class="space-y-2 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-slate-900 text-[14px]">{{ comment.name }}</span>
                                </div>
                                <span class="text-[12px] text-slate-400">{{ new Date(comment.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}</span>
                            </div>
                            <p class="text-[14px] text-slate-600 leading-relaxed">
                                {{ comment.message }}
                            </p>

                            <!-- Reply Display -->
                            <div v-if="comment.reply" class="ml-4 bg-slate-50 p-3 border border-slate-100 text-[14px] text-slate-600 leading-relaxed">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-medium text-slate-900 text-[13px]">Balasan Admin</span>
                                </div>
                                {{ comment.reply }}
                            </div>

                            <!-- Reply Form (Admin Only) -->
                            <div v-if="user?.role === 'admin' && !comment.reply" class="ml-4 mt-2">
                                <form @submit.prevent="submitReply(comment.id)" class="flex space-x-2">
                                    <input 
                                        v-model="replyForms[comment.id]" 
                                        type="text" 
                                        placeholder="Tulis balasan..." 
                                        class="flex-1 bg-slate-50 border border-slate-200 rounded-none px-3 py-1.5 text-[13px] text-slate-800 focus:outline-none focus:border-slate-500 transition-all font-normal"
                                    />
                                    <button 
                                        type="submit" 
                                        class="bg-slate-800 text-white text-[13px] font-normal py-1.5 px-3 rounded-none shadow-sm hover:shadow transition-all flex items-center"
                                    >
                                        Balas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-slate-500 text-[14px]">
                        Belum ada pertanyaan.
                    </div>
                </div>
            </div>
        </div> <!-- End of Grid -->

        <!-- Other Cars Section -->
            <div class="mt-12 space-y-6 font-normal">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <h2 class="text-[20px] font-normal text-slate-900">Mobil Lainnya yang Tersedia</h2>
                    <Link href="/?tab=katalog" class="text-[15px] text-slate-600 hover:text-slate-900 transition-colors">
                        Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
                    </Link>
                </div>
                
                <div v-if="otherCars && otherCars.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="otherCar in otherCars" :key="otherCar.id" class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden flex flex-col font-normal transition hover:border-slate-300">
                        <div class="aspect-video bg-slate-100 overflow-hidden border-b border-slate-100">
                            <img :src="getPrimaryImage(otherCar.image)" :alt="otherCar.brand" class="w-full h-full object-cover pointer-events-none" />
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[13px] font-normal text-slate-400 uppercase">{{ capitalizeWords(otherCar.brand) }} | {{ otherCar.year }}</span>
                                <h3 class="text-[16px] font-normal text-slate-900 mt-0.5">
                                    <Link :href="route('cars.show', otherCar.id)" class="hover:text-slate-700 transition-colors">
                                        {{ capitalizeWords(otherCar.model) }}
                                    </Link>
                                </h3>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-[15px] font-normal text-slate-800">{{ formatRupiah(otherCar.price) }}</span>
                                <Link :href="route('cars.show', otherCar.id)" class="text-[13px] text-slate-600 hover:text-slate-900 font-normal transition-colors flex items-center">
                                    Detail <i class="fa-solid fa-chevron-right ml-1 text-[10px]"></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-slate-500">
                    Tidak ada mobil lain yang tersedia saat ini.
                </div>
            </div>
        </main>

        <!-- Booking Modal (On this page) -->
        <div v-if="showBookingModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6 font-normal">
            <div class="bg-white border border-slate-200 shadow-xl rounded-none max-w-md w-full overflow-hidden flex flex-col font-normal">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center font-normal">
                    <h2 class="text-[17px] font-normal text-slate-900 flex items-center">
                        <i class="fa-solid fa-calendar-plus mr-2 text-slate-800"></i>Buat Janji Temu Peninjauan
                    </h2>
                    <button @click="showBookingModal = false" class="text-slate-400 hover:text-slate-600 font-normal text-[15px] flex items-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="submitBooking" class="p-6 space-y-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nama Lengkap</label>
                        <input 
                            v-model="bookingForm.name" 
                            type="text" 
                            required 
                            placeholder="Nama lengkap Anda..." 
                            class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nomor Telepon / WhatsApp</label>
                        <input 
                            v-model="bookingForm.phone" 
                            type="text" 
                            required 
                            placeholder="Contoh: 081234567890..." 
                            class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Email (Opsional)</label>
                        <input 
                            v-model="bookingForm.email" 
                            type="email" 
                            placeholder="Alamat email Anda..." 
                            class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Tanggal Kunjungan</label>
                            <input 
                                v-model="bookingForm.meeting_date" 
                                type="date" 
                                required 
                                class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Pertemuan</label>
                            <select 
                                v-model="bookingForm.meeting_time" 
                                required 
                                class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                            >
                                <option 
                                    v-for="time in availableTimes" 
                                    :key="time" 
                                    :value="time" 
                                >
                                    {{ time }} WIB
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Catatan Tambahan</label>
                        <textarea 
                            v-model="bookingForm.notes" 
                            rows="2" 
                            class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all resize-none font-normal"
                        ></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2 font-normal">
                        <button 
                            type="button" 
                            @click="showBookingModal = false" 
                            class="border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-50 text-[15px] font-normal py-2 px-4 rounded-none transition-all flex items-center"
                        >
                            <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="bookingForm.processing"
                            class="bg-slate-800 text-white text-[15px] font-normal py-2 px-4 rounded-none shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center"
                        >
                            <i class="fa-solid fa-check mr-1.5"></i>{{ bookingForm.processing ? 'Mengirim...' : 'Buat Jadwal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Footer Premium Modern -->
        <footer v-if="currentRole === 'pengguna'" class="bg-slate-950 border-t border-slate-900 pt-16 pb-8 text-slate-400 font-normal relative overflow-hidden mt-auto">
            <!-- decorative elements -->
            <div class="absolute top-0 left-1/4 w-[300px] h-[300px] bg-slate-800/20 blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 right-1/4 w-[300px] h-[300px] bg-slate-800/20 blur-[100px] pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                    
                    <!-- Branding Section -->
                    <div class="flex flex-col">
                        <span class="text-[24px] font-bold text-white tracking-tight mb-6">
                            Rizkya Motor
                        </span>
                        <p class="text-[15px] leading-relaxed mb-6">
                            Bursa otomotif bekas terpercaya di Indonesia. Menyediakan kendaraan berkualitas tinggi dengan inspeksi ketat dan harga transparan.
                        </p>
                        <div class="flex space-x-3 mt-auto">
                            <a href="#" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-instagram text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                            <a href="#" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-facebook-f text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                            <a href="#" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-tiktok text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                            <a href="#" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-youtube text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="flex flex-col">
                        <h4 class="text-[16px] font-bold text-white uppercase tracking-wider mb-6">Tautan Cepat</h4>
                        <ul class="space-y-3">
                            <li><Link href="/?tab=beranda" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Beranda</Link></li>
                            <li><Link href="/?tab=katalog" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Katalog Mobil</Link></li>
                            <li><Link href="/?tab=jual" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Jual Mobil</Link></li>
                            <li><Link href="/?tab=jadwal" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Cek Jadwal</Link></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div class="flex flex-col">
                        <h4 class="text-[16px] font-bold text-white uppercase tracking-wider mb-6">Hubungi Kami</h4>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <i class="fa-solid fa-location-dot mt-1 mr-4 text-slate-600"></i>
                                <span class="text-[15px] leading-relaxed">Jl. Otomotif Raya No. 123<br>Jakarta Selatan, DKI Jakarta 12345</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fa-solid fa-phone mr-4 text-slate-600"></i>
                                <span class="text-[15px]">+62 812-3456-7890</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fa-solid fa-envelope mr-4 text-slate-600"></i>
                                <span class="text-[15px]">info@rizkya-motor.com</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Business Hours -->
                    <div class="flex flex-col">
                        <h4 class="text-[16px] font-bold text-white uppercase tracking-wider mb-6">Jam Operasional</h4>
                        <ul class="space-y-4">
                            <li class="flex justify-between items-center border-b border-slate-800/80 pb-3">
                                <span class="text-[15px]">Senin - Jumat</span>
                                <span class="text-[15px] text-slate-200 font-medium">08:00 - 17:00</span>
                            </li>
                            <li class="flex justify-between items-center border-b border-slate-800/80 pb-3">
                                <span class="text-[15px]">Sabtu</span>
                                <span class="text-[15px] text-slate-200 font-medium">09:00 - 15:00</span>
                            </li>
                            <li class="flex justify-between items-center border-b border-slate-800/80 pb-3">
                                <span class="text-[15px]">Minggu</span>
                                <span class="text-[15px] text-red-400 font-medium">Tutup</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between">
                    <p class="text-[14px] text-slate-500 mb-4 md:mb-0">
                        &copy; 2026 Rizkya Motor. Hak cipta dilindungi undang-undang.
                    </p>
                    <div class="flex space-x-6 text-[14px] text-slate-500">
                        <a href="#" class="hover:text-slate-300 transition-colors duration-200">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-slate-300 transition-colors duration-200">Kebijakan Privasi</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
