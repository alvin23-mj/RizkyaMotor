<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    partners: Array
});

const partnerForm = useForm({
    name: '',
    position: '',
    image: null
});

const editingPartnerId = ref(null);

const editPartner = (partner) => {
    editingPartnerId.value = partner.id;
    partnerForm.name = partner.name;
    partnerForm.position = partner.position || '';
    partnerForm.image = null;
};

const submitPartnerForm = () => {
    if (editingPartnerId.value) {
        partnerForm.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('partners.update', editingPartnerId.value), {
            onSuccess: () => {
                editingPartnerId.value = null;
                partnerForm.reset();
                alert('Mitra berhasil diperbarui.');
            }
        });
    } else {
        partnerForm.post(route('partners.store'), {
            onSuccess: () => {
                partnerForm.reset();
                alert('Mitra berhasil ditambahkan.');
            }
        });
    }
};

const deletePartner = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus mitra ini?')) {
        router.delete(route('partners.destroy', id), {
            onSuccess: () => {
                alert('Mitra berhasil dihapus.');
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
    <Head title="Kelola Mitra - Rizkya Admin" />

    <AdminLayout activeTab="partners" title="Kelola Mitra">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Form Tambah Mitra -->
                    <div class="bg-white border border-slate-200/80 shadow-sm rounded-none p-6 font-normal h-fit">
                        <h2 class="text-[16px] font-normal text-slate-900 mb-4 flex items-center">
                            <i class="fa-solid fa-plus-circle mr-2 text-slate-800"></i>{{ editingPartnerId ? 'Edit Mitra' : 'Tambah Mitra Baru' }}
                        </h2>
                        <form @submit.prevent="submitPartnerForm" class="space-y-4 font-normal">
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Nama Mitra</label>
                                <input 
                                    v-model="partnerForm.name" 
                                    type="text" 
                                    required 
                                    placeholder="Contoh: Nama Mitra..." 
                                    class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" 
                                />
                                <div v-if="partnerForm.errors.name" class="text-red-500 text-xs mt-1">{{ partnerForm.errors.name }}</div>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Jabatan / Peran</label>
                                <input 
                                    v-model="partnerForm.position" 
                                    type="text" 
                                    placeholder="Contoh: Kepala Mekanik, Manajer..." 
                                    class="bg-slate-50 border border-slate-200 rounded-none px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" 
                                />
                                <div v-if="partnerForm.errors.position" class="text-red-500 text-xs mt-1">{{ partnerForm.errors.position }}</div>
                            </div>
                            <div class="flex flex-col font-normal">
                                <label class="text-[15px] font-normal text-slate-500 mb-2">Foto / Logo Mitra</label>
                                <input 
                                    @input="partnerForm.image = $event.target.files[0]" 
                                    type="file" 
                                    accept="image/*" 
                                    class="text-[14px] text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-none file:border-0 file:text-[14px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" 
                                />
                                <div v-if="partnerForm.errors.image" class="text-red-500 text-xs mt-1">{{ partnerForm.errors.image }}</div>
                            </div>
                            <button 
                                type="submit" 
                                :disabled="partnerForm.processing"
                                class="w-full bg-slate-800 hover:bg-slate-900 text-white font-normal py-2 px-4 rounded-none shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center justify-center"
                            >
                                <i class="fa-solid fa-check mr-1.5"></i>{{ partnerForm.processing ? 'Menyimpan...' : (editingPartnerId ? 'Perbarui Mitra' : 'Simpan Mitra') }}
                            </button>
                            <button 
                                v-if="editingPartnerId"
                                type="button" 
                                @click="editingPartnerId = null; partnerForm.reset()"
                                class="w-full mt-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-normal py-2 px-4 rounded-none transition-all flex items-center justify-center"
                            >
                                <i class="fa-solid fa-xmark mr-1.5"></i>Batal Edit
                            </button>
                        </form>
                    </div>

                    <!-- Daftar Mitra -->
                    <div class="col-span-2 bg-white border border-slate-200/80 shadow-sm rounded-none p-6 font-normal">
                        <h2 class="text-[16px] font-normal text-slate-900 mb-4 flex items-center">
                            <i class="fa-solid fa-list mr-2 text-slate-800"></i>Daftar Mitra
                        </h2>
                        
                        <div class="overflow-x-auto font-normal">
                            <table class="w-full text-left text-[15px] text-slate-600 border-collapse font-normal">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-800 font-normal">
                                        <th class="p-4">No</th>
                                        <th class="p-4">Foto</th>
                                        <th class="p-4">Nama Mitra</th>
                                        <th class="p-4">Jabatan</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="partners.length === 0">
                                        <td colspan="4" class="p-4 text-center text-slate-400">Belum ada mitra terdaftar.</td>
                                    </tr>
                                    <tr 
                                        v-for="(partner, index) in partners" 
                                        :key="partner.id" 
                                        class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150 font-normal"
                                    >
                                        <td class="p-4 font-mono text-slate-500">{{ index + 1 }}</td>
                                        <td class="p-4">
                                            <img v-if="partner.image" :src="'/storage/' + partner.image" :alt="partner.name" class="w-10 h-10 object-cover rounded-full border border-slate-200" />
                                            <div v-else class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                        </td>
                                        <td class="p-4 font-normal text-slate-900">{{ capitalizeWords(partner.name) }}</td>
                                        <td class="p-4 font-normal text-slate-600">{{ partner.position || '-' }}</td>
                                        <td class="p-4">
                                            <div class="flex items-center justify-center space-x-2 font-normal">
                                                <button 
                                                    @click="editPartner(partner)" 
                                                    class="border border-slate-200 hover:border-yellow-300 hover:bg-yellow-50 text-yellow-600 p-2 rounded-none transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Edit Mitra"
                                                >
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </button>
                                                <button 
                                                    @click="deletePartner(partner.id)" 
                                                    class="border border-slate-200 hover:border-red-300 hover:bg-red-50 text-red-600 p-2 rounded-none transition duration-200 text-[15px] font-normal flex items-center"
                                                    title="Hapus Mitra"
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
