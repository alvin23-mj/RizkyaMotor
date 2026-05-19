<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    testimonials: Array
});

const testimonialForm = useForm({
    name: '',
    role: '',
    avatar: null,
    rating: 5,
    message: '',
    is_active: true
});

const editingTestimonialId = ref(null);

const editTestimonial = (testimonial) => {
    editingTestimonialId.value = testimonial.id;
    testimonialForm.name = testimonial.name;
    testimonialForm.role = testimonial.role || '';
    testimonialForm.rating = testimonial.rating;
    testimonialForm.message = testimonial.message;
    testimonialForm.is_active = testimonial.is_active;
    testimonialForm.avatar = null; // Reset file input
};

const submitTestimonialForm = () => {
    if (editingTestimonialId.value) {
        testimonialForm.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('testimonials.update', editingTestimonialId.value), {
            onSuccess: () => {
                editingTestimonialId.value = null;
                testimonialForm.reset();
                alert('Testimoni berhasil diperbarui.');
            }
        });
    } else {
        testimonialForm.post(route('testimonials.store'), {
            onSuccess: () => {
                testimonialForm.reset();
                alert('Testimoni berhasil ditambahkan.');
            }
        });
    }
};

const deleteTestimonial = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus testimoni ini?')) {
        router.delete(route('testimonials.destroy', id), {
            onSuccess: () => {
                alert('Testimoni berhasil dihapus.');
            }
        });
    }
};
</script>

<template>
    <Head title="Kelola Testimoni - Rizkya Admin" />

    <AdminLayout activeTab="testimonials" title="Kelola Testimoni">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Form Tambah/Edit Testimoni -->
            <div class="bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal h-fit">
                <h2 class="text-[16px] font-normal text-slate-900 mb-4 flex items-center">
                    <i class="fa-solid fa-plus-circle mr-2 text-slate-800"></i>{{ editingTestimonialId ? 'Edit Testimoni' : 'Tambah Testimoni Baru' }}
                </h2>
                <form @submit.prevent="submitTestimonialForm" class="space-y-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Nama</label>
                        <input 
                            v-model="testimonialForm.name" 
                            type="text" 
                            required 
                            placeholder="Contoh: John Doe" 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" 
                        />
                        <div v-if="testimonialForm.errors.name" class="text-red-500 text-xs mt-1">{{ testimonialForm.errors.name }}</div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Peran / Instansi (Opsional)</label>
                        <input 
                            v-model="testimonialForm.role" 
                            type="text" 
                            placeholder="Contoh: Pembeli Setia, Pengusaha" 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" 
                        />
                        <div v-if="testimonialForm.errors.role" class="text-red-500 text-xs mt-1">{{ testimonialForm.errors.role }}</div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Rating</label>
                        <select 
                            v-model="testimonialForm.rating" 
                            required
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal"
                        >
                            <option :value="5">5 Bintang</option>
                            <option :value="4">4 Bintang</option>
                            <option :value="3">3 Bintang</option>
                            <option :value="2">2 Bintang</option>
                            <option :value="1">1 Bintang</option>
                        </select>
                        <div v-if="testimonialForm.errors.rating" class="text-red-500 text-xs mt-1">{{ testimonialForm.errors.rating }}</div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Pesan Testimoni</label>
                        <textarea 
                            v-model="testimonialForm.message" 
                            required 
                            rows="4"
                            placeholder="Tulis testimoni di sini..." 
                            class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" 
                        ></textarea>
                        <div v-if="testimonialForm.errors.message" class="text-red-500 text-xs mt-1">{{ testimonialForm.errors.message }}</div>
                    </div>

                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Foto / Avatar (Opsional)</label>
                        <input 
                            @input="testimonialForm.avatar = $event.target.files[0]" 
                            type="file" 
                            accept="image/*" 
                            class="text-[14px] text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[14px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" 
                        />
                        <div v-if="testimonialForm.errors.avatar" class="text-red-500 text-xs mt-1">{{ testimonialForm.errors.avatar }}</div>
                    </div>

                    <div class="flex items-center font-normal">
                        <input 
                            v-model="testimonialForm.is_active" 
                            type="checkbox" 
                            id="is_active"
                            class="rounded border-slate-300 text-slate-800 focus:ring-slate-500 h-4 w-4"
                        />
                        <label for="is_active" class="ml-2 text-[15px] font-normal text-slate-700">Tampilkan di Halaman Utama</label>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="testimonialForm.processing"
                        class="w-full bg-slate-800 hover:bg-slate-900 text-white font-normal py-2 px-4 rounded-md shadow-sm hover:shadow transition-all disabled:opacity-50 flex items-center justify-center"
                    >
                        <i class="fa-solid fa-check mr-1.5"></i>{{ testimonialForm.processing ? 'Menyimpan...' : (editingTestimonialId ? 'Perbarui Testimoni' : 'Simpan Testimoni') }}
                    </button>
                    <button 
                        v-if="editingTestimonialId"
                        type="button" 
                        @click="editingTestimonialId = null; testimonialForm.reset()"
                        class="w-full mt-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-normal py-2 px-4 rounded-md transition-all flex items-center justify-center"
                    >
                        <i class="fa-solid fa-xmark mr-1.5"></i>Batal Edit
                    </button>
                </form>
            </div>

            <!-- Daftar Testimoni -->
            <div class="col-span-2 bg-white border border-slate-200/80 shadow-sm rounded-md p-6 font-normal">
                <h2 class="text-[16px] font-normal text-slate-900 mb-4 flex items-center">
                    <i class="fa-solid fa-list mr-2 text-slate-800"></i>Daftar Testimoni
                </h2>
                
                <div class="overflow-x-auto font-normal">
                    <table class="w-full text-left text-[15px] text-slate-600 border-collapse font-normal">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-800 font-normal">
                                <th class="p-4">No</th>
                                <th class="p-4">Avatar</th>
                                <th class="p-4">Nama / Peran</th>
                                <th class="p-4">Pesan</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="testimonials.length === 0">
                                <td colspan="6" class="p-4 text-center text-slate-400">Belum ada testimoni.</td>
                            </tr>
                            <tr 
                                v-for="(testimonial, index) in testimonials" 
                                :key="testimonial.id" 
                                class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150 font-normal"
                            >
                                <td class="p-4 font-mono text-slate-500">{{ index + 1 }}</td>
                                <td class="p-4">
                                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center border border-slate-200 overflow-hidden">
                                        <img v-if="testimonial.avatar" :src="'/storage/' + testimonial.avatar" :alt="testimonial.name" class="w-full h-full object-cover" />
                                        <i v-else class="fa-solid fa-user text-slate-400"></i>
                                    </div>
                                </td>
                                <td class="p-4 font-normal">
                                    <div class="text-slate-900 font-medium">{{ testimonial.name }}</div>
                                    <div class="text-slate-500 text-xs">{{ testimonial.role }}</div>
                                    <div class="flex text-yellow-400 text-xs mt-1">
                                        <i v-for="i in testimonial.rating" :key="i" class="fa-solid fa-star"></i>
                                    </div>
                                </td>
                                <td class="p-4 font-normal text-slate-600 text-[14px]">
                                    <span class="line-clamp-3">{{ testimonial.message }}</span>
                                </td>
                                <td class="p-4">
                                    <span :class="[testimonial.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200']" class="text-xs px-2 py-1 rounded-full border">
                                        {{ testimonial.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center space-x-2 font-normal">
                                        <button 
                                            @click="editTestimonial(testimonial)" 
                                            class="border border-slate-200 hover:border-yellow-300 hover:bg-yellow-50 text-yellow-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                            title="Edit Testimoni"
                                        >
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                        </button>
                                        <button 
                                            @click="deleteTestimonial(testimonial.id)" 
                                            class="border border-slate-200 hover:border-red-300 hover:bg-red-50 text-red-600 p-2 rounded-md transition duration-200 text-[15px] font-normal flex items-center"
                                            title="Hapus Testimoni"
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
