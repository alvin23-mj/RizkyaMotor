<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    brands: Array
});

const brandForm = useForm({
    name: '',
    image: null
});

const editingBrandId = ref(null);

const editBrand = (brand) => {
    editingBrandId.value = brand.id;
    brandForm.name = brand.name;
    brandForm.image = null;
};

const submitBrandForm = () => {
    if (editingBrandId.value) {
        brandForm.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('brands.update', editingBrandId.value), {
            onSuccess: () => {
                editingBrandId.value = null;
                brandForm.reset();
                alert('Merek berhasil diperbarui.');
            }
        });
    } else {
        brandForm.post(route('brands.store'), {
            onSuccess: () => {
                brandForm.reset();
                alert('Merek berhasil ditambahkan.');
            }
        });
    }
};

const deleteBrand = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus merek ini?')) {
        router.delete(route('brands.destroy', id), {
            onSuccess: () => {
                alert('Merek berhasil dihapus.');
            }
        });
    }
};

const capitalizeWords = (text) => {
    if (!text) return '';
    return text.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="Kelola Merek - Rizkya Admin" />

    <AdminLayout activeTab="brands" title="Kelola Merek Mobil">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Form Tambah Merek -->
                    <div class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal h-fit">
                        <h2 class="text-[16px] font-normal text-slate-900 mb-4 flex items-center">
                            <i class="fa-solid fa-plus-circle mr-2 text-slate-800"></i>{{ editingBrandId ? 'Edit Merek' : 'Tambah Merek Baru' }}
                        </h2>
                        <form @submit.prevent="submitBrandForm" class="space-y-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Nama Merek</label>
                                <input 
                                    v-model="brandForm.name" 
                                    type="text" 
                                    required 
                                    placeholder="Contoh: Toyota, Honda..." 
                                    class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" 
                                />
                                <div v-if="brandForm.errors.name" class="text-red-500 text-xs mt-1">{{ brandForm.errors.name }}</div>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Logo Merek</label>
                                <input 
                                    @input="brandForm.image = $event.target.files[0]" 
                                    type="file" 
                                    accept="image/*" 
                                    class="text-[14px] text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[14px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" 
                                />
                                <div v-if="brandForm.errors.image" class="text-red-500 text-xs mt-1">{{ brandForm.errors.image }}</div>
                            </div>
                            <button 
                                type="submit" 
                                :disabled="brandForm.processing"
                                class="w-full bg-slate-800 hover:bg-slate-900 text-white font-normal py-2 px-4 rounded-md shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center justify-center"
                            >
                                <i class="fa-solid fa-check mr-1.5"></i>{{ brandForm.processing ? 'Menyimpan...' : (editingBrandId ? 'Perbarui Merek' : 'Simpan Merek') }}
                            </button>
                            <button 
                                v-if="editingBrandId"
                                type="button" 
                                @click="editingBrandId = null; brandForm.reset()"
                                class="w-full mt-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-normal py-2 px-4 rounded-md transition-all flex items-center justify-center"
                            >
                                <i class="fa-solid fa-xmark mr-1.5"></i>Batal Edit
                            </button>
                        </form>
                    </div>

                    <!-- Daftar Merek -->
                    <div class="col-span-2 bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal">
                        <h2 class="text-[16px] font-normal text-slate-900 mb-4 flex items-center">
                            <i class="fa-solid fa-list mr-2 text-slate-800"></i>Daftar Merek Terdaftar
                        </h2>
                        
                        <div class="overflow-x-auto font-normal">
                            <table class="w-full text-left text-[15px] text-slate-600 border-collapse font-normal">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-800 font-normal">
                                        <th class="p-4">No</th>
                                        <th class="p-4">Logo</th>
                                        <th class="p-4">Nama Merek</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="brands.length === 0">
                                        <td colspan="4" class="p-4 text-center text-slate-400">Belum ada merek terdaftar.</td>
                                    </tr>
                                    <tr 
                                        v-for="(brand, index) in brands" 
                                        :key="brand.id" 
                                        class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150 font-normal"
                                    >
                                        <td class="p-4 font-mono text-slate-500">{{ index + 1 }}</td>
                                        <td class="p-4">
                                            <img v-if="brand.image" :src="'/storage/' + brand.image" :alt="brand.name" class="w-12 h-8 object-contain rounded border border-slate-200" />
                                            <span v-else class="text-slate-400">-</span>
                                        </td>
                                        <td class="p-4 font-normal text-slate-900">{{ capitalizeWords(brand.name) }}</td>
                                        <td class="p-4">
                                            <div class="flex items-center justify-center space-x-2 font-normal">
                                                <button 
                                                    @click="editBrand(brand)" 
                                                    class="border border-slate-200 hover:border-yellow-300 hover:bg-yellow-50 text-yellow-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Edit Merek"
                                                >
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </button>
                                                <button 
                                                    @click="deleteBrand(brand.id)" 
                                                    class="border border-slate-200 hover:border-red-300 hover:bg-red-50 text-red-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Hapus Merek"
                                                >
                                                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
    </AdminLayout>
</template>
