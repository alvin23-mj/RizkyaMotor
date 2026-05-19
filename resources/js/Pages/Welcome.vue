<script setup>
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    cars: {
        type: Array,
        required: true,
    },
    brands: {
        type: Array,
        required: true,
    },
    bookings: {
        type: Array,
        required: true,
    },
    partners: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    settings: {
        type: Object,
        default: () => ({}),
    },
    testimonials: {
        type: Array,
        default: () => ([]),
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Roles state: 'pengguna' | 'admin'
const currentRole = ref('pengguna');

// Watch user role and sync currentRole
watch(() => user.value, (newUser) => {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');
    
    if (mode === 'user') {
        currentRole.value = 'pengguna';
    } else if (newUser) {
        currentRole.value = newUser.role;
    } else {
        currentRole.value = 'pengguna';
    }
}, { immediate: true });

const logout = () => {
    router.post(route('logout'));
};

// Active tab for User mode: 'beranda' | 'katalog' | 'bandingkan' | 'jual' | 'jadwal'
const activeTab = ref('beranda');

// Check URL for tab param
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');
if (tabParam) {
    activeTab.value = tabParam;
}

// Active tab for Admin mode: 'dashboard' | 'kelola_mobil' | 'kelola_jadwal'
const adminTab = ref('dashboard');
const adminTabParam = urlParams.get('admin_tab');
if (adminTabParam) {
    adminTab.value = adminTabParam;
}

// search & filters for catalog
const searchQuery = ref(props.filters.search || '');
const selectedBrand = ref(props.filters.brand || '');
const selectedTransmission = ref(props.filters.transmission || '');
const selectedFuel = ref(props.filters.fuel || '');
const maxPrice = ref(props.filters.price_max || '');
const sortBy = ref('default');
const selectedType = ref('');
const selectedColor = ref('');

const uniqueCarTypes = computed(() => {
    if (!props.cars) return [];
    const types = props.cars.map(car => car.car_type).filter(Boolean);
    return [...new Set(types)];
});

const uniqueColors = computed(() => {
    if (!props.cars) return [];
    const colors = props.cars.map(car => car.color).filter(Boolean);
    return [...new Set(colors)];
});

// global search visibility
const showGlobalSearch = ref(false);
const showProfileDropdown = ref(false);

// active car for detail modal
const selectedCar = ref(null);

// comparison list (holds up to 3 cars)
const comparisonList = ref([]);

// toggle comparison selection
const toggleComparison = (car) => {
    const exists = comparisonList.value.find(item => item.id === car.id);
    if (exists) {
        comparisonList.value = comparisonList.value.filter(item => item.id !== car.id);
    } else {
        if (comparisonList.value.length < 3) {
            comparisonList.value.push(car);
        } else {
            alert('Anda hanya dapat membandingkan maksimal 3 kendaraan sekaligus.');
        }
    }
};

const getPrimaryImage = (imageStr) => {
    if (!imageStr) return '/images/placeholder-car.jpg';
    if (imageStr.startsWith('[')) {
        try {
            const arr = JSON.parse(imageStr);
            return arr.length > 0 ? arr[0] : '/images/placeholder-car.jpg';
        } catch(e) {
            return imageStr;
        }
    }
    return imageStr;
};

// computed filtered cars (only available 'tersedia' status for normal user catalog)
const filteredCars = computed(() => {
    if (!props.cars) return [];
    
    let result = props.cars.filter(car => {
        // Normal user only sees 'tersedia' and active cars in catalog
        if (car.status !== 'tersedia' || !car.is_active) return false;

        const matchesSearch = !searchQuery.value || 
            car.brand.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            car.model.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesBrand = !selectedBrand.value || car.brand === selectedBrand.value;
        const matchesTransmission = !selectedTransmission.value || car.transmission === selectedTransmission.value;
        const matchesFuel = !selectedFuel.value || car.fuel === selectedFuel.value;
        const matchesPrice = !maxPrice.value || car.price <= Number(maxPrice.value);
        const matchesType = !selectedType.value || car.car_type === selectedType.value;
        const matchesColor = !selectedColor.value || car.color === selectedColor.value;

        return matchesSearch && matchesBrand && matchesTransmission && matchesFuel && matchesPrice && matchesType && matchesColor;
    });

    // Sorting
    if (sortBy.value === 'price_asc') {
        result.sort((a, b) => a.price - b.price);
    } else if (sortBy.value === 'price_desc') {
        result.sort((a, b) => b.price - a.price);
    } else if (sortBy.value === 'year_desc') {
        result.sort((a, b) => b.year - a.year);
    } else if (sortBy.value === 'year_asc') {
        result.sort((a, b) => a.year - b.year);
    }

    return result;
});

const terlarisCars = computed(() => {
    return props.cars.filter(car => (car.is_terlaris || car.is_unggulan) && car.status === 'tersedia' && car.is_active);
});

// booking test drive form
const bookingForm = useForm({
    car_id: '',
    name: '',
    phone: '',
    email: '',
    meeting_date: '',
    meeting_time: '10:00',
    notes: '',
});

// selling form
const sellForm = useForm({
    name: '',
    phone: '',
    email: '',
    car_brand: '',
    car_model: '',
    car_year: '',
    car_price: '',
    meeting_date: '',
    meeting_time: '10:00',
    notes: '',
});

// Admin Car Management Form
const showCarModal = ref(false);
const editingCar = ref(null);
const showBookingEditModal = ref(false);
const editingBooking = ref(null);
const carForm = useForm({
    brand: '',
    model: '',
    year: '',
    price: '',
    mileage: '',
    transmission: 'otomatis',
    fuel: 'bensin',
    engine: '',
    color: '',
    image: '',
    images: [],
    description: '',
    condition: 'bekas',
    status: 'tersedia',
    contact_phone: '081234567890',
    is_active: true,
    is_terlaris: false,
    is_unggulan: false,
});

// open booking modal from detail
const openBookingModal = (car) => {
    bookingForm.car_id = car.id;
    bookingForm.notes = `Rencana peninjauan kendaraan: ${car.brand} ${car.model} (${car.year})`;
    selectedCar.value = null; // close detail modal
    showBookingModal.value = true;
};

const showBookingModal = ref(false);

const submitBooking = () => {
    bookingForm.post(route('bookings.store'), {
        onSuccess: () => {
            bookingForm.reset();
            showBookingModal.value = false;
            activeTab.value = 'jadwal';
            alert('Jadwal temu berhasil dikirim. Kami akan segera menghubungi Anda.');
        },
    });
};

const submitSellCar = () => {
    sellForm.post(route('sell-car.store'), {
        onSuccess: () => {
            sellForm.reset();
            activeTab.value = 'jadwal';
            alert('Pengajuan jual mobil dan jadwal temu berhasil dikirim. Staff kami akan melakukan inspeksi sesuai jadwal.');
        },
    });
};

// Admin actions
const openAddCarModal = () => {
    editingCar.value = null;
    carForm.reset();
    showCarModal.value = true;
};

const openEditCarModal = (car) => {
    editingCar.value = car;
    carForm.brand = car.brand;
    carForm.model = car.model;
    carForm.year = car.year;
    carForm.price = car.price;
    carForm.mileage = car.mileage;
    carForm.transmission = car.transmission;
    carForm.fuel = car.fuel;
    carForm.engine = car.engine;
    carForm.color = car.color;
    carForm.image = car.image;
    carForm.description = car.description;
    carForm.condition = car.condition;
    carForm.status = car.status;
    carForm.contact_phone = car.contact_phone || '081234567890';
    carForm.is_active = !!car.is_active;
    carForm.is_terlaris = !!car.is_terlaris;
    carForm.is_unggulan = !!car.is_unggulan;
    showCarModal.value = true;
};

const openEditBookingModal = (booking) => {
    editingBooking.value = booking;
    bookingForm.name = booking.name;
    bookingForm.phone = booking.phone;
    bookingForm.email = booking.email;
    bookingForm.meeting_date = booking.meeting_date;
    bookingForm.meeting_time = booking.meeting_time;
    bookingForm.notes = booking.notes;
    showBookingEditModal.value = true;
};

const submitBookingEdit = () => {
    bookingForm.put(route('bookings.update', editingBooking.value.id), {
        onSuccess: () => {
            showBookingEditModal.value = false;
            bookingForm.reset();
            alert('Jadwal temu berhasil diperbarui.');
        }
    });
};

const submitCarForm = () => {
    if (editingCar.value) {
        carForm.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('cars.update', editingCar.value.id), {
            onSuccess: () => {
                showCarModal.value = false;
                carForm.reset();
                alert('Informasi mobil berhasil diperbarui.');
            }
        });
    } else {
        carForm.post(route('cars.store'), {
            onSuccess: () => {
                showCarModal.value = false;
                carForm.reset();
                alert('Mobil baru berhasil ditambahkan.');
            }
        });
    }
};

const deleteCar = (carId) => {
    if (confirm('Apakah Anda yakin ingin menghapus mobil ini dari katalog?')) {
        router.delete(route('cars.destroy', carId), {
            onSuccess: () => {
                alert('Mobil berhasil dihapus.');
            }
        });
    }
};

const updateBookingStatus = (bookingId, newStatus) => {
    router.patch(route('bookings.updateStatus', bookingId), {
        status: newStatus
    }, {
        onSuccess: () => {
            alert('Status janji temu berhasil diperbarui.');
        }
    });
};

const disableCarInCatalog = (car) => {
    if (!car) return;
    if (confirm('Apakah Anda yakin ingin menonaktifkan mobil ini di katalog?')) {
        router.post(route('cars.update', car.id), {
            _method: 'put',
            brand: car.brand,
            model: car.model,
            year: car.year,
            price: car.price,
            mileage: car.mileage,
            transmission: car.transmission,
            fuel: car.fuel,
            engine: car.engine,
            color: car.color,
            condition: car.condition,
            status: 'terjual',
            contact_phone: car.contact_phone || '081234567890',
        }, {
            onSuccess: () => {
                alert('Mobil berhasil dinonaktifkan di katalog (status: terjual).');
            }
        });
    }
};

const capitalizeFirst = (text) => {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
};

const capitalizeWords = (text) => {
    if (!text) return '';
    return text.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getBrandLogo = (brand) => {
    const domains = {
        'toyota': 'toyota.co.id',
        'honda': 'honda-indonesia.com',
        'bmw': 'bmw.co.id',
        'mazda': 'mazda.co.id',
        'wuling': 'wuling.id',
        'hyundai': 'hyundai.com',
        'suzuki': 'suzuki.co.id',
        'daihatsu': 'daihatsu.co.id',
        'mitsubishi': 'mitsubishi-motors.co.id',
        'nissan': 'nissan.co.id'
    };
    const domain = domains[brand.toLowerCase()] || `${brand.toLowerCase()}.com`;
    return `https://logo.clearbit.com/${domain}`;
};

// helper currency & formats
const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value).replace('Rp', 'Rp ');
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const defaultTimes = ['09:00', '11:00', '13:00', '15:00', '17:00'];
const availableTimes = ref(JSON.parse(localStorage.getItem('availableTimes')) || defaultTimes);

const saveAvailableTimes = () => {
    localStorage.setItem('availableTimes', JSON.stringify(availableTimes.value));
};

const showTimeSettingsModal = ref(false);
const newTimeInput = ref('');

const addTimeSlot = () => {
    if (newTimeInput.value && !availableTimes.value.includes(newTimeInput.value)) {
        availableTimes.value.push(newTimeInput.value);
        availableTimes.value.sort();
        saveAvailableTimes();
        newTimeInput.value = '';
    }
};

const removeTimeSlot = (time) => {
    availableTimes.value = availableTimes.value.filter(t => t !== time);
    saveAvailableTimes();
};

const getTimeSlotInfo = (date, time) => {
    let baseLabel = time;
    if (!date) return { disabled: false, label: baseLabel };
    const booking = props.bookings.find(b => b.meeting_date === date && b.meeting_time.substring(0, 5) === time && b.status !== 'dibatalkan');
    if (booking) {
        return { disabled: true, label: `${baseLabel} (Dibooking oleh ${capitalizeWords(booking.name)})` };
    }
    return { disabled: false, label: baseLabel };
};

// --- Calendar State ---
const currentDateObj = new Date();
const currentMonth = ref(currentDateObj.getMonth());
const currentYear = ref(currentDateObj.getFullYear());

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const daysInMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
});

const blankDays = computed(() => {
    return new Date(currentYear.value, currentMonth.value, 1).getDay();
});

const getBookingsForDate = (year, month, day) => {
    const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return props.bookings.filter(b => b.meeting_date === dateString);
};

const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const selectedCalendarDate = ref(null);

const selectDate = (year, month, day) => {
    selectedCalendarDate.value = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
};

const isSelectedDate = (year, month, day) => {
    const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return selectedCalendarDate.value === dateString;
};

const isToday = (year, month, day) => {
    const d = new Date();
    return d.getFullYear() === year && d.getMonth() === month && d.getDate() === day;
};

const formatLongDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(date);
};

const filteredBookings = computed(() => {
    if (!selectedCalendarDate.value) return props.bookings;
    return props.bookings.filter(b => b.meeting_date === selectedCalendarDate.value);
});

const stats = computed(() => {
    return {
        totalCars: props.cars.length,
        totalBookings: props.bookings.length,
        pendingBookings: props.bookings.filter(b => b.status === 'menunggu').length,
        approvedBookings: props.bookings.filter(b => b.status === 'disetujui').length,
        soldCars: props.cars.filter(c => c.status === 'terjual').length
    };
});

// Admin Filtering
const adminCarSearch = ref('');
const filteredAdminCars = computed(() => {
    if (!adminCarSearch.value) return props.cars;
    const query = adminCarSearch.value.toLowerCase();
    return props.cars.filter(car => 
        car.brand.toLowerCase().includes(query) ||
        car.model.toLowerCase().includes(query) ||
        car.year.toString().includes(query)
    );
});

const adminBookingSearch = ref('');
const adminBookingDateFilter = ref('');
const adminBookingTypeFilter = ref('');
const adminBookingSort = ref('asc');

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const baseFilteredBookings = computed(() => {
    let bookings = props.bookings;
    
    // Search filter
    if (adminBookingSearch.value) {
        const query = adminBookingSearch.value.toLowerCase();
        bookings = bookings.filter(b => 
            b.name.toLowerCase().includes(query) ||
            b.phone.includes(query) ||
            (b.car && b.car.brand.toLowerCase().includes(query)) ||
            (b.car && b.car.model.toLowerCase().includes(query))
        );
    }
    
    // Date filter
    if (adminBookingDateFilter.value) {
        bookings = bookings.filter(b => b.meeting_date === adminBookingDateFilter.value);
    }

    // Type filter
    if (adminBookingTypeFilter.value) {
        bookings = bookings.filter(b => b.type === adminBookingTypeFilter.value);
    }
    
    // Sort
    bookings = [...bookings].sort((a, b) => {
        const dateA = new Date(a.meeting_date + ' ' + a.meeting_time);
        const dateB = new Date(b.meeting_date + ' ' + b.meeting_time);
        return adminBookingSort.value === 'asc' ? dateA - dateB : dateB - dateA;
    });
    
    return bookings;
});

const filteredAdminBookings = computed(() => {
    return baseFilteredBookings.value.filter(b => b.status !== 'selesai' && b.status !== 'dibatalkan');
});

const groupedAdminBookings = computed(() => {
    const grouped = {};
    filteredAdminBookings.value.forEach(booking => {
        const date = booking.meeting_date;
        if (!grouped[date]) {
            grouped[date] = [];
        }
        grouped[date].push(booking);
    });
    return grouped;
});

const filteredLaporanBookings = computed(() => {
    return baseFilteredBookings.value.filter(b => b.status === 'selesai' || b.status === 'dibatalkan');
});

const groupedLaporanBookings = computed(() => {
    const grouped = {};
    filteredLaporanBookings.value.forEach(booking => {
        const date = booking.meeting_date;
        if (!grouped[date]) {
            grouped[date] = [];
        }
        grouped[date].push(booking);
    });
    return grouped;
});
// --- End Calendar State ---
</script>

<template>
    <Head title="Rizkya Motor - Bursa Otomotif Bekas Berkualitas" />

    <div class="min-h-screen bg-[#f8fafc] text-slate-600 selection:bg-slate-200 selection:text-slate-950 text-[15px] font-normal">
        
        <!-- Glowing background elements (rounded-md/rounded-lg under constraints, no rounded-full/none) -->
        <div class="fixed top-0 left-1/4 w-[500px] h-[500px] bg-slate-400/5 blur-[120px] rounded-lg pointer-events-none"></div>
        <div class="fixed bottom-10 right-1/4 w-[400px] h-[400px] bg-blue-400/5 blur-[100px] rounded-lg pointer-events-none"></div>

        <!-- header/navigation -->
        <header v-if="currentRole === 'pengguna'" class="sticky top-0 z-30 font-normal shadow-sm">
            <!-- Top Bar Contact Info & Auth -->
            <div class="bg-slate-900 text-slate-300 h-9 flex items-center text-[13px]">
                <div class="w-full px-6 flex items-center justify-between">
                    
                    <!-- Contact Info -->
                    <div class="flex items-center divide-x divide-slate-700/80">
                        <!-- Facebook -->
                        <a v-if="settings.facebook_active !== '0'" :href="settings.facebook_link || '#'" target="_blank" class="group flex items-center cursor-pointer h-9 px-4 border-l border-slate-700/80">
                            <i class="fa-brands fa-facebook group-hover:text-blue-400 transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-blue-400">Facebook</span>
                            </div>
                        </a>
                        <!-- Email -->
                        <a :href="'mailto:' + (settings.email || 'info@rizkya-motor.com')" class="group flex items-center cursor-pointer h-9 px-4 border-l border-slate-700/80">
                            <i class="fa-solid fa-envelope group-hover:text-white transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-white">{{ settings.email || 'info@rizkya-motor.com' }}</span>
                            </div>
                        </a>
                        <!-- WA -->
                        <a :href="'https://wa.me/' + (settings.whatsapp_number || '')" target="_blank" class="group flex items-center cursor-pointer h-9 px-4">
                            <i class="fa-brands fa-whatsapp text-[15px] group-hover:text-emerald-400 transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-emerald-400">{{ settings.phone || '+62 812-3456-7890' }}</span>
                            </div>
                        </a>
                        <!-- TikTok -->
                        <a v-if="settings.tiktok_active !== '0'" :href="settings.tiktok_link || '#'" target="_blank" class="group flex items-center cursor-pointer h-9 pl-4">
                            <i class="fa-brands fa-tiktok group-hover:text-white transition-colors"></i>
                            <div class="overflow-hidden transition-all duration-500 max-w-0 group-hover:max-w-[200px] opacity-0 group-hover:opacity-100 flex items-center">
                                <span class="pl-2 whitespace-nowrap text-white">TikTok</span>
                            </div>
                        </a>
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
                                        @keyup.enter="activeTab = 'katalog'"
                                        placeholder="Cari mobil..." 
                                        class="w-full bg-slate-800 border-none rounded-sm px-3 py-1.5 text-[13px] text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-500"
                                    />
                                </div>
                                <button 
                                    @click="showGlobalSearch = !showGlobalSearch; if(showGlobalSearch && activeTab !== 'katalog') activeTab = 'katalog'" 
                                    class="text-slate-400 hover:text-white transition-colors"
                                    title="Pencarian"
                                >
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tracking Jadwal / Jadwal Saya -->
                        <div v-if="currentRole === 'pengguna'" class="flex items-center px-4 border-r border-slate-700/80">
                            <button 
                                @click="activeTab = 'jadwal'" 
                                class="text-slate-300 hover:text-white text-[13px] transition duration-200 flex items-center relative"
                            >
                                <i class="fa-solid fa-calendar-days mr-1.5"></i>Cek Jadwal
                            </button>
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
                    <div @click="activeTab = 'beranda'" class="cursor-pointer flex items-center z-10">
                        <span class="text-[22px] font-bold text-slate-800 select-none tracking-tight">
                            Rizkya Motor
                        </span>
                    </div>

                    <!-- Center: menu items (User mode) -->
                    <div v-if="currentRole === 'pengguna'" class="absolute inset-x-0 flex items-center justify-center pointer-events-none z-0">
                        <nav class="flex items-center space-x-6 lg:space-x-8 pointer-events-auto">
                            <button 
                                @click="activeTab = 'beranda'" 
                                :class="[activeTab === 'beranda' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Beranda
                            </button>
                            <button 
                                @click="activeTab = 'katalog'" 
                                :class="[activeTab === 'katalog' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Katalog Mobil
                            </button>
                            <button 
                                @click="activeTab = 'tentang'" 
                                :class="[activeTab === 'tentang' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Tentang Kami
                            </button>
                            <button 
                                @click="activeTab = 'testimoni'" 
                                :class="[activeTab === 'testimoni' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Testimoni
                            </button>
                            <button 
                                @click="activeTab = 'kontak'" 
                                :class="[activeTab === 'kontak' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[15px] lg:text-[16px] flex items-center"
                            >
                                Kontak
                            </button>
                        </nav>
                    </div>

                    <!-- Center: menu items (Admin mode) -->
                    <div v-else class="absolute inset-x-0 flex items-center justify-center pointer-events-none z-0">
                        <nav class="flex items-center space-x-8 pointer-events-auto">
                            <button 
                                @click="adminTab = 'dashboard'" 
                                :class="[adminTab === 'dashboard' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[16px] flex items-center"
                            >
                                Dashboard
                            </button>
                            <button 
                                @click="adminTab = 'kelola_mobil'" 
                                :class="[adminTab === 'kelola_mobil' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[16px] flex items-center"
                            >
                                Kelola Mobil
                            </button>
                            <button 
                                @click="adminTab = 'kelola_jadwal'" 
                                :class="[adminTab === 'kelola_jadwal' ? 'text-slate-800 font-medium' : 'text-slate-500 hover:text-slate-800']"
                                class="px-2 py-2 transition-all duration-300 text-[16px] flex items-center relative"
                            >
                                Kelola Janji Temu
                            </button>
                        </nav>
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex items-center z-10 justify-end space-x-3">
                        <button v-if="currentRole === 'pengguna'"
                            @click="activeTab = 'bandingkan'" 
                            class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 px-4 py-2 rounded-none text-[15px] font-medium transition duration-200 shadow-sm flex items-center"
                        >
                            <i class="fa-solid fa-scale-balanced mr-2"></i>Banding
                        </button>
                        <button v-if="currentRole === 'pengguna'"
                            @click="activeTab = 'jual'" 
                            class="bg-slate-800 text-white hover:bg-slate-900 px-5 py-2 rounded-none text-[15px] font-medium transition duration-200 shadow-sm flex items-center"
                        >
                            <i class="fa-solid fa-tag mr-2"></i>Jual Mobil
                        </button>
                    </div>

                </div>
            </div>
        </header>

        <!-- FULL-WIDTH HERO BANNER (BERANDA VIEW ONLY) -->
        <div v-if="currentRole === 'pengguna' && activeTab === 'beranda'">
            <!-- hero banner -->
            <section class="relative w-full h-[500px] flex items-center justify-center overflow-hidden bg-slate-900 border-b border-slate-800">
                <div class="absolute inset-0">
                    <img src="/images/showroom_bg.png" alt="Showroom Background" class="w-full h-full object-cover object-center opacity-40" />
                    <!-- overlay gradient for better text readability -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                </div>
                
                <div class="relative z-10 text-center px-6 max-w-4xl mx-auto">
                    <h1 class="text-[40px] md:text-[48px] font-bold text-white mb-6 leading-tight tracking-tight">
                        Temukan Mobil Impian Anda <br><span class="text-slate-400">Tanpa Kompromi</span>
                    </h1>
                    <p class="text-[17px] text-slate-300 leading-relaxed font-normal mb-10 max-w-2xl mx-auto">
                        Selamat datang di Rizkya Motor. Kami menyediakan mobil bekas berkualitas tinggi dengan transparansi penuh, garansi mesin, layanan inspeksi komparatif terpercaya, dan kemudahan penjadwalan temu secara langsung.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <button 
                            @click="activeTab = 'katalog'" 
                            class="bg-slate-800 text-white hover:bg-slate-900 px-8 py-3.5 rounded-none transition duration-300 font-medium shadow-lg text-[16px] flex items-center"
                        >
                            <i class="fa-solid fa-car mr-2"></i>Lihat Katalog Mobil
                        </button>
                        <button 
                            @click="activeTab = 'jual'" 
                            class="bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white/20 hover:border-white/40 px-8 py-3.5 rounded-none transition duration-300 font-medium shadow-lg text-[16px] flex items-center"
                        >
                            <i class="fa-solid fa-hand-holding-dollar mr-2"></i>Jual Mobil Anda
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- main content -->
        <main :class="currentRole === 'pengguna' ? (activeTab === 'katalog' ? 'w-full px-6 py-12 font-normal' : 'max-w-7xl mx-auto px-6 py-12 font-normal') : 'flex h-screen w-full overflow-hidden bg-slate-50'">
            
            <!-- USER VIEW CONTINUED -->
            <div v-if="currentRole === 'pengguna'">

                <!-- tab: BERANDA (Additional Content) -->
                <div v-if="activeTab === 'beranda'">
                    
                    <!-- Brands Section -->
                    <section class="py-8 mb-6 border-b border-slate-200/80">
                        <div class="text-center mb-8">
                            <h2 class="text-[20px] font-bold text-slate-900 uppercase tracking-wide">Merek Populer</h2>
                            <p class="text-[15px] text-slate-500 mt-2">Jelajahi berbagai pilihan merek mobil ternama di kelasnya.</p>
                        </div>
                        
                        <div class="flex flex-wrap justify-center gap-4">
                            <div 
                                v-for="brand in brands" 
                                :key="brand.id"
                                @click="activeTab = 'katalog'; selectedBrand = brand.name"
                                class="bg-white border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-400 transition-all duration-300 rounded-none px-6 py-4 flex items-center justify-center min-w-[150px] h-[80px] cursor-pointer group"
                            >
                                <img 
                                    :src="brand.image ? '/storage/' + brand.image : getBrandLogo(brand.name)" 
                                    :alt="brand.name" 
                                    class="max-h-10 max-w-[100px] object-contain grayscale group-hover:grayscale-0 transition-all duration-300" 
                                />
                            </div>

                            <div v-if="!brands || brands.length === 0" class="text-slate-400 text-[15px] italic">Memuat merek...</div>
                        </div>
                    </section>

                    <!-- Featured Cars Section -->
                    <!-- Best Selling Cars Section -->
                    <section class="py-8 mb-6 border-b border-slate-200/80">
                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <h2 class="text-[20px] font-bold text-slate-900 uppercase tracking-wide">Mobil Terlaris & Unggulan</h2>
                                <p class="text-[15px] text-slate-500 mt-2">Daftar mobil terlaris dan mobil premium pilihan kami.</p>
                            </div>
                            <button @click="activeTab = 'katalog'" class="text-slate-800 hover:text-slate-950 text-[15px] font-medium flex items-center transition-colors">
                                Lihat Semua <i class="fa-solid fa-arrow-right-long ml-2"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div 
                                v-for="car in terlarisCars.slice(0, 6)" 
                                :key="car.id" 
                                @click="router.visit(route('cars.show', car.id))"
                                class="bg-white border border-slate-200/80 rounded-md overflow-hidden group hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col justify-between font-normal cursor-pointer"
                            >
                                <!-- image -->
                                <div class="relative overflow-hidden aspect-video bg-slate-100 font-normal font-normal">
                                    <img 
                                        :src="getPrimaryImage(car.image)" 
                                        :alt="car.brand + ' ' + car.model" 
                                        class="w-full h-full object-cover pointer-events-none"
                                    />
                                </div>

                                <!-- body info -->
                                <div class="p-6 flex-grow font-normal">
                                    <div class="flex justify-between items-start mb-2 font-normal">
                                        <div class="flex flex-col font-normal">
                                            <span class="text-[15px] font-normal text-slate-400 uppercase">{{ capitalizeWords(car.brand) }} | {{ car.year }}</span>
                                            <span class="text-[17px] font-normal text-slate-900 mt-0.5">{{ capitalizeWords(car.model) }}</span>
                                            <span class="text-[16px] font-medium text-slate-800 mt-1">{{ formatRupiah(car.price) }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1 mt-1">
                                            <i v-if="car.is_terlaris" class="fa-solid fa-fire text-orange-500 text-[16px]"></i>
                                            <i v-if="car.is_unggulan" class="fa-solid fa-gem text-blue-500 text-[16px]"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- action buttons -->
                                <div class="px-6 pb-6 pt-2 border-t border-slate-100 grid grid-cols-2 gap-3 bg-slate-50/50 font-normal">
                                    <button 
                                        @click.stop="openBookingModal(car)" 
                                        class="border border-slate-200 hover:border-slate-400 hover:bg-slate-100/30 text-slate-800 text-[15px] font-normal py-2 px-3 rounded-md text-center transition duration-200 flex items-center justify-center"
                                    >
                                        <i class="fa-solid fa-calendar-plus mr-1.5 text-slate-500"></i>Atur Jadwal
                                    </button>
                                    <button 
                                        @click.stop="toggleComparison(car)" 
                                        :class="[comparisonList.find(c => c.id === car.id) ? 'bg-slate-100 border-slate-300 text-slate-900 font-normal' : 'border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-800 hover:bg-slate-100/50']"
                                        class="border text-[15px] font-normal py-2 px-3 rounded-md text-center transition duration-200 flex items-center justify-center"
                                    >
                                        <i class="fa-solid mr-1.5 animate-none" :class="[comparisonList.find(c => c.id === car.id) ? 'fa-check text-slate-800' : 'fa-plus text-slate-400']"></i>
                                        <span>Bandingkan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="terlarisCars.length === 0" class="text-center py-10 border border-dashed border-slate-300 rounded-none">
                            <p class="text-slate-500 text-[15px]">Belum ada mobil terlaris yang ditandai.</p>
                        </div>
                    </section>



                    <!-- About Us Section -->
                    <section class="py-0 mb-12 bg-slate-900 text-slate-300 flex flex-col md:flex-row items-stretch rounded-none shadow-lg border border-slate-800">
                        <!-- Left: Image -->
                        <div class="md:w-1/2 w-full h-[300px] md:h-auto">
                            <img src="/images/about_us_dealership.png" alt="Tentang Rizkya Motor" class="w-full h-full object-cover rounded-none" />
                        </div>
                        
                        <!-- Right: Content -->
                        <div class="md:w-1/2 w-full p-8 md:p-12 lg:p-16 flex flex-col justify-center z-10">
                            <span class="text-slate-400 font-bold tracking-wider uppercase text-[13px] mb-3 block">Tentang Kami</span>
                            <h2 class="text-[28px] md:text-[32px] font-bold text-white mb-6 leading-tight">Mitra Terpercaya Anda <br>di Dunia Otomotif</h2>
                            <p class="text-[16px] leading-relaxed mb-6 text-slate-400">
                                Rizkya Motor hadir untuk memberikan standar tertinggi dalam penjualan mobil bekas berkualitas. Kami percaya bahwa membeli kendaraan haruslah menjadi pengalaman yang menyenangkan, sepenuhnya transparan, dan bebas dari rasa khawatir.
                            </p>
                            <div class="flex flex-col space-y-4 mb-8">
                                <div class="flex items-start">
                                    <div class="bg-slate-500/20 rounded-full w-5 h-5 flex items-center justify-center mr-3 mt-0.5">
                                        <i class="fa-solid fa-check text-slate-400 text-[11px]"></i>
                                    </div>
                                    <span class="text-slate-300">Pilihan unit terlengkap dengan kondisi prima.</span>
                                </div>
                                <div class="flex items-start">
                                    <div class="bg-slate-500/20 rounded-full w-5 h-5 flex items-center justify-center mr-3 mt-0.5">
                                        <i class="fa-solid fa-check text-slate-400 text-[11px]"></i>
                                    </div>
                                    <span class="text-slate-300">Dokumen dijamin 100% aman dan berlegalitas asli.</span>
                                </div>
                                <div class="flex items-start">
                                    <div class="bg-slate-500/20 rounded-full w-5 h-5 flex items-center justify-center mr-3 mt-0.5">
                                        <i class="fa-solid fa-check text-slate-400 text-[11px]"></i>
                                    </div>
                                    <span class="text-slate-300">Layanan inspeksi dan garansi purna jual terpercaya.</span>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-800">
                                <button @click="activeTab = 'jual'" class="bg-transparent border border-slate-500 text-slate-400 hover:bg-slate-500 hover:text-white px-6 py-2.5 rounded-none font-medium transition duration-300 flex items-center w-fit">
                                    <i class="fa-solid fa-handshake mr-2"></i>Konsultasi Penjualan
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- tab: TENTANG -->
                <div v-if="activeTab === 'tentang'" class="animate-fade-in-up">
                    <section class="py-12 bg-white border border-slate-200/80 shadow-sm p-10 mb-8 rounded-none">
                        <div class="max-w-4xl mx-auto text-center mb-16">
                            <span class="text-slate-400 font-bold tracking-wider uppercase text-[13px] mb-3 block">Mengenal Kami Lebih Dekat</span>
                            <h2 class="text-[32px] md:text-[40px] font-bold text-slate-900 mb-6 leading-tight">Berkomitmen pada Kualitas dan Transparansi</h2>
                            <div class="w-20 h-1 bg-slate-800 mx-auto mb-6"></div>
                            <p class="text-[17px] leading-relaxed text-slate-600">
                                Rizkya Motor didirikan dengan visi sederhana: mengubah stigma jual-beli mobil bekas menjadi pengalaman yang aman, transparan, dan menyenangkan bagi setiap pelanggan. Kami hadir sebagai solusi bagi Anda yang mencari kendaraan berkualitas dengan jaminan penuh.
                            </p>
                        </div>
                        
                        <!-- Visi & Misi Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16">
                            <div class="relative h-[400px]">
                                <img src="/images/showroom_bg.png" alt="Showroom Rizkya Motor" class="w-full h-full object-cover rounded-none shadow-lg grayscale hover:grayscale-0 transition-all duration-700" />
                                <div class="absolute -bottom-6 -left-6 bg-slate-900 text-white p-6 border border-slate-700 shadow-xl hidden md:block">
                                    <div class="text-[32px] font-bold mb-1">10+</div>
                                    <div class="text-[14px] text-slate-400 uppercase tracking-widest">Tahun Pengalaman</div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-[24px] font-bold text-slate-900 mb-4">Visi & Misi Kami</h3>
                                <p class="text-[16px] text-slate-600 mb-6 leading-relaxed">
                                    Kami bermimpi menjadi bursa otomotif nomor satu di Indonesia yang mengutamakan kepuasan pelanggan di atas segalanya. Setiap kendaraan yang keluar dari pintu showroom kami telah melewati proses inspeksi 150 titik yang sangat ketat.
                                </p>
                                <ul class="space-y-4">
                                    <li class="flex items-start">
                                        <i class="fa-solid fa-check text-slate-800 mt-1 mr-3"></i>
                                        <span class="text-[16px] text-slate-600">Kejujuran dalam mendeskripsikan kondisi setiap unit.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fa-solid fa-check text-slate-800 mt-1 mr-3"></i>
                                        <span class="text-[16px] text-slate-600">Kemudahan transaksi dan fasilitas pembiayaan fleksibel.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fa-solid fa-check text-slate-800 mt-1 mr-3"></i>
                                        <span class="text-[16px] text-slate-600">Layanan purna jual yang sigap dan solutif.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    <!-- Features Section -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 py-4 mb-12">
                        <!-- Item 1 -->
                        <div class="text-center p-6 bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 rounded-none hover:-translate-y-1 group">
                            <div class="w-16 h-16 mx-auto bg-slate-50 border border-slate-100 flex items-center justify-center rounded-none mb-5 text-slate-800 text-2xl group-hover:bg-slate-800 group-hover:text-white transition-colors duration-300">
                                <i class="fa-solid fa-medal"></i>
                            </div>
                            <h3 class="text-[16px] font-bold text-slate-900 mb-2">Kualitas Terjamin</h3>
                            <p class="text-[14px] text-slate-500">Semua mobil telah melewati 150 titik inspeksi ketat oleh mitra.</p>
                        </div>
                        <!-- Item 2 -->
                        <div class="text-center p-6 bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 rounded-none hover:-translate-y-1 group">
                            <div class="w-16 h-16 mx-auto bg-slate-50 border border-slate-100 flex items-center justify-center rounded-none mb-5 text-slate-800 text-2xl group-hover:bg-slate-800 group-hover:text-white transition-colors duration-300">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h3 class="text-[16px] font-bold text-slate-900 mb-2">Garansi 1 Tahun</h3>
                            <p class="text-[14px] text-slate-500">Ketenangan pikiran dengan garansi mesin dan transmisi hingga 1 tahun penuh.</p>
                        </div>
                        <!-- Item 3 -->
                        <div class="text-center p-6 bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 rounded-none hover:-translate-y-1 group">
                            <div class="w-16 h-16 mx-auto bg-slate-50 border border-slate-100 flex items-center justify-center rounded-none mb-5 text-slate-800 text-2xl group-hover:bg-slate-800 group-hover:text-white transition-colors duration-300">
                                <i class="fa-solid fa-handshake-simple"></i>
                            </div>
                            <h3 class="text-[16px] font-bold text-slate-900 mb-2">Harga Transparan</h3>
                            <p class="text-[14px] text-slate-500">Tidak ada biaya tersembunyi. Harga yang Anda lihat adalah harga yang Anda bayar.</p>
                        </div>
                        <!-- Item 4 -->
                        <div class="text-center p-6 bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 rounded-none hover:-translate-y-1 group">
                            <div class="w-16 h-16 mx-auto bg-slate-50 border border-slate-100 flex items-center justify-center rounded-none mb-5 text-slate-800 text-2xl group-hover:bg-slate-800 group-hover:text-white transition-colors duration-300">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <h3 class="text-[16px] font-bold text-slate-900 mb-2">Proses Cepat</h3>
                            <p class="text-[14px] text-slate-500">Proses pembelian dan dokumen kendaraan diselesaikan dengan cepat dan mudah.</p>
                        </div>
                    </div>

                        <!-- Team -->
                        <div class="border-t border-slate-100 pt-16">
                            <div class="text-center mb-12">
                                <h3 class="text-[28px] font-bold text-slate-900">Mitra Kami</h3>
                                <p class="text-slate-500 mt-2">Didedikasikan untuk melayani Anda</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div v-for="partner in partners" :key="partner.id" class="text-center group">
                                    <div class="w-32 h-32 mx-auto bg-slate-100 rounded-full mb-4 overflow-hidden border-2 border-transparent group-hover:border-slate-800 transition-all duration-300">
                                        <img v-if="partner.image" :src="'/storage/' + partner.image" :alt="partner.name" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-3xl">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    </div>
                                    <h4 class="text-[18px] font-bold text-slate-900">{{ partner.name }}</h4>
                                    <p class="text-[14px] text-slate-500 uppercase tracking-wider">{{ partner.position || 'Mitra' }}</p>
                                </div>
                                <div v-if="partners.length === 0" class="col-span-3 text-center text-slate-400 py-10 border border-dashed border-slate-300">
                                    Belum ada mitra terdaftar.
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- tab: TESTIMONI -->
                <div v-if="activeTab === 'testimoni'" class="animate-fade-in-up">
                    <section class="py-12 mb-8">
                        <div class="text-center mb-12">
                            <span class="text-slate-400 font-bold tracking-wider uppercase text-[13px] mb-3 block">Kata Mereka</span>
                            <h2 class="text-[32px] md:text-[40px] font-bold text-slate-900 mb-4 leading-tight">Pengalaman Pelanggan Kami</h2>
                            <p class="text-[17px] text-slate-500 max-w-2xl mx-auto">Kami bangga menjadi bagian dari perjalanan pelanggan dalam menemukan mobil impian mereka.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Testimonial Item -->
                            <div v-for="(testimonial, index) in testimonials" :key="testimonial.id" 
                                 :class="[index % 3 === 1 ? 'bg-slate-900 border border-slate-800 text-slate-300' : 'bg-white border border-slate-200/80']"
                                 class="p-8 shadow-sm hover:shadow-lg transition-shadow duration-300 relative group rounded-none">
                                <i class="fa-solid fa-quote-right absolute top-6 right-6 text-5xl transition-colors"
                                   :class="[index % 3 === 1 ? 'text-slate-800 group-hover:text-slate-700' : 'text-slate-100 group-hover:text-slate-200']"></i>
                                <div class="flex text-amber-400 text-[12px] mb-4 space-x-1">
                                    <i v-for="i in testimonial.rating" :key="i" class="fa-solid fa-star"></i>
                                </div>
                                <p class="leading-relaxed mb-8 relative z-10 italic"
                                   :class="[index % 3 === 1 ? 'text-slate-300' : 'text-slate-600']">
                                    "{{ testimonial.message }}"
                                </p>
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-slate-200 rounded-full mr-4 overflow-hidden flex items-center justify-center">
                                        <img v-if="testimonial.avatar" :src="'/storage/' + testimonial.avatar" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-user text-slate-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold" :class="[index % 3 === 1 ? 'text-white' : 'text-slate-900']">{{ testimonial.name }}</h4>
                                        <p :class="[index % 3 === 1 ? 'text-slate-400' : 'text-slate-500']" class="text-[13px]">{{ testimonial.role }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="testimonials.length === 0" class="col-span-3 text-center py-10 border border-dashed border-slate-300 rounded-none">
                                <p class="text-slate-500 text-[15px]">Belum ada testimoni yang ditambahkan.</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- tab: KONTAK -->
                <div v-if="activeTab === 'kontak'" class="animate-fade-in-up">
                    <section class="py-12 mb-8 bg-white border border-slate-200/80 shadow-sm p-8 md:p-12 rounded-none">
                        <div class="text-center mb-12">
                            <h2 class="text-[32px] md:text-[40px] font-bold text-slate-900 mb-4 leading-tight">Hubungi Kami</h2>
                            <p class="text-[17px] text-slate-500 max-w-2xl mx-auto">Tim kami selalu siap sedia membantu menjawab pertanyaan Anda atau mengatur jadwal kunjungan.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                            <!-- Contact Form -->
                            <div class="order-2 lg:order-1">
                                <form @submit.prevent="alert('Pesan berhasil terkirim. Tim kami akan merespons secepatnya.')" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="flex flex-col">
                                            <label class="text-[15px] font-medium text-slate-700 mb-2">Nama Lengkap</label>
                                            <input type="text" required placeholder="Masukkan nama Anda" class="bg-slate-50 border border-slate-200 rounded-none px-4 py-3 text-[15px] text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors" />
                                        </div>
                                        <div class="flex flex-col">
                                            <label class="text-[15px] font-medium text-slate-700 mb-2">No. Telepon / WA</label>
                                            <input type="tel" required placeholder="Contoh: 08123456789" class="bg-slate-50 border border-slate-200 rounded-none px-4 py-3 text-[15px] text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors" />
                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-[15px] font-medium text-slate-700 mb-2">Alamat Email</label>
                                        <input type="email" required placeholder="email@contoh.com" class="bg-slate-50 border border-slate-200 rounded-none px-4 py-3 text-[15px] text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors" />
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-[15px] font-medium text-slate-700 mb-2">Pesan Anda</label>
                                        <textarea required rows="5" placeholder="Tuliskan pertanyaan atau pesan Anda di sini..." class="bg-slate-50 border border-slate-200 rounded-none px-4 py-3 text-[15px] text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition-colors resize-none"></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-slate-900 text-white hover:bg-slate-800 font-medium py-3.5 px-6 rounded-none transition duration-300 shadow-md flex items-center justify-center">
                                        <i class="fa-regular fa-paper-plane mr-2"></i>Kirim Pesan
                                    </button>
                                </form>
                            </div>

                            <!-- Contact Info & Map -->
                            <div class="order-1 lg:order-2 flex flex-col space-y-8">
                                <div class="bg-slate-50 border border-slate-100 p-8 flex flex-col space-y-6">
                                    <div class="flex items-start">
                                        <div class="w-12 h-12 bg-slate-900 text-white flex items-center justify-center rounded-none mr-4 shrink-0">
                                            <i class="fa-solid fa-location-dot text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-[16px] mb-1">Kunjungi Showroom Kami</h4>
                                            <p class="text-slate-600 text-[15px] leading-relaxed">{{ settings.address || 'Jl. Otomotif Raya No. 123, Kebayoran Baru Jakarta Selatan, DKI Jakarta 12345' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <div class="w-12 h-12 bg-slate-900 text-white flex items-center justify-center rounded-none mr-4 shrink-0">
                                            <i class="fa-brands fa-whatsapp text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-[16px] mb-1">WhatsApp</h4>
                                            <p class="text-slate-600 text-[15px] mb-1">{{ settings.phone || '+62 812-3456-7890' }} (Layanan 24 Jam)</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="w-12 h-12 bg-slate-900 text-white flex items-center justify-center rounded-none mr-4 shrink-0">
                                            <i class="fa-solid fa-envelope text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 text-[16px] mb-1">Email</h4>
                                            <p class="text-slate-600 text-[15px]">{{ settings.email || 'info@rizkya-motor.com' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Placeholder Map -->
                                <div class="flex-grow min-h-[250px] bg-slate-200 border border-slate-300 relative overflow-hidden flex items-center justify-center group">
                                    <!-- Google Maps Embed Placeholder -->
                                    <div class="absolute inset-0 bg-[url('https://maps.googleapis.com/maps/api/staticmap?center=-6.2088,106.8456&zoom=14&size=800x400&sensor=false')] bg-cover bg-center opacity-50 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-700"></div>
                                    <div class="relative z-10 bg-white/90 backdrop-blur px-6 py-3 border border-slate-200 text-slate-800 font-medium shadow-sm flex items-center">
                                        <i class="fa-solid fa-map-location-dot mr-2"></i>Peta Lokasi Interaktif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- tab: KATALOG -->
                <div v-if="activeTab === 'katalog'" class="flex flex-col md:flex-row gap-8">
                    
                    <!-- Left Sidebar (Filters) -->
                    <div class="w-full md:w-64 flex-shrink-0">
                        <div class="md:sticky md:top-28 space-y-4">
                            <!-- filters section -->
                            <section class="bg-white border border-slate-200/80 shadow-sm hover:shadow-md rounded-md p-6 transition-all duration-300 font-normal">
                                <div class="flex flex-col gap-4">
                                    <!-- sort filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Urutkan
                                        </label>
                                        <select 
                                            v-model="sortBy" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="default">Default</option>
                                            <option value="price_asc">Harga: Rendah ke Tinggi</option>
                                            <option value="price_desc">Harga: Tinggi ke Rendah</option>
                                            <option value="year_desc">Tahun: Terbaru</option>
                                            <option value="year_asc">Tahun: Terlama</option>
                                        </select>
                                    </div>

                                    <!-- search query -->
                                    <div class="flex flex-col relative font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Pencarian Kendaraan</label>
                                        <div class="relative font-normal">
                                            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-3.5"></i>
                                            <input 
                                                v-model="searchQuery" 
                                                type="text" 
                                                placeholder="Cari merk atau model..." 
                                                class="w-full bg-slate-50 border border-slate-200 rounded-md pl-9 pr-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                            />
                                        </div>
                                    </div>

                                    <!-- brand filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Merk Mobil
                                        </label>
                                        <select 
                                            v-model="selectedBrand" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="">Semua Merk</option>
                                            <option v-for="brand in brands" :key="brand.id" :value="brand.name">{{ capitalizeWords(brand.name) }}</option>
                                        </select>
                                    </div>

                                    <!-- transmission filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Transmisi
                                        </label>
                                        <select 
                                            v-model="selectedTransmission" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="">Semua Transmisi</option>
                                            <option value="otomatis">Otomatis (Automatic)</option>
                                            <option value="manual">Manual</option>
                                        </select>
                                    </div>

                                    <!-- fuel filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Bahan Bakar
                                        </label>
                                        <select 
                                            v-model="selectedFuel" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="">Semua Bahan Bakar</option>
                                            <option value="bensin">Bensin</option>
                                            <option value="listrik">Listrik (EV)</option>
                                        </select>
                                    </div>

                                    <!-- type filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Jenis Mobil
                                        </label>
                                        <select 
                                            v-model="selectedType" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="">Semua Jenis</option>
                                            <option v-for="type in uniqueCarTypes" :key="type" :value="type">{{ capitalizeWords(type) }}</option>
                                        </select>
                                    </div>

                                    <!-- color filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Warna
                                        </label>
                                        <select 
                                            v-model="selectedColor" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="">Semua Warna</option>
                                            <option v-for="color in uniqueColors" :key="color" :value="color">{{ capitalizeWords(color) }}</option>
                                        </select>
                                    </div>

                                    <!-- price filter -->
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">
                                            Harga Maksimum
                                        </label>
                                        <select 
                                            v-model="maxPrice" 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option value="">Semua Harga</option>
                                            <option value="100000000">&lt; Rp 100 Juta</option>
                                            <option value="200000000">&lt; Rp 200 Juta</option>
                                            <option value="300000000">&lt; Rp 300 Juta</option>
                                            <option value="400000000">&lt; Rp 400 Juta</option>
                                            <option value="500000000">&lt; Rp 500 Juta</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- clear filters button -->
                                <div v-if="searchQuery || selectedBrand || selectedTransmission || selectedFuel || maxPrice || sortBy !== 'default' || selectedType || selectedColor" class="mt-4 flex justify-end font-normal">
                                    <button 
                                        @click="searchQuery = ''; selectedBrand = ''; selectedTransmission = ''; selectedFuel = ''; maxPrice = ''; sortBy = 'default'; selectedType = ''; selectedColor = ''" 
                                        class="text-[13px] text-slate-500 hover:text-slate-800 transition-colors font-normal"
                                    >
                                        <i class="fa-solid fa-arrow-rotate-left mr-1"></i>Bersihkan Filter
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Right Content (Cars Grid) -->
                    <div class="flex-grow">
                        <!-- list cars grid -->
                        <section class="font-normal">
                            <div v-if="filteredCars.length === 0" class="text-center py-20 border border-dashed border-slate-300 rounded-md font-normal">
                                <p class="text-slate-500 text-[15px] font-normal">Tidak ada mobil bekas yang cocok dengan kriteria filter Anda.</p>
                                <button @click="searchQuery = ''; selectedBrand = ''; selectedTransmission = ''; selectedFuel = ''; maxPrice = ''; sortBy = 'default'; selectedType = ''; selectedColor = ''" class="mt-4 text-[15px] text-slate-800 hover:underline font-normal">
                                    <i class="fa-solid fa-rotate mr-1"></i>Tampilkan Semua Mobil
                                </button>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 font-normal">
                                <div 
                                    v-for="car in filteredCars" 
                                    :key="car.id" 
                                    @click="router.visit(route('cars.show', car.id))"
                                    class="bg-white border border-slate-200/80 rounded-md overflow-hidden group hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col justify-between font-normal cursor-pointer"
                                >
                                    <!-- image -->
                                    <div class="relative overflow-hidden aspect-video bg-slate-100 font-normal font-normal">
                                        <img 
                                            :src="getPrimaryImage(car.image)" 
                                            :alt="car.brand + ' ' + car.model" 
                                            class="w-full h-full object-cover pointer-events-none"
                                        />

                                    </div>

                                    <!-- body info -->
                                    <div class="p-6 flex-grow font-normal">
                                        <div class="flex justify-between items-start mb-2 font-normal">
                                            <div class="flex flex-col font-normal">
                                                <span class="text-[15px] font-normal text-slate-400 uppercase">{{ capitalizeWords(car.brand) }} | {{ car.year }}</span>
                                                <span class="text-[17px] font-normal text-slate-900 mt-0.5">{{ capitalizeWords(car.model) }}</span>
                                                <span class="text-[16px] font-medium text-slate-800 mt-1">{{ formatRupiah(car.price) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-1 mt-1">
                                                <i v-if="car.is_terlaris" class="fa-solid fa-fire text-orange-500 text-[16px]"></i>
                                                <i v-if="car.is_unggulan" class="fa-solid fa-gem text-blue-500 text-[16px]"></i>
                                            </div>
                                        </div>


                                    </div>

                                    <!-- action buttons -->
                                    <div class="px-6 pb-6 pt-2 border-t border-slate-100 grid grid-cols-2 gap-3 bg-slate-50/50 font-normal">
                                        <button 
                                            @click.stop="openBookingModal(car)" 
                                            class="border border-slate-200 hover:border-slate-400 hover:bg-slate-100/30 text-slate-800 text-[15px] font-normal py-2 px-3 rounded-md text-center transition duration-200 flex items-center justify-center"
                                        >
                                            <i class="fa-solid fa-calendar-plus mr-1.5 text-slate-500"></i>Atur Jadwal
                                        </button>
                                        <button 
                                            @click.stop="toggleComparison(car)" 
                                            :class="[comparisonList.find(c => c.id === car.id) ? 'bg-slate-100 border-slate-300 text-slate-900 font-normal' : 'border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-800 hover:bg-slate-100/50']"
                                            class="border text-[15px] font-normal py-2 px-3 rounded-md text-center transition duration-200 flex items-center justify-center"
                                        >
                                            <i class="fa-solid mr-1.5 animate-none" :class="[comparisonList.find(c => c.id === car.id) ? 'fa-check text-slate-800' : 'fa-plus text-slate-400']"></i>
                                            <span>Bandingkan</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- tab: COMPARE VEHICLES -->
                <div v-if="activeTab === 'bandingkan'">
                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-8 font-normal">
                        <div class="flex justify-between items-center mb-8 border-b border-slate-100 pb-4 font-normal">
                            <h2 class="text-[18px] font-normal text-slate-900">
                                <i class="fa-solid fa-scale-balanced mr-2 text-slate-800"></i>Bandingkan Spesifikasi Kendaraan
                            </h2>
                            <button 
                                v-if="comparisonList.length > 0" 
                                @click="comparisonList = []" 
                                class="text-[15px] font-normal text-slate-500 hover:text-red-600 transition-colors"
                            >
                                <i class="fa-solid fa-trash-can mr-1.5"></i>Bersihkan Semua ×
                            </button>
                        </div>

                        <!-- empty state -->
                        <div v-if="comparisonList.length === 0" class="text-center py-16 font-normal">
                            <p class="text-slate-500 text-[15px] font-normal mb-6">Anda belum memilih kendaraan untuk dibandingkan.</p>
                            <button 
                                @click="activeTab = 'katalog'" 
                                class="bg-slate-800 text-white border border-slate-800 hover:bg-slate-900 px-5 py-2.5 rounded-md transition text-[15px] font-normal shadow-sm hover:shadow flex items-center mx-auto"
                            >
                                <i class="fa-solid fa-car mr-2"></i>Pilih dari Katalog
                            </button>
                        </div>

                        <!-- active comparison list -->
                        <div v-else class="font-normal">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 font-normal">
                                <!-- spacer / labels column -->
                                <div class="hidden md:flex flex-col justify-end pb-4 border-r border-slate-100 pr-4 font-normal">
                                    <div class="h-[180px] flex items-end pb-4 border-b border-slate-100 font-normal">
                                        <span class="text-[15px] font-normal text-slate-400 uppercase">Spesifikasi</span>
                                    </div>
                                    <div class="space-y-4 py-4 text-[15px] font-normal text-slate-500">
                                        <div class="h-8 flex items-center">Merk & Model</div>
                                        <div class="h-8 flex items-center">Harga</div>
                                        <div class="h-8 flex items-center">Tahun Rilis</div>
                                        <div class="h-8 flex items-center">Jarak Tempuh</div>
                                        <div class="h-8 flex items-center">Transmisi</div>
                                        <div class="h-8 flex items-center">Bahan Bakar</div>
                                        <div class="h-8 flex items-center">Kapasitas Mesin</div>
                                        <div class="h-8 flex items-center">Warna Bodi</div>
                                        <div class="h-8 flex items-center">Kondisi Fisik</div>
                                    </div>
                                </div>

                                <!-- car columns -->
                                <div 
                                    v-for="car in comparisonList" 
                                    :key="car.id" 
                                    class="border border-slate-200 rounded-md bg-white overflow-hidden flex flex-col justify-between shadow-sm font-normal"
                                >
                                    <!-- image & header card -->
                                    <div class="relative h-[180px] bg-slate-100 font-normal border-b border-slate-100">
                                        <img :src="getPrimaryImage(car.image)" :alt="car.brand" class="w-full h-full object-cover pointer-events-none" />
                                        <button 
                                            @click="toggleComparison(car)" 
                                            class="absolute top-2 right-2 bg-black/60 hover:bg-red-600 text-white p-1 px-2.5 text-[15px] font-normal rounded-md transition-colors flex items-center"
                                        >
                                            <i class="fa-solid fa-xmark mr-1"></i>Hapus
                                        </button>
                                    </div>

                                    <!-- properties -->
                                    <div class="p-4 flex-grow font-normal">
                                        <!-- responsive labels shown only on mobile -->
                                        <div class="md:hidden space-y-4 py-2 text-[15px] text-slate-500 font-normal">
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Merk & Model</span>
                                                <span class="text-slate-900 font-normal">{{ capitalizeWords(car.brand) }} {{ capitalizeWords(car.model) }}</span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Harga</span>
                                                <span class="text-slate-800 font-normal"><span class="font-mono tracking-tight">{{ formatRupiah(car.price) }}</span></span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Tahun Rilis</span>
                                                <span class="text-slate-800 font-normal"><span class="font-mono">{{ car.year }}</span></span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Jarak Tempuh</span>
                                                <span class="text-slate-800 font-normal"><span class="font-mono tracking-tight">{{ formatNumber(car.mileage) }}</span> Km</span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Transmisi</span>
                                                <span class="text-slate-800 font-normal">{{ capitalizeWords(car.transmission) }}</span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Bahan Bakar</span>
                                                <span class="text-slate-800 font-normal">{{ capitalizeWords(car.fuel) }}</span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Mesin</span>
                                                <span class="text-slate-800 font-normal"><span class="font-mono tracking-tight">{{ capitalizeWords(car.engine) }}</span></span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Warna</span>
                                                <span class="text-slate-800 font-normal">{{ capitalizeWords(car.color) }}</span>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2 font-normal">
                                                <span class="block text-[15px] font-normal text-slate-400 mb-0.5">Kondisi</span>
                                                <span class="text-slate-800 font-normal">{{ capitalizeWords(car.condition) }}</span>
                                            </div>
                                        </div>

                                        <!-- desktop layout alignment -->
                                        <div class="hidden md:block space-y-4 text-[15px] text-slate-700 font-normal">
                                            <div class="h-8 flex items-center border-b border-slate-100 font-normal text-slate-950">{{ capitalizeWords(car.brand) }} {{ capitalizeWords(car.model) }}</div>
                                            <div class="h-8 flex items-center border-b border-slate-100 font-normal text-slate-800"><span class="font-mono tracking-tight">{{ formatRupiah(car.price) }}</span></div>
                                            <div class="h-8 flex items-center border-b border-slate-100"><span class="font-mono">{{ car.year }}</span></div>
                                            <div class="h-8 flex items-center border-b border-slate-100"><span class="font-mono tracking-tight">{{ formatNumber(car.mileage) }}</span> Km</div>
                                            <div class="h-8 flex items-center border-b border-slate-100">{{ capitalizeWords(car.transmission) }}</div>
                                            <div class="h-8 flex items-center border-b border-slate-100">{{ capitalizeWords(car.fuel) }}</div>
                                            <div class="h-8 flex items-center border-b border-slate-100"><span class="font-mono tracking-tight">{{ capitalizeWords(car.engine) }}</span></div>
                                            <div class="h-8 flex items-center border-b border-slate-100">{{ capitalizeWords(car.color) }}</div>
                                            <div class="h-8 flex items-center border-b border-slate-100">{{ capitalizeWords(car.condition) }}</div>
                                        </div>
                                    </div>

                                    <!-- book viewing schedule -->
                                    <div class="p-4 bg-slate-50 border-t border-slate-100 font-normal">
                                        <button 
                                            @click="openBookingModal(car)" 
                                            class="w-full bg-slate-800 hover:bg-slate-900 text-white text-[15px] font-normal py-2.5 rounded-md transition duration-200 shadow-sm flex items-center justify-center"
                                        >
                                            <i class="fa-solid fa-calendar-plus mr-1.5"></i>Atur Jadwal Temu
                                        </button>
                                    </div>
                                </div>

                                <!-- placeholder column if less than 3 compared -->
                                <div 
                                    v-if="comparisonList.length < 3" 
                                    class="hidden md:flex border border-dashed border-slate-300 rounded-md bg-slate-50/50 flex-col items-center justify-center p-6 text-center font-normal"
                                >
                                    <span class="text-[15px] font-normal text-slate-500 mb-3">Bandingkan hingga 3 kendaraan</span>
                                    <button 
                                        @click="activeTab = 'katalog'" 
                                        class="border border-slate-300 hover:border-slate-400 hover:bg-slate-100 text-slate-600 hover:text-slate-900 text-[15px] font-normal py-2 px-4 rounded-md transition-all flex items-center"
                                    >
                                        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Mobil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- tab: SELL CAR (JUAL MOBIL) -->
                <div v-if="activeTab === 'jual'">
                    <section class="max-w-3xl mx-auto bg-white border border-slate-200/80 shadow-sm rounded-md p-8 font-normal">
                        <div class="mb-8 border-b border-slate-100 pb-4 font-normal">
                            <h2 class="text-[18px] font-normal text-slate-900">
                                <i class="fa-solid fa-hand-holding-dollar mr-2 text-slate-800"></i>Jual Mobil Anda di Rizkya Motor
                            </h2>
                            <p class="text-[15px] text-slate-500 mt-1 font-normal">Isi detail kendaraan dan tentukan jadwal inspeksi fisik oleh tim penilai kami.</p>
                        </div>

                        <form @submit.prevent="submitSellCar" class="space-y-6 font-normal">
                            
                            <!-- seller credentials -->
                            <div class="space-y-4 font-normal">
                                <span class="text-[15px] font-normal text-slate-400 uppercase block border-b border-slate-100 pb-1">Data Diri Pemilik</span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nama Lengkap</label>
                                        <input 
                                            v-model="sellForm.name" 
                                            type="text" 
                                            required 
                                            placeholder="Nama lengkap pemilik..." 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nomor Telepon / WhatsApp</label>
                                        <input 
                                            v-model="sellForm.phone" 
                                            type="text" 
                                            required 
                                            placeholder="Contoh: 081234567890..." 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col font-normal">
                                    <label class="text-[15px] font-normal text-slate-500 mb-2">Alamat Email (Opsional)</label>
                                    <input 
                                        v-model="sellForm.email" 
                                        type="email" 
                                        placeholder="Alamat email Anda..." 
                                        class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                    />
                                </div>
                            </div>

                            <!-- car details -->
                            <div class="space-y-4 pt-4 font-normal">
                                <span class="text-[15px] font-normal text-slate-400 uppercase block border-b border-slate-100 pb-1">Informasi Kendaraan</span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Merk Kendaraan</label>
                                        <input 
                                            v-model="sellForm.car_brand" 
                                            type="text" 
                                            required 
                                            placeholder="Contoh: Toyota, Honda, BMW..." 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Model & Tipe</label>
                                        <input 
                                            v-model="sellForm.car_model" 
                                            type="text" 
                                            required 
                                            placeholder="Contoh: Raize GR Sport, Civic Hatchback..." 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Tahun Rilis</label>
                                        <input 
                                            v-model="sellForm.car_year" 
                                            type="number" 
                                            required 
                                            placeholder="Contoh: 2021..." 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Perkiraan Harga Penawaran (Rp)</label>
                                        <input 
                                            v-model="sellForm.car_price" 
                                            type="number" 
                                            required 
                                            placeholder="Contoh: 250000000..." 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- appointment details -->
                            <div class="space-y-4 pt-4 font-normal">
                                <span class="text-[15px] font-normal text-slate-400 uppercase block border-b border-slate-100 pb-1">Atur Jadwal Pertemuan Inspeksi</span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Tanggal Temu</label>
                                        <input 
                                            v-model="sellForm.meeting_date" 
                                            type="date" 
                                            required 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        />
                                    </div>
                                    <div class="flex flex-col font-normal">
                                        <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Pertemuan</label>
                                        <select 
                                            v-model="sellForm.meeting_time" 
                                            required 
                                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                                        >
                                            <option 
                                                v-for="time in availableTimes" 
                                                :key="time" 
                                                :value="time" 
                                                :disabled="getTimeSlotInfo(sellForm.meeting_date, time).disabled"
                                            >
                                                {{ getTimeSlotInfo(sellForm.meeting_date, time).label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex flex-col font-normal">
                                    <label class="text-[15px] font-normal text-slate-500 mb-2">Catatan Tambahan untuk Tim Inspeksi</label>
                                    <textarea 
                                        v-model="sellForm.notes" 
                                        placeholder="Jelaskan kondisi fisik mobil secara singkat, kelengkapan surat-surat, atau preferensi lokasi peninjauan..." 
                                        rows="3" 
                                        class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2.5 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all resize-none font-normal"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- submit -->
                            <div class="pt-4 border-t border-slate-100 flex justify-end font-normal">
                                <button 
                                    type="submit" 
                                    :disabled="sellForm.processing"
                                    class="bg-slate-800 text-white border border-slate-800 hover:bg-slate-900 px-6 py-3 rounded-md font-normal transition-all duration-300 disabled:opacity-50 shadow-sm hover:shadow text-[15px] flex items-center"
                                >
                                    <i class="fa-solid fa-paper-plane mr-2"></i>{{ sellForm.processing ? 'Sedang Mengirim...' : 'Kirim Pengajuan & Jadwalkan Inspeksi' }}
                                </button>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- tab: CEK JADWAL (BOOKINGS TRACKER) -->
                <div v-if="activeTab === 'jadwal'">
                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-8 font-normal">
                        <!-- header and calendar controls -->
                        <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4 border-b border-slate-100 pb-4 font-normal">
                            <div>
                                <h2 class="text-[18px] font-normal text-slate-900">
                                    <i class="fa-solid fa-calendar-days mr-2 text-slate-800"></i>Kalender Jadwal Pertemuan
                                </h2>
                                <p class="text-[15px] text-slate-500 mt-1 font-normal">Pilih tanggal untuk melihat detail pertemuan inspeksi dan peninjauan.</p>
                            </div>
                            
                            <!-- Calendar Navigation -->
                            <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-md p-1">
                                <button @click="prevMonth" class="px-3 py-1.5 text-slate-600 hover:text-slate-900 hover:bg-white rounded transition shadow-sm"><i class="fa-solid fa-chevron-left"></i></button>
                                <span class="font-medium text-[15px] text-slate-800 min-w-[130px] text-center">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
                                <button @click="nextMonth" class="px-3 py-1.5 text-slate-600 hover:text-slate-900 hover:bg-white rounded transition shadow-sm"><i class="fa-solid fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="mb-10 overflow-hidden rounded-md border border-slate-200 bg-slate-200 gap-px grid grid-cols-7 font-normal">
                            <!-- Headers -->
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Minggu</div>
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Senin</div>
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Selasa</div>
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Rabu</div>
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Kamis</div>
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Jumat</div>
                            <div class="bg-slate-50 text-center py-3 text-[13px] font-medium text-slate-500 uppercase tracking-wider">Sabtu</div>

                            <!-- Blank Days -->
                            <div v-for="blank in blankDays" :key="'blank-'+blank" class="bg-slate-50/30 min-h-[100px] p-2"></div>

                            <!-- Real Days -->
                            <div 
                                v-for="day in daysInMonth" 
                                :key="'day-'+day" 
                                @click="selectDate(currentYear, currentMonth, day)"
                                class="bg-white min-h-[100px] p-2 cursor-pointer transition-colors hover:bg-slate-50 font-normal relative"
                                :class="{ 'ring-2 ring-inset ring-slate-800 bg-slate-50': isSelectedDate(currentYear, currentMonth, day) }"
                            >
                                <span class="text-[14px] text-slate-500 block mb-2" :class="{'font-bold text-slate-900 bg-slate-200 w-6 h-6 rounded-full flex items-center justify-center': isToday(currentYear, currentMonth, day)}">{{ day }}</span>
                                <div class="flex flex-wrap gap-1">
                                    <div 
                                        v-for="b in getBookingsForDate(currentYear, currentMonth, day)" 
                                        :key="b.id"
                                        class="text-[10px] px-1.5 py-0.5 rounded-sm truncate bg-slate-100 text-slate-700 border border-slate-200"
                                        :class="[
                                            b.status === 'dibatalkan' ? 'opacity-50' : ''
                                        ]"
                                    >
                                        <span :class="b.status === 'dibatalkan' ? 'line-through' : ''">{{ b.meeting_time.substring(0, 5) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- detailed list title -->
                        <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-2">
                            <h3 class="text-[16px] font-medium text-slate-800">
                                <span v-if="selectedCalendarDate">Detail Jadwal pada {{ formatLongDate(selectedCalendarDate) }}</span>
                                <span v-else>Seluruh Daftar Jadwal</span>
                            </h3>
                            <button v-if="selectedCalendarDate" @click="selectedCalendarDate = null" class="text-sm text-slate-500 hover:text-slate-800 bg-slate-100 px-3 py-1 rounded-md">
                                <i class="fa-solid fa-list mr-1"></i>Lihat Semua
                            </button>
                        </div>

                        <!-- empty state -->
                        <div v-if="filteredBookings.length === 0" class="text-center py-12 bg-slate-50 border border-dashed border-slate-300 rounded-md font-normal">
                            <p class="text-slate-500 text-[15px] font-normal mb-4">Tidak ada jadwal pertemuan pada {{ selectedCalendarDate ? 'tanggal ini' : 'sistem' }}.</p>
                            <div v-if="!selectedCalendarDate" class="flex justify-center space-x-4 font-normal">
                                <button 
                                    @click="activeTab = 'katalog'" 
                                    class="bg-slate-800 text-white hover:bg-slate-900 px-4 py-2.5 rounded-md transition font-normal shadow-sm text-[15px] flex items-center"
                                >
                                    <i class="fa-solid fa-calendar-plus mr-1.5"></i>Jadwalkan Peninjauan Beli
                                </button>
                                <button 
                                    @click="activeTab = 'jual'" 
                                    class="bg-white border border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50 px-4 py-2.5 rounded-md transition font-normal shadow-sm text-[15px] flex items-center"
                                >
                                    <i class="fa-solid fa-handshake mr-1.5"></i>Jadwalkan Inspeksi Jual
                                </button>
                            </div>
                        </div>

                        <!-- list bookings -->
                        <div v-else class="space-y-4 font-normal">
                            <div 
                                v-for="booking in filteredBookings" 
                                :key="booking.id" 
                                class="bg-white border border-slate-200 rounded-md p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-sm font-normal transition hover:border-slate-300"
                            >
                                <div class="space-y-2 font-normal">
                                    <div class="flex items-center space-x-3 font-normal">
                                        <span 
                                            :class="[booking.type === 'pembelian' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200']"
                                            class="text-[14px] font-normal px-2.5 py-0.5 rounded-md border shadow-sm"
                                        >
                                            <i class="fa-solid mr-1.5" :class="[booking.type === 'pembelian' ? 'fa-cart-shopping' : 'fa-handshake']"></i>
                                            {{ booking.type === 'pembelian' ? 'Peninjauan Pembelian' : 'Inspeksi Penjualan' }}
                                        </span>
                                    </div>

                                    <!-- detail header -->
                                    <h3 v-if="booking.type === 'pembelian' && booking.car" class="text-[16px] font-medium text-slate-900">
                                        Rencana Survei: {{ capitalizeWords(booking.car.brand) }} {{ capitalizeWords(booking.car.model) }} ({{ booking.car.year }})
                                    </h3>
                                    <h3 v-else-if="booking.type === 'penjualan'" class="text-[16px] font-medium text-slate-900">
                                        Inspeksi Mandiri: {{ capitalizeWords(booking.car_brand) }} {{ capitalizeWords(booking.car_model) }} ({{ booking.car_year }})
                                    </h3>
                                    <h3 v-else class="text-[16px] font-medium text-slate-900">
                                        Rencana Pertemuan Survei Umum
                                    </h3>


                                </div>

                                <!-- time & status -->
                                <div class="flex flex-col items-start md:items-end space-y-2 min-w-[180px] font-normal">
                                    <div class="text-[14px] text-slate-600 font-normal flex flex-col md:items-end font-normal">
                                        <span class="font-medium text-slate-800"><i class="fa-solid fa-calendar mr-1.5 text-slate-400"></i>{{ formatLongDate(booking.meeting_date) }}</span>
                                        <span class="text-slate-500 mt-0.5 font-normal"><i class="fa-solid fa-clock mr-1.5 text-slate-400"></i>{{ booking.meeting_time.substring(0, 5) }}</span>
                                    </div>
                                    <span 
                                        :class="[
                                            booking.status === 'menunggu' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : '',
                                            booking.status === 'disetujui' ? 'bg-blue-50 text-blue-700 border-blue-200' : '',
                                            booking.status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '',
                                            booking.status === 'dibatalkan' ? 'bg-red-50 text-red-700 border-red-200' : '',
                                        ]"
                                        class="text-[14px] font-medium px-2.5 py-0.5 rounded-md border shadow-sm inline-block"
                                    >
                                        {{ capitalizeWords(booking.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </section>
                </div>

            </div>

            <!-- ADMIN VIEW -->
            <div v-if="currentRole === 'admin'" class="flex-1 flex w-full h-full font-normal">
                <AdminLayout :activeTab="adminTab" :title="adminTab === 'dashboard' ? 'Dashboard Utama' : (adminTab === 'kelola_mobil' ? 'Manajemen Kendaraan' : 'Manajemen Jadwal Temu')">
                    <template #footer>
                        <button @click="currentRole = 'pengguna'" class="w-full flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 py-2.5 rounded-md transition-colors text-[13px] font-medium">
                            <i class="fa-solid fa-arrow-right-arrow-left w-6 mr-2"></i> <span>Kembali ke User Mode</span>
                        </button>
                    </template>

                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <p class="text-[15px] text-slate-500 font-normal">Kelola listing mobil bekas dan atur semua persetujuan janji temu pelanggan dari satu dashboard.</p>
                        </div>
                        <Link 
                            v-if="adminTab === 'kelola_mobil'" 
                            :href="route('cars.create')" 
                            class="bg-blue-600 text-white hover:bg-blue-700 px-5 py-2.5 rounded-md font-medium text-[14px] transition shadow-sm flex items-center"
                        >
                            <i class="fa-solid fa-plus mr-2"></i>Tambah Mobil Baru
                        </Link>
                            <button 
                                v-if="adminTab === 'kelola_jadwal'" 
                                @click="showTimeSettingsModal = true" 
                                class="bg-slate-800 text-white hover:bg-slate-700 px-5 py-2.5 rounded-md font-medium text-[14px] transition shadow-sm flex items-center"
                            >
                                <i class="fa-solid fa-clock mr-2"></i>Atur Jam
                            </button>
                        </div>

                <!-- Admin tab: DASHBOARD -->
                <div v-if="adminTab === 'dashboard'">
                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal mb-8">
                        <h2 class="text-[18px] font-normal text-slate-900 flex items-center mb-6 pb-4 border-b border-slate-100">
                            <i class="fa-solid fa-chart-line mr-2 text-slate-800"></i>Ringkasan Performa
                        </h2>
                        <!-- Admin Dashboard Summary Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 font-normal">
                            <div class="bg-slate-50 border border-slate-200 p-6 rounded-md shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center text-slate-600 border border-slate-100 shadow-sm">
                                        <i class="fa-solid fa-car"></i>
                                    </div>
                                    <span class="text-[12px] font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">Aktif</span>
                                </div>
                                <div class="text-slate-500 text-[14px] font-normal">Total Unit Mobil</div>
                                <div class="text-[26px] font-bold text-slate-900 font-mono mt-1">{{ stats.totalCars }}</div>
                            </div>
                            
                            <div class="bg-slate-50 border border-slate-200 p-6 rounded-md shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center text-blue-600 border border-slate-100 shadow-sm">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <span class="text-[12px] font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">Semua</span>
                                </div>
                                <div class="text-slate-500 text-[14px] font-normal">Total Janji Temu</div>
                                <div class="text-[26px] font-bold text-slate-900 font-mono mt-1">{{ stats.totalBookings }}</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 p-6 rounded-md shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center text-yellow-600 border border-slate-100 shadow-sm">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <span class="text-[12px] font-medium text-yellow-600 bg-yellow-50 px-2 py-0.5 rounded-md border border-yellow-100">Penting</span>
                                </div>
                                <div class="text-slate-500 text-[14px] font-normal">Menunggu Persetujuan</div>
                                <div class="text-[26px] font-bold text-slate-900 font-mono mt-1">{{ stats.pendingBookings }}</div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 p-6 rounded-md shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center text-emerald-600 border border-slate-100 shadow-sm">
                                        <i class="fa-solid fa-sack-dollar"></i>
                                    </div>
                                    <span class="text-[12px] font-medium text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-sm">Terjual</span>
                                </div>
                                <div class="text-slate-500 text-[14px] font-normal">Mobil Berhasil Terjual</div>
                                <div class="text-[26px] font-bold text-slate-900 font-mono mt-1">{{ stats.soldCars }}</div>
                            </div>
                        </div>
                    </section>

                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal">
                        <h2 class="text-[18px] font-normal text-slate-900 flex items-center mb-6 pb-4 border-b border-slate-100">
                            <i class="fa-solid fa-bell mr-2 text-yellow-500"></i>Aktivitas Terbaru
                        </h2>
                        <div v-if="props.bookings.filter(b => b.status === 'menunggu').length === 0" class="text-center text-slate-500 py-8">
                            Tidak ada jadwal baru yang perlu disetujui.
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="booking in props.bookings.filter(b => b.status === 'menunggu').slice(0, 5)" :key="booking.id" class="flex justify-between items-center p-4 bg-slate-50 border border-slate-100 rounded-md">
                                <div>
                                    <p class="text-slate-800 font-medium">{{ capitalizeWords(booking.name) }}</p>
                                    <p class="text-slate-500 text-sm mt-1">Meminta jadwal {{ booking.type === 'pembelian' ? 'peninjauan' : 'inspeksi' }} pada {{ booking.meeting_date }} pukul {{ booking.meeting_time.substring(0, 5) }}</p>
                                </div>
                                <button @click="adminTab = 'kelola_jadwal'" class="text-sm bg-white border border-slate-300 text-slate-700 px-3 py-1.5 rounded-md hover:bg-slate-100 transition shadow-sm flex items-center">
                                    <i class="fa-solid fa-eye mr-1.5"></i>Tinjau
                                </button>
                            </div>
                            <div v-if="props.bookings.filter(b => b.status === 'menunggu').length > 5" class="text-center mt-4">
                                <button @click="adminTab = 'kelola_jadwal'" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Lihat Semua Jadwal Menunggu...</button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- admin tab: KELOLA MOBIL -->
                <div v-if="adminTab === 'kelola_mobil'">
                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal">
                        
                        <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4 border-b border-slate-100 pb-4 font-normal">
                            <h2 class="text-[18px] font-normal text-slate-900 flex items-center">
                                <i class="fa-solid fa-car mr-2 text-slate-800"></i>Daftar Semua Mobil dalam Sistem
                            </h2>
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input 
                                        v-model="adminCarSearch"
                                        type="text" 
                                        placeholder="Cari mobil..." 
                                        class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500 w-64"
                                    />
                                </div>
                                <span class="text-[15px] bg-slate-100 px-3 py-1.5 rounded-md text-slate-600 font-normal">Total: {{ filteredAdminCars.length }} Unit</span>
                            </div>
                        </div>

                        <!-- cars table list -->
                        <div class="overflow-x-auto font-normal">
                            <table class="w-full text-left text-[15px] text-slate-600 border-collapse font-normal">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-800 font-normal">
                                        <th class="p-4">ID</th>
                                        <th class="p-4">Foto</th>
                                        <th class="p-4">Model & Tahun</th>
                                        <th class="p-4">Harga</th>
                                        <th class="p-4">Kondisi & Jarak</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4">Visibilitas Card</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="filteredAdminCars.length === 0">
                                        <td colspan="6" class="p-8 text-center text-slate-400">Tidak ada mobil bekas terdaftar dalam sistem database.</td>
                                    </tr>
                                    <tr 
                                        v-for="car in filteredAdminCars" 
                                        :key="car.id" 
                                        class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150 font-normal"
                                    >
                                        <td class="p-4 font-mono text-slate-500">#{{ car.id }}</td>
                                        <td class="p-4">
                                            <img :src="getPrimaryImage(car.image)" :alt="car.brand" class="w-20 h-12 object-cover rounded-md border border-slate-200 pointer-events-none" />
                                        </td>
                                        <td class="p-4">
                                            <div class="font-normal text-slate-900">{{ capitalizeWords(car.brand) }} {{ capitalizeWords(car.model) }}</div>
                                            <div class="text-slate-400 mt-1 font-normal"><span class="font-mono">{{ car.year }}</span> | Transmisi: {{ capitalizeWords(car.transmission) }} | {{ capitalizeWords(car.fuel) }}</div>
                                        </td>
                                        <td class="p-4 font-normal text-slate-800">
                                            <span class="font-mono tracking-tight">{{ formatRupiah(car.price) }}</span>
                                        </td>
                                        <td class="p-4">
                                            <span class="capitalize bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-md border border-slate-200 font-normal text-[15px] mr-2">
                                                {{ car.condition }}
                                            </span>
                                            <span class="text-slate-500"><span class="font-mono tracking-tight">{{ formatNumber(car.mileage) }}</span> Km</span>
                                        </td>
                                        <td class="p-4">
                                            <span 
                                                :class="[
                                                    car.status === 'tersedia' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '',
                                                    car.status === 'terjual' ? 'bg-slate-100 text-slate-500 border-slate-200' : '',
                                                    car.status === 'perbaikan' ? 'bg-amber-50 text-amber-700 border-amber-200' : '',
                                                ]"
                                                class="text-[15px] font-normal px-2.5 py-0.5 rounded-md border shadow-sm inline-block"
                                            >
                                                {{ capitalizeWords(car.status) }}
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <span 
                                                :class="[car.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200']"
                                                class="text-[15px] font-normal px-2.5 py-0.5 rounded-md border shadow-sm inline-block"
                                            >
                                                {{ car.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center justify-center space-x-2 font-normal">
                                                <Link 
                                                    :href="route('cars.edit', car.id)" 
                                                    class="border border-slate-200 hover:border-yellow-300 hover:bg-yellow-50 text-yellow-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Edit Mobil"
                                                >
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </Link>
                                                <button 
                                                    @click="deleteCar(car.id)" 
                                                    class="border border-slate-200 hover:border-red-300 hover:bg-red-50 text-red-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Hapus Mobil"
                                                >
                                                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </section>
                </div>

                <!-- admin tab: KELOLA JADWAL (MANAGE BOOKINGS) -->
                <div v-if="adminTab === 'kelola_jadwal'">
                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal">
                        
                        <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4 border-b border-slate-100 pb-4 font-normal">
                            <h2 class="text-[18px] font-normal text-slate-900 flex items-center">
                                <i class="fa-solid fa-clipboard-list mr-2 text-slate-800"></i>Daftar Semua Pertemuan Pelanggan
                            </h2>
                            <div class="flex items-center space-x-3">
                                <!-- Date Filter -->
                                <input 
                                    v-model="adminBookingDateFilter"
                                    type="date" 
                                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500"
                                />
                                <!-- Type Filter -->
                                <select 
                                    v-model="adminBookingTypeFilter"
                                    class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500"
                                >
                                    <option value="">Semua Tipe</option>
                                    <option value="pembelian">Beli / Tinjau</option>
                                    <option value="penjualan">Jual / Inspeksi</option>
                                </select>
                                <!-- Sort Filter -->
                                <select 
                                    v-model="adminBookingSort"
                                    class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500"
                                >
                                    <option value="asc">Terdekat</option>
                                    <option value="desc">Terjauh</option>
                                </select>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input 
                                        v-model="adminBookingSearch"
                                        type="text" 
                                        placeholder="Cari pelanggan / mobil..." 
                                        class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500 w-64"
                                    />
                                </div>
                                <span class="text-[15px] bg-slate-100 px-3 py-1.5 rounded-md text-slate-600 font-normal">Total: {{ filteredAdminBookings.length }} Janji Temu</span>
                            </div>
                        </div>

                        <!-- bookings list table grouped by date -->
                        <div v-if="Object.keys(groupedAdminBookings).length === 0" class="p-8 text-center text-slate-400 font-normal">
                            Tidak ada janji temu pelanggan yang masuk.
                        </div>
                        
                        <div v-for="(bookings, date) in groupedAdminBookings" :key="date" class="mb-8">
                            <h3 class="text-[16px] font-medium text-slate-800 mb-3 bg-slate-50 p-2 flex items-center">
                                <i class="fa-solid fa-calendar-day mr-2"></i>{{ formatDate(date) }}
                            </h3>
                            <div class="overflow-x-auto font-normal">
                                <table class="w-full text-left text-[15px] text-slate-600 border-collapse font-normal">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-800 font-normal">
                                            <th class="p-4">Waktu & Tanggal</th>
                                            <th class="p-4">Detail Pelanggan</th>
                                            <th class="p-4">Tipe Janji</th>
                                            <th class="p-4">Detail Mobil</th>
                                            <th class="p-4">Status</th>
                                            <th class="p-4 text-center">Atur Status</th>
                                            <th class="p-4 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr 
                                            v-for="booking in bookings" 
                                            :key="booking.id" 
                                            class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150 font-normal"
                                        >
                                        <!-- 1. Waktu & Tanggal -->
                                        <td class="p-4 font-normal">
                                            <div class="flex items-center text-slate-700 font-normal">
                                                <i class="fa-solid fa-calendar mr-1.5 text-slate-400"></i>{{ booking.meeting_date }}
                                            </div>
                                            <div class="flex items-center text-slate-500 mt-1 font-normal">
                                                <i class="fa-solid fa-clock mr-1.5 text-slate-400"></i>{{ booking.meeting_time }} WIB
                                            </div>
                                        </td>
                                        <!-- 2. Detail Pelanggan -->
                                        <td class="p-4 font-normal">
                                            <div class="font-normal text-slate-900">{{ capitalizeWords(booking.name) }}</div>
                                            <div class="text-slate-500 mt-1 font-normal flex items-center">
                                                <i class="fa-solid fa-phone mr-1.5 text-slate-400"></i>
                                                <a :href="'https://wa.me/' + (booking.phone.startsWith('0') ? '62' + booking.phone.substring(1) : booking.phone).replace(/[^0-9]/g, '')" target="_blank" class="hover:text-blue-600 transition">{{ booking.phone }}</a>
                                            </div>
                                            <div class="text-slate-400 text-[15px] mt-0.5 font-normal flex items-center">
                                                <i class="fa-solid fa-envelope mr-1.5 text-slate-400"></i>
                                                <a v-if="booking.email" :href="'mailto:' + booking.email" class="hover:text-blue-600 transition">{{ booking.email }}</a>
                                                <span v-else>-</span>
                                            </div>
                                        </td>
                                        <!-- 3. Tipe Janji -->
                                        <td class="p-4">
                                            <span 
                                                :class="[booking.type === 'pembelian' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200']"
                                                class="text-[13px] font-normal px-2.5 py-0.5 rounded-md border"
                                            >
                                                {{ booking.type === 'pembelian' ? 'Beli / Tinjau' : 'Jual / Inspeksi' }}
                                            </span>
                                        </td>
                                        <!-- 4. Detail Mobil -->
                                        <td class="p-4 font-normal">
                                            <!-- if buying and has relational car -->
                                            <div v-if="booking.type === 'pembelian' && booking.car" class="font-normal">
                                                <span class="font-normal text-slate-800">{{ capitalizeWords(booking.car.brand) }} {{ capitalizeWords(booking.car.model) }}</span>
                                                <div class="text-slate-400 mt-1 font-normal">{{ booking.car.year }} | {{ formatRupiah(booking.car.price) }}</div>
                                            </div>
                                            <!-- if selling -->
                                            <div v-else-if="booking.type === 'penjualan'" class="font-normal">
                                                <span class="font-normal text-slate-800">{{ capitalizeWords(booking.car_brand) }} {{ capitalizeWords(booking.car_model) }}</span>
                                                <div class="text-slate-400 mt-1 font-normal">{{ booking.car_year }} | Penawaran: {{ formatRupiah(booking.car_price) }}</div>
                                            </div>
                                            <div v-else class="text-slate-400">-</div>
                                            
                                            <div v-if="booking.notes" class="mt-2 text-slate-500 bg-slate-50 p-2 rounded-md border border-slate-100 text-[15px] leading-relaxed max-w-xs font-normal">
                                                <strong>Catatan:</strong> {{ booking.notes }}
                                            </div>
                                        </td>
                                        <!-- 5. Status -->
                                        <td class="p-4">
                                            <span 
                                                :class="[
                                                    booking.status === 'menunggu' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : '',
                                                    booking.status === 'disetujui' ? 'bg-blue-50 text-blue-700 border-blue-200' : '',
                                                    booking.status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '',
                                                    booking.status === 'dibatalkan' ? 'bg-red-50 text-red-700 border-red-200' : '',
                                                ]"
                                                class="text-[15px] font-normal px-2.5 py-0.5 rounded-md border shadow-sm inline-block"
                                            >
                                                {{ capitalizeWords(booking.status) }}
                                            </span>
                                        </td>
                                        <!-- 6. Atur Status -->
                                        <td class="p-4">
                                            <div class="flex flex-col gap-1 items-center font-normal">
                                                <button 
                                                    v-if="booking.status !== 'disetujui' && booking.status !== 'selesai'"
                                                    @click="updateBookingStatus(booking.id, 'disetujui')" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white font-normal py-1 px-3 rounded-md text-[15px] transition shadow-sm w-full text-center flex items-center justify-center"
                                                >
                                                    <i class="fa-solid fa-circle-check mr-1.5"></i> Setujui
                                                </button>
                                                <button 
                                                    v-if="booking.status !== 'selesai' && booking.status !== 'dibatalkan'"
                                                    @click="updateBookingStatus(booking.id, 'selesai')" 
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-normal py-1 px-3 rounded-md text-[15px] transition shadow-sm w-full text-center flex items-center justify-center"
                                                >
                                                    <i class="fa-solid fa-check-double mr-1.5"></i> Selesai
                                                </button>
                                                <button 
                                                    v-if="booking.status !== 'dibatalkan' && booking.status !== 'selesai'"
                                                    @click="updateBookingStatus(booking.id, 'dibatalkan')" 
                                                    class="bg-white border border-slate-200 hover:border-red-300 hover:bg-red-50 text-red-600 font-normal py-1 px-3 rounded-md text-[15px] transition shadow-sm w-full text-center flex items-center justify-center"
                                                >
                                                    <i class="fa-solid fa-ban mr-1.5"></i> Batalkan
                                                </button>
                                            </div>
                                        </td>
                                        <!-- 7. Aksi -->
                                        <td class="p-4">
                                            <div class="flex items-center justify-center font-normal">
                                                <button 
                                                    @click="openEditBookingModal(booking)" 
                                                    class="border border-slate-200 hover:border-yellow-300 hover:bg-yellow-50 text-yellow-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Edit Janji Temu"
                                                >
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </div>

                    </section>
                </div>

                <!-- admin tab: LAPORAN -->
                <div v-if="adminTab === 'laporan'">
                    <section class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal">
                        
                        <div class="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4 border-b border-slate-100 pb-4 font-normal">
                            <h2 class="text-[18px] font-normal text-slate-900 flex items-center">
                                <i class="fa-solid fa-file-invoice mr-2 text-slate-800"></i>Laporan
                            </h2>
                            <div class="flex items-center space-x-3">
                                <!-- Date Filter -->
                                <input 
                                    v-model="adminBookingDateFilter"
                                    type="date" 
                                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500"
                                />

                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                    <input 
                                        v-model="adminBookingSearch"
                                        type="text" 
                                        placeholder="Cari pelanggan / mobil..." 
                                        class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-slate-500 w-64"
                                    />
                                </div>
                                <span class="text-[15px] bg-slate-100 px-3 py-1.5 rounded-md text-slate-600 font-normal">Total: {{ filteredLaporanBookings.length }} Laporan</span>
                            </div>
                        </div>                        <!-- bookings list table -->
                        <div v-if="filteredLaporanBookings.length === 0" class="p-8 text-center text-slate-400 font-normal">
                            Tidak ada laporan data yang selesai.
                        </div>
                        
                        <div v-else class="overflow-x-auto font-normal">
                            <table class="w-full text-left text-[15px] text-slate-600 border-collapse font-normal">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-800 font-normal">
                                        <th class="p-4">Waktu & Tanggal</th>
                                        <th class="p-4">Detail Pelanggan</th>
                                        <th class="p-4">Tipe Janji</th>
                                        <th class="p-4">Detail Mobil</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="booking in filteredLaporanBookings" 
                                        :key="booking.id" 
                                        class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150 font-normal"
                                    >
                                        <!-- 1. Waktu & Tanggal -->
                                        <td class="p-4 font-normal">
                                            <div class="flex items-center text-slate-700 font-normal">
                                                <i class="fa-solid fa-calendar mr-1.5 text-slate-400"></i>{{ booking.meeting_date }}
                                            </div>
                                            <div class="flex items-center text-slate-500 mt-1 font-normal">
                                                <i class="fa-solid fa-clock mr-1.5 text-slate-400"></i>{{ booking.meeting_time }} WIB
                                            </div>
                                        </td>
                                        <!-- 2. Detail Pelanggan -->
                                        <td class="p-4 font-normal">
                                            <div class="font-normal text-slate-900">{{ capitalizeWords(booking.name) }}</div>
                                            <div class="text-slate-500 mt-1 font-normal flex items-center">
                                                <i class="fa-solid fa-phone mr-1.5 text-slate-400"></i>
                                                <a :href="'https://wa.me/' + booking.phone.replace(/[^0-9]/g, '')" target="_blank" class="hover:text-blue-600 transition">{{ booking.phone }}</a>
                                            </div>
                                            <div class="text-slate-400 text-[15px] mt-0.5 font-normal flex items-center">
                                                <i class="fa-solid fa-envelope mr-1.5 text-slate-400"></i>
                                                <a v-if="booking.email" :href="'mailto:' + booking.email" class="hover:text-blue-600 transition">{{ booking.email }}</a>
                                                <span v-else>-</span>
                                            </div>
                                        </td>
                                        <!-- 3. Tipe Janji -->
                                        <td class="p-4">
                                            <span 
                                                :class="[booking.type === 'pembelian' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200']"
                                                class="text-[13px] font-normal px-2.5 py-0.5 rounded-md border"
                                            >
                                                {{ booking.type === 'pembelian' ? 'Beli / Tinjau' : 'Jual / Inspeksi' }}
                                            </span>
                                        </td>
                                        <!-- 4. Detail Mobil -->
                                        <td class="p-4 font-normal">
                                            <div v-if="booking.car" class="font-normal">
                                                <div class="font-normal text-slate-800">{{ capitalizeWords(booking.car.brand) }} {{ capitalizeWords(booking.car.model) }}</div>
                                                <div class="text-slate-500 text-[15px] mt-0.5 font-normal">{{ booking.car.year }} | {{ formatRupiah(booking.car.price) }}</div>
                                            </div>
                                            <div v-else class="text-slate-400 font-normal">-</div>
                                            <div v-if="booking.notes" class="mt-2 text-slate-500 bg-slate-50 p-2 rounded-md border border-slate-100 text-[15px] leading-relaxed max-w-xs font-normal">
                                                <strong>Catatan:</strong> {{ booking.notes }}
                                            </div>
                                        </td>
                                        <!-- 5. Status -->
                                        <td class="p-4">
                                            <span 
                                                :class="[
                                                    booking.status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '',
                                                    booking.status === 'dibatalkan' ? 'bg-red-50 text-red-700 border-red-200' : '',
                                                ]"
                                                class="text-[15px] font-normal px-2.5 py-0.5 rounded-md border shadow-sm inline-block"
                                            >
                                                {{ capitalizeWords(booking.status) }}
                                            </span>
                                        </td>
                                        <!-- 6. Aksi -->
                                        <td class="p-4">
                                            <div class="flex flex-col gap-1 items-center font-normal">
                                                <button 
                                                    @click="updateBookingStatus(booking.id, 'menunggu')" 
                                                    class="bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-normal py-1 px-3 rounded-md text-[13px] transition shadow-sm w-full text-center flex items-center justify-center"
                                                >
                                                    <i class="fa-solid fa-rotate-left mr-1.5"></i> Kembalikan
                                                </button>
                                                <button 
                                                    v-if="booking.status !== 'dibatalkan' && booking.car && booking.car.status === 'tersedia'"
                                                    @click="disableCarInCatalog(booking.car)" 
                                                    class="bg-white border border-red-200 hover:border-red-300 hover:bg-red-50 text-red-600 font-normal py-1 px-3 rounded-md text-[13px] transition shadow-sm w-full text-center flex items-center justify-center"
                                                >
                                                    <i class="fa-solid fa-eye-slash mr-1.5"></i> Nonaktifkan Card
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                </AdminLayout>
            </div>

        </main>



        <!-- USER schedule booking modal (For user view, standard rounded-md, no none or full) -->
        <div v-if="showBookingModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6 font-normal">
            <div class="bg-white border border-slate-200 shadow-xl rounded-md max-w-md w-full overflow-hidden flex flex-col font-normal">
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
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nomor Telepon / WhatsApp</label>
                        <input 
                            v-model="bookingForm.phone" 
                            type="text" 
                            required 
                            placeholder="Contoh: 081234567890..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Email (Opsional)</label>
                        <input 
                            v-model="bookingForm.email" 
                            type="email" 
                            placeholder="Alamat email Anda..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Tanggal Kunjungan</label>
                            <input 
                                v-model="bookingForm.meeting_date" 
                                type="date" 
                                required 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Pertemuan</label>
                            <select 
                                v-model="bookingForm.meeting_time" 
                                required 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                            >
                                <option 
                                    v-for="time in availableTimes" 
                                    :key="time" 
                                    :value="time" 
                                    :disabled="getTimeSlotInfo(bookingForm.meeting_date, time).disabled"
                                >
                                    {{ getTimeSlotInfo(bookingForm.meeting_date, time).label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Catatan Tambahan</label>
                        <textarea 
                            v-model="bookingForm.notes" 
                            rows="2" 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all resize-none font-normal"
                        ></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2 font-normal">
                        <button 
                            type="button" 
                            @click="showBookingModal = false" 
                            class="border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-50 text-[15px] font-normal py-2 px-4 rounded-md transition-all flex items-center"
                        >
                            <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="bookingForm.processing"
                            class="bg-slate-800 text-white text-[15px] font-normal py-2 px-4 rounded-md shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center"
                        >
                            <i class="fa-solid fa-check mr-1.5"></i>{{ bookingForm.processing ? 'Mengirim...' : 'Buat Jadwal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ADMIN TIME SETTINGS MODAL -->
        <div v-if="showTimeSettingsModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6 font-normal">
            <div class="bg-white border border-slate-200 shadow-xl rounded-md max-w-md w-full overflow-hidden flex flex-col font-normal">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center font-normal">
                    <h2 class="text-[17px] font-normal text-slate-900 flex items-center">
                        <i class="fa-solid fa-clock mr-2 text-slate-800"></i>Atur Jam Pertemuan
                    </h2>
                    <button @click="showTimeSettingsModal = false" class="text-slate-400 hover:text-slate-600 font-normal text-[15px] flex items-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 font-normal">
                    <!-- Add New Time -->
                    <div class="flex gap-2 font-normal">
                        <input 
                            v-model="newTimeInput" 
                            type="time" 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal flex-1"
                        />
                        <button 
                            @click="addTimeSlot"
                            class="bg-slate-800 text-white text-[15px] font-normal py-2 px-4 rounded-md shadow-sm hover:bg-slate-700 transition-all flex items-center"
                        >
                            <i class="fa-solid fa-plus mr-1.5"></i>Tambah
                        </button>
                    </div>

                    <!-- List of Times -->
                    <div class="space-y-2 font-normal max-h-48 overflow-y-auto">
                        <div 
                            v-for="time in availableTimes" 
                            :key="time" 
                            class="flex justify-between items-center bg-slate-50 p-2 rounded-md border border-slate-100"
                        >
                            <span class="text-slate-800 font-normal">{{ time }} WIB</span>
                            <button 
                                @click="removeTimeSlot(time)" 
                                class="text-red-500 hover:text-red-700 font-normal text-[15px] flex items-center p-1"
                                title="Hapus Jam"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        <div v-if="availableTimes.length === 0" class="text-center text-slate-400 py-4">
                            Belum ada jam pertemuan yang diatur.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end font-normal">
                        <button 
                            @click="showTimeSettingsModal = false" 
                            class="bg-slate-800 text-white text-[15px] font-normal py-2 px-4 rounded-md shadow-sm hover:bg-slate-700 transition-all flex items-center"
                        >
                            <i class="fa-solid fa-check mr-1.5"></i>Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADMIN EDIT BOOKING MODAL -->
        <div v-if="showBookingEditModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6 font-normal">
            <div class="bg-white border border-slate-200 shadow-xl rounded-md max-w-md w-full overflow-hidden flex flex-col font-normal">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center font-normal">
                    <h2 class="text-[17px] font-normal text-slate-900 flex items-center">
                        <i class="fa-solid fa-calendar-check mr-2 text-slate-800"></i>Edit Janji Temu
                    </h2>
                    <button @click="showBookingEditModal = false" class="text-slate-400 hover:text-slate-600 font-normal text-[15px] flex items-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="submitBookingEdit" class="p-6 space-y-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nama Lengkap</label>
                        <input 
                            v-model="bookingForm.name" 
                            type="text" 
                            required 
                            placeholder="Nama lengkap..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nomor Telepon / WhatsApp</label>
                        <input 
                            v-model="bookingForm.phone" 
                            type="text" 
                            required 
                            placeholder="Contoh: 081234567890..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Email (Opsional)</label>
                        <input 
                            v-model="bookingForm.email" 
                            type="email" 
                            placeholder="Alamat email..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Tanggal Kunjungan</label>
                            <input 
                                v-model="bookingForm.meeting_date" 
                                type="date" 
                                required 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Pertemuan</label>
                            <select 
                                v-model="bookingForm.meeting_time" 
                                required 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all font-normal"
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
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 transition-all resize-none font-normal"
                        ></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2 font-normal">
                        <button 
                            type="button" 
                            @click="showBookingEditModal = false" 
                            class="border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-50 text-[15px] font-normal py-2 px-4 rounded-md transition-all flex items-center"
                        >
                            <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="bookingForm.processing"
                            class="bg-slate-800 text-white text-[15px] font-normal py-2 px-4 rounded-md shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center"
                        >
                            <i class="fa-solid fa-check mr-1.5"></i>{{ bookingForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ADMIN ADD / EDIT CAR MODAL (Standard rounded-md, no none or full) -->
        <div v-if="showCarModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6 font-normal">
            <div class="bg-white border border-slate-200 shadow-xl rounded-md max-w-2xl w-full max-h-[90vh] overflow-y-auto flex flex-col font-normal">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center font-normal">
                    <h2 class="text-[18px] font-normal text-slate-900 flex items-center">
                        <i class="fa-solid fa-car mr-2 text-slate-800"></i>{{ editingCar ? 'Edit Detail Mobil' : 'Tambah Mobil Baru' }}
                    </h2>
                    <button @click="showCarModal = false" class="text-slate-400 hover:text-slate-600 font-normal text-[15px] flex items-center">
                        <i class="fa-solid fa-xmark mr-1"></i>Tutup
                    </button>
                </div>

                <form @submit.prevent="submitCarForm" class="p-6 space-y-4 font-normal">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Merk Mobil</label>
                            <input 
                                v-model="carForm.brand" 
                                type="text" 
                                required 
                                placeholder="Contoh: Toyota" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Model / Tipe</label>
                            <input 
                                v-model="carForm.model" 
                                type="text" 
                                required 
                                placeholder="Contoh: Fortuner GR Sport" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Tahun</label>
                            <input 
                                v-model="carForm.year" 
                                type="number" 
                                required 
                                placeholder="Contoh: 2022" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Harga (Rp)</label>
                            <input 
                                v-model="carForm.price" 
                                type="number" 
                                required 
                                placeholder="Contoh: 425000000" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Kilometer</label>
                            <input 
                                v-model="carForm.mileage" 
                                type="number" 
                                required 
                                placeholder="Contoh: 18000" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Transmisi</label>
                            <select 
                                v-model="carForm.transmission" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            >
                                <option value="manual">Manual</option>
                                <option value="otomatis">Otomatis</option>
                            </select>
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Bahan Bakar</label>
                            <select 
                                v-model="carForm.fuel" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            >
                                <option value="bensin">Bensin</option>
                                <option value="listrik">Listrik</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Kapasitas Mesin</label>
                            <input 
                                v-model="carForm.engine" 
                                type="text" 
                                required 
                                placeholder="Contoh: 2400cc VNT" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Warna Bodi</label>
                            <input 
                                v-model="carForm.color" 
                                type="text" 
                                required 
                                placeholder="Contoh: Putih Mutiara" 
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Unggah Gambar Mobil (Pilih minimal 4 gambar)</label>
                        <input 
                            @input="carForm.images = $event.target.files"
                            type="file" 
                            multiple
                            accept="image/*"
                            :required="!editingCar"
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                        />
                        <div v-if="carForm.images && carForm.images.length > 0" class="mt-2 text-[13px] text-slate-500">
                            Terpilih {{ carForm.images.length }} gambar.
                        </div>
                        <div v-if="editingCar && editingCar.image" class="mt-2 text-[13px] text-blue-500">
                            * Biarkan kosong jika tidak ingin mengubah gambar yang sudah ada.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Kondisi</label>
                            <select 
                                v-model="carForm.condition" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none font-normal"
                            >
                                <option value="baru">Baru</option>
                                <option value="bekas">Bekas</option>
                            </select>
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Status</label>
                            <select 
                                v-model="carForm.status" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none font-normal"
                            >
                                <option value="tersedia">Tersedia</option>
                                <option value="terjual">Terjual</option>
                                <option value="perbaikan">Perbaikan</option>
                            </select>
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Visibilitas Card</label>
                            <select 
                                v-model="carForm.is_active" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none font-normal"
                            >
                                <option :value="true">Aktif</option>
                                <option :value="false">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">No. Kontak Admin</label>
                            <input 
                                v-model="carForm.contact_phone" 
                                type="text" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none font-normal"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Status Terlaris</label>
                            <select 
                                v-model="carForm.is_terlaris" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none font-normal"
                            >
                                <option :value="true">Ya, Terlaris</option>
                                <option :value="false">Tidak</option>
                            </select>
                        </div>
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Status Unggulan</label>
                            <select 
                                v-model="carForm.is_unggulan" 
                                required
                                class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none font-normal"
                            >
                                <option :value="true">Ya, Unggulan</option>
                                <option :value="false">Tidak</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Deskripsi Lengkap Mobil</label>
                        <textarea 
                            v-model="carForm.description" 
                            rows="3" 
                            required
                            placeholder="Jelaskan detail riwayat service, kelengkapan berkas, kondisi ban, interior dll..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none resize-none font-normal"
                        ></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2 font-normal">
                        <button 
                            type="button" 
                            @click="showCarModal = false" 
                            class="border border-slate-200 text-slate-600 hover:bg-slate-50 text-[15px] font-normal py-2 px-4 rounded-md transition-all flex items-center"
                        >
                            <i class="fa-solid fa-xmark mr-1.5"></i>Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="carForm.processing"
                            class="bg-slate-800 text-white text-[15px] font-normal py-2 px-4 rounded-md shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center"
                        >
                            <i class="fa-solid fa-check mr-1.5"></i>{{ carForm.processing ? 'Menyimpan...' : 'Simpan Mobil' }}
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
                            {{ settings.footer_about || 'Bursa otomotif bekas terpercaya di Indonesia. Menyediakan kendaraan berkualitas tinggi dengan inspeksi ketat dan harga transparan.' }}
                        </p>
                        <div class="flex space-x-3 mt-auto">
                            <a v-if="settings.instagram_active !== '0'" :href="settings.instagram_link || '#'" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-instagram text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                            <a v-if="settings.facebook_active !== '0'" :href="settings.facebook_link || '#'" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-facebook-f text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                            <a v-if="settings.tiktok_active !== '0'" :href="settings.tiktok_link || '#'" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-tiktok text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                            <a v-if="settings.youtube_active !== '0'" :href="settings.youtube_link || '#'" class="w-10 h-10 rounded-none border border-slate-800 bg-slate-900/50 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-600 transition-all duration-300 group">
                                <i class="fa-brands fa-youtube text-[16px] group-hover:scale-110 transition-transform"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="flex flex-col">
                        <h4 class="text-[16px] font-bold text-white uppercase tracking-wider mb-6">Tautan Cepat</h4>
                        <ul class="space-y-3">
                            <li><button @click="activeTab = 'beranda'" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Beranda</button></li>
                            <li><button @click="activeTab = 'katalog'" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Katalog Mobil</button></li>
                            <li><button @click="activeTab = 'jual'" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Jual Mobil</button></li>
                            <li><button @click="activeTab = 'jadwal'" class="text-[15px] hover:text-white transition-colors duration-200 flex items-center group"><i class="fa-solid fa-chevron-right text-[10px] mr-3 text-slate-700 group-hover:text-slate-400 transition-colors"></i>Cek Jadwal</button></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div class="flex flex-col">
                        <h4 class="text-[16px] font-bold text-white uppercase tracking-wider mb-6">Hubungi Kami</h4>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <i class="fa-solid fa-location-dot mt-1 mr-4 text-slate-600"></i>
                                <span class="text-[15px] leading-relaxed">{{ settings.address || 'Jl. Otomotif Raya No. 123 Jakarta Selatan, DKI Jakarta 12345' }}</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fa-solid fa-phone mr-4 text-slate-600"></i>
                                <span class="text-[15px]">{{ settings.phone || '+62 812-3456-7890' }}</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fa-solid fa-envelope mr-4 text-slate-600"></i>
                                <span class="text-[15px]">{{ settings.email || 'info@rizkya-motor.com' }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Business Hours -->
                    <div class="flex flex-col">
                        <h4 class="text-[16px] font-bold text-white uppercase tracking-wider mb-6">Jam Operasional</h4>
                        <ul class="space-y-4">
                            <li class="flex justify-between items-center border-b border-slate-800/80 pb-3">
                                <span class="text-[15px]">Senin - Jumat</span>
                                <span class="text-[15px] text-slate-200 font-medium">{{ settings.opening_hours_weekday || '08:00 - 17:00' }}</span>
                            </li>
                            <li class="flex justify-between items-center border-b border-slate-800/80 pb-3">
                                <span class="text-[15px]">Sabtu</span>
                                <span class="text-[15px] text-slate-200 font-medium">{{ settings.opening_hours_saturday || '09:00 - 15:00' }}</span>
                            </li>
                            <li class="flex justify-between items-center border-b border-slate-800/80 pb-3">
                                <span class="text-[15px]">Minggu</span>
                                <span class="text-[15px] text-red-400 font-medium">{{ settings.opening_hours_sunday || 'Tutup' }}</span>
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
