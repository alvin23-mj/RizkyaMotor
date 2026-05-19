<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object
});

const form = useForm({
    facebook_link: props.settings.facebook_link || '',
    instagram_link: props.settings.instagram_link || '',
    tiktok_link: props.settings.tiktok_link || '',
    youtube_link: props.settings.youtube_link || '',
    whatsapp_number: props.settings.whatsapp_number || '',
    coordinates: props.settings.coordinates || '',
    footer_about: props.settings.footer_about || '',
    address: props.settings.address || '',
    email: props.settings.email || '',
    phone: props.settings.phone || '',
    opening_hours_weekday: props.settings.opening_hours_weekday || '',
    opening_hours_saturday: props.settings.opening_hours_saturday || '',
    opening_hours_sunday: props.settings.opening_hours_sunday || '',
    facebook_active: props.settings.facebook_active || '1',
    instagram_active: props.settings.instagram_active || '1',
    tiktok_active: props.settings.tiktok_active || '1',
    youtube_active: props.settings.youtube_active || '1',
    logo: null
});

const submitForm = () => {
    form.post(route('settings.update'), {
        onSuccess: () => {
            alert('Pengaturan berhasil diperbarui.');
        }
    });
};
</script>

<template>
    <Head title="Pengaturan Website - Rizkya Admin" />

    <AdminLayout activeTab="settings" title="Pengaturan Website">
        <div class="max-w-4xl bg-white border border-slate-200 rounded-md p-8 shadow-sm">
            <form @submit.prevent="submitForm" class="space-y-6 font-normal">
                <!-- Social Media -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[15px] font-normal text-slate-500">Facebook Link</label>
                            <label class="flex items-center space-x-2 text-[13px] text-slate-600">
                                <input type="checkbox" v-model="form.facebook_active" true-value="1" false-value="0" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span>Aktif</span>
                            </label>
                        </div>
                        <input v-model="form.facebook_link" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="https://facebook.com/..." />
                    </div>
                    <div class="flex flex-col font-normal">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[15px] font-normal text-slate-500">Instagram Link</label>
                            <label class="flex items-center space-x-2 text-[13px] text-slate-600">
                                <input type="checkbox" v-model="form.instagram_active" true-value="1" false-value="0" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span>Aktif</span>
                            </label>
                        </div>
                        <input v-model="form.instagram_link" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="https://instagram.com/..." />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[15px] font-normal text-slate-500">Tiktok Link</label>
                            <label class="flex items-center space-x-2 text-[13px] text-slate-600">
                                <input type="checkbox" v-model="form.tiktok_active" true-value="1" false-value="0" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span>Aktif</span>
                            </label>
                        </div>
                        <input v-model="form.tiktok_link" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="https://tiktok.com/@..." />
                    </div>
                    <div class="flex flex-col font-normal">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[15px] font-normal text-slate-500">YouTube Link</label>
                            <label class="flex items-center space-x-2 text-[13px] text-slate-600">
                                <input type="checkbox" v-model="form.youtube_active" true-value="1" false-value="0" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span>Aktif</span>
                            </label>
                        </div>
                        <input v-model="form.youtube_link" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="https://youtube.com/..." />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">WhatsApp Number</label>
                        <input v-model="form.whatsapp_number" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="081234567890" />
                    </div>
                </div>

                <!-- Coordinates & Logo -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Koordinat Alamat (Maps)</label>
                        <input v-model="form.coordinates" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="-6.200000, 106.816666" />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Logo Website</label>
                        <input @input="form.logo = $event.target.files[0]" type="file" accept="image/*" class="text-[14px] text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[14px] file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="flex flex-col font-normal">
                    <label class="text-[15px] font-normal text-slate-500 mb-2">Alamat Lengkap</label>
                    <textarea v-model="form.address" rows="2" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal resize-none" placeholder="Jl. Otomotif Raya No. 123..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Email</label>
                        <input v-model="form.email" type="email" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="info@example.com" />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Telepon</label>
                        <input v-model="form.phone" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="+62 812-..." />
                    </div>
                </div>

                <!-- Opening Hours -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-normal">
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Kerja (Senin - Jumat)</label>
                        <input v-model="form.opening_hours_weekday" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="08:00 - 17:00" />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Kerja (Sabtu)</label>
                        <input v-model="form.opening_hours_saturday" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="09:00 - 15:00" />
                    </div>
                    <div class="flex flex-col font-normal">
                        <label class="text-[15px] font-normal text-slate-500 mb-2">Jam Kerja (Minggu)</label>
                        <input v-model="form.opening_hours_sunday" type="text" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal" placeholder="Tutup" />
                    </div>
                </div>

                <!-- Footer About -->
                <div class="flex flex-col font-normal">
                    <label class="text-[15px] font-normal text-slate-500 mb-2">Tentang Website (Footer)</label>
                    <textarea v-model="form.footer_about" rows="4" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-[15px] text-slate-800 focus:outline-none focus:border-slate-500 focus:ring-1 font-normal resize-none" placeholder="Tuliskan deskripsi singkat untuk footer..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-6 border-t border-slate-100 font-normal">
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 text-white hover:bg-blue-700 px-5 py-2 rounded-md font-medium text-[14px] transition shadow-sm disabled:opacity-50">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
