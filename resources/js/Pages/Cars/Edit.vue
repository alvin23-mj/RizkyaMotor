<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    car: Object,
    brands: Array
});

const carForm = useForm({
    brand: props.car.brand,
    model: props.car.model,
    year: props.car.year,
    price: props.car.price,
    mileage: props.car.mileage,
    transmission: props.car.transmission,
    fuel: props.car.fuel,
    engine: props.car.engine,
    color: props.car.color,
    images: [],
    description: props.car.description,
    condition: props.car.condition,
    status: props.car.status,
    contact_phone: props.car.contact_phone || '081234567890',
    is_active: !!props.car.is_active,
    is_terlaris: !!props.car.is_terlaris,
    is_unggulan: !!props.car.is_unggulan,
    car_type: props.car.car_type || '',
    seating_capacity: props.car.seating_capacity || '',
    features: props.car.features || [],
});

const capitalizeWords = (text) => {
    if (!text) return '';
    return text.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const submitCarForm = () => {
    carForm.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('cars.update', props.car.id), {
        onSuccess: () => {
            alert('Informasi mobil berhasil diperbarui.');
        }
    });
};
</script>

<template>
    <Head title="Edit Mobil - Rizkya Admin" />

    <AdminLayout activeTab="kelola_mobil" :title="'Edit Mobil: ' + capitalizeWords(car.brand) + ' ' + capitalizeWords(car.model)">
                <div class="max-w-4xl bg-white border border-slate-200 rounded-md p-8 shadow-sm">
                    <form @submit.prevent="submitCarForm" class="space-y-6">
                        <!-- Brand & Model -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Merk Kendaraan</label>
                                <select v-model="carForm.brand" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option value="" disabled>Pilih Merk</option>
                                    <option v-for="brand in brands" :key="brand" :value="brand">{{ capitalizeWords(brand) }}</option>
                                </select>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Model / Tipe</label>
                                <input v-model="carForm.model" type="text" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                        </div>

                        <!-- Year, Price, Mileage -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Tahun</label>
                                <input v-model="carForm.year" type="number" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Harga (Rp)</label>
                                <input v-model="carForm.price" type="number" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Kilometer</label>
                                <input v-model="carForm.mileage" type="number" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                        </div>

                        <!-- Transmission & Fuel -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Transmisi</label>
                                <select v-model="carForm.transmission" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option value="manual">Manual</option>
                                    <option value="otomatis">Otomatis</option>
                                </select>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Bahan Bakar</label>
                                <select v-model="carForm.fuel" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option value="bensin">Bensin</option>
                                    <option value="listrik">Listrik</option>
                                </select>
                            </div>
                        </div>

                        <!-- Engine & Color -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Kapasitas Mesin</label>
                                <input v-model="carForm.engine" type="text" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Warna Bodi</label>
                                <input v-model="carForm.color" type="text" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                        </div>

                        <!-- Car Type & Seating Capacity -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Jenis Mobil</label>
                                <input v-model="carForm.car_type" type="text" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="Contoh: SUV, Sedan, MPV" />
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Kapasitas Tempat Duduk</label>
                                <input v-model="carForm.seating_capacity" type="number" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="Contoh: 5, 7" />
                            </div>
                        </div>

                        <!-- Condition, Status, Visibility -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Kondisi</label>
                                <select v-model="carForm.condition" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option value="baru">Baru</option>
                                    <option value="bekas">Bekas</option>
                                </select>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Status</label>
                                <select v-model="carForm.status" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option value="tersedia">Tersedia</option>
                                    <option value="terjual">Terjual</option>
                                    <option value="perbaikan">Perbaikan</option>
                                </select>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Visibilitas Card</label>
                                <select v-model="carForm.is_active" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option :value="true">Aktif</option>
                                    <option :value="false">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">No. Kontak Admin</label>
                                <input v-model="carForm.contact_phone" type="text" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" />
                            </div>
                        </div>

                        <!-- Status Terlaris & Unggulan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Status Terlaris</label>
                                <select v-model="carForm.is_terlaris" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option :value="true">Ya, Terlaris</option>
                                    <option :value="false">Tidak</option>
                                </select>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Status Unggulan</label>
                                <select v-model="carForm.is_unggulan" required class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal">
                                    <option :value="true">Ya, Unggulan</option>
                                    <option :value="false">Tidak</option>
                                </select>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Fitur Mobil</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 bg-slate-50 p-4 rounded-md border border-slate-200">
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="AC" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>AC</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Power Window" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Power Window</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Airbag" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Airbag</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Sensor Parkir" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Sensor Parkir</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Kamera Mundur" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Kamera Mundur</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Sunroof" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Sunroof</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Keyless Entry" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Keyless Entry</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Bluetooth" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Bluetooth</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Radio Tape" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Radio Tape</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Power Steering" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Power Steering</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Central Lock" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Central Lock</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Alarm" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Alarm</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Kaca Film" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Kaca Film</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Terpal (Pickup)" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Terpal (Pickup)</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Bumper Guard" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Bumper Guard</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Fog Lamp" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Fog Lamp</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Velg Racing" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Velg Racing</span>
                                </label>
                                <label class="flex items-center space-x-2 text-[14px] text-slate-700">
                                    <input type="checkbox" v-model="carForm.features" value="Ban Serep" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <span>Ban Serep</span>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Deskripsi Lengkap Mobil</label>
                            <textarea v-model="carForm.description" rows="5" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal resize-none"></textarea>
                        </div>

                        <!-- Images -->
                        <div class="flex flex-col font-normal">
                            <label class="text-[15px] font-normal text-slate-500 mb-2">Ganti Gambar (Opsional, minimal 4 jika ganti)</label>
                            <input @input="carForm.images = $event.target.files" type="file" multiple accept="image/*" class="text-[14px] text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[14px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-slate-100 font-normal">
                            <Link :href="route('cars.index')" class="bg-white border border-slate-200 hover:border-slate-400 text-slate-700 px-5 py-2 rounded-md font-medium text-[14px] transition shadow-sm">
                                Batal
                            </Link>
                            <button type="submit" :disabled="carForm.processing" class="bg-blue-600 text-white hover:bg-blue-700 px-5 py-2 rounded-md font-medium text-[14px] transition shadow-sm disabled:opacity-50">
                                {{ carForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>
    </AdminLayout>
</template>
