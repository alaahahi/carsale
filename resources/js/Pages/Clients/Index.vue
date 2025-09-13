<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import { ref, onMounted, computed } from 'vue';
import { TailwindPagination } from 'laravel-vue-pagination';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    url: String,
    cards: Array
});

const form = useForm();

// البيانات التفاعلية
const clientsData = ref([]);
const stats = ref({
    total_clients: 0,
    total_required: 0,
    total_paid: 0,
    total_debt: 0,
    clients_with_debt: 0,
    clients_paid_off: 0
});
const loading = ref(false);

// بيانات النافذة المنبثقة للتعديل
const isEditModalVisible = ref(false);
const editForm = ref({
    id: null,
    name: '',
    phone: ''
});
const editLoading = ref(false);

// بيانات النافذة المنبثقة للإضافة
const isAddModalVisible = ref(false);
const addForm = ref({
    name: '',
    phone: ''
});
const addLoading = ref(false);

// جلب بيانات العملاء
const getClients = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/getIndexClients');
        clientsData.value = response.data.data;
        stats.value = response.data.stats;
    } catch (error) {
        toast.error('حدث خطأ في جلب بيانات العملاء');
        console.error('Error fetching clients:', error);
    } finally {
        loading.value = false;
    }
};

// حذف عميل
const destroy = (id) => {
    if (confirm('هل أنت متأكد من حذف هذا العميل؟')) {
        form.delete(route('users.destroy', id), {
            onSuccess: () => {
                toast.success('تم حذف العميل بنجاح');
                getClients();
            },
            onError: () => {
                toast.error('حدث خطأ في حذف العميل');
            }
        });
    }
};

// حظر عميل
const ban = (id) => {
    if (confirm('هل أنت متأكد من حظر هذا العميل؟')) {
        form.get(route('ban', id), {
            onSuccess: () => {
                toast.success('تم حظر العميل بنجاح');
                getClients();
            },
            onError: () => {
                toast.error('حدث خطأ في حظر العميل');
            }
        });
    }
};

// إلغاء حظر عميل
const unban = (id) => {
    if (confirm('هل أنت متأكد من إلغاء حظر هذا العميل؟')) {
        form.get(route('unban', id), {
            onSuccess: () => {
                toast.success('تم إلغاء حظر العميل بنجاح');
                getClients();
            },
            onError: () => {
                toast.error('حدث خطأ في إلغاء حظر العميل');
            }
        });
    }
};

// فتح نافذة تعديل العميل
const showClientEditModal = (client) => {
    editForm.value = {
        id: client.id,
        name: client.name || '',
        phone: client.phone || ''
    };
    isEditModalVisible.value = true;
};

// إغلاق نافذة تعديل العميل
const hideClientEditModal = () => {
    console.log('Closing edit modal');
    isEditModalVisible.value = false;
    editForm.value = {
        id: null,
        name: '',
        phone: ''
    };
};

// حفظ تحديثات العميل
const saveClientUpdates = async () => {
    console.log('Saving client updates:', editForm.value);
    
    if (!editForm.value.name || !editForm.value.name.trim()) {
        toast.error('الاسم مطلوب');
        return;
    }

    if (!editForm.value.id) {
        toast.error('خطأ في معرف العميل');
        return;
    }

    editLoading.value = true;
    try {
        const response = await axios.put(`/api/clients/${editForm.value.id}/update`, {
            name: editForm.value.name.trim(),
            phone: editForm.value.phone ? editForm.value.phone.trim() : ''
        });

        console.log('Update response:', response.data);

        if (response.data && response.data.success) {
            toast.success(response.data.message || 'تم تحديث بيانات العميل بنجاح');
            hideClientEditModal();
            await getClients(); // إعادة تحميل البيانات
        } else {
            toast.error(response.data?.message || 'حدث خطأ في تحديث البيانات');
        }
    } catch (error) {
        console.error('Error updating client:', error);
        toast.error('حدث خطأ في تحديث بيانات العميل');
        if (error.response?.data?.message) {
            toast.error(error.response.data.message);
        }
    } finally {
        editLoading.value = false;
    }
};

// فتح نافذة إضافة العميل
const showAddClientModal = () => {
    addForm.value = {
        name: '',
        phone: ''
    };
    isAddModalVisible.value = true;
};

// إغلاق نافذة إضافة العميل
const hideAddClientModal = () => {
    isAddModalVisible.value = false;
    addForm.value = {
        name: '',
        phone: ''
    };
};

// حفظ العميل الجديد
const saveNewClient = async () => {
    console.log('Saving new client:', addForm.value);
    
    if (!addForm.value.name || !addForm.value.name.trim()) {
        toast.error('الاسم مطلوب');
        return;
    }

    addLoading.value = true;
    try {
        const response = await axios.post('/api/clients/store', {
            name: addForm.value.name.trim(),
            phone: addForm.value.phone ? addForm.value.phone.trim() : ''
        });

        console.log('Add response:', response.data);

        if (response.data && response.data.success) {
            toast.success(response.data.message || 'تم إضافة العميل بنجاح');
            hideAddClientModal();
            await getClients(); // إعادة تحميل البيانات
        } else {
            toast.error(response.data?.message || 'حدث خطأ في إضافة العميل');
        }
    } catch (error) {
        console.error('Error adding client:', error);
        toast.error('حدث خطأ في إضافة العميل');
        if (error.response?.data?.message) {
            toast.error(error.response.data.message);
        }
    } finally {
        addLoading.value = false;
    }
};

// تنسيق الأرقام
const formatNumber = (number) => {
    return Math.round(number || 0).toLocaleString();
};

// تحديد لون الدين
const getDebtColor = (debt) => {
    if (debt > 0) return 'text-red-600';
    if (debt < 0) return 'text-green-600';
    return 'text-gray-600';
};

// تحديد حالة العميل
const getClientStatus = (debt) => {
    if (debt > 0) return { text: 'مدين', class: 'bg-red-100 text-red-800' };
    if (debt < 0) return { text: 'له رصيد', class: 'bg-green-100 text-green-800' };
    return { text: 'مكتمل', class: 'bg-gray-100 text-gray-800' };
};

// تحميل البيانات عند بدء الصفحة
onMounted(() => {
    getClients();
});
</script>

<template>
    <Head title="إدارة العملاء" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
                    إدارة العملاء
                </h2>
                <button
                    @click="showAddClientModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    إضافة عميل جديد
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- إحصائيات إجمالية -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
                    <!-- إجمالي العملاء -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">إجمالي العملاء</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stats.total_clients }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إجمالي المطلوب -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">إجمالي المطلوب</p>
                                    <p class="text-2xl font-semibold text-gray-900">${{ formatNumber(stats.total_required) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إجمالي المدفوع -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">إجمالي المدفوع</p>
                                    <p class="text-2xl font-semibold text-gray-900">${{ formatNumber(stats.total_paid) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إجمالي الدين -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">إجمالي الدين</p>
                                    <p class="text-2xl font-semibold" :class="getDebtColor(stats.total_debt)">${{ formatNumber(stats.total_debt) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- عملاء مدينون -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">عملاء مدينون</p>
                                    <p class="text-2xl font-semibold text-red-600">{{ stats.clients_with_debt }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- عملاء مكتملون -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">عملاء مكتملون</p>
                                    <p class="text-2xl font-semibold text-green-600">{{ stats.clients_paid_off }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول العملاء -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">قائمة العملاء</h3>
                            <button 
                                @click="getClients"
                                :disabled="loading"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center gap-2 disabled:opacity-50">
                                <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                تحديث
                            </button>
                        </div>

                        <div v-if="loading" class="flex justify-center items-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>

                        <div v-else-if="clientsData.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">لا يوجد عملاء</h3>
                            <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة عميل جديد.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الهاتف</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المطلوب</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدفوع</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المتبقي</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العمليات</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="client in clientsData" :key="client.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ client.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ client.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ client.phone || 'غير محدد' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ formatNumber(client.total_required) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ formatNumber(client.total_paid) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="getDebtColor(client.remaining_debt)">
                                            ${{ formatNumber(client.remaining_debt) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getClientStatus(client.remaining_debt).class">
                                                {{ getClientStatus(client.remaining_debt).text }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    v-if="client.email !== 'admin@admin.com'"
                                                    @click="showClientEditModal(client)"
                                                    class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded-md bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
                                                    تعديل
                                                </button>
                                                <button
                                                    v-if="client.email !== 'admin@admin.com'"
                                                    @click="destroy(client.id)"
                                                    class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md bg-red-50 hover:bg-red-100 transition-colors duration-200">
                                                    حذف
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نافذة تعديل العميل -->
        <Modal :show="isEditModalVisible" @close="hideClientEditModal">
            <div class="bg-white px-6 py-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-semibold text-gray-900">تعديل بيانات العميل</h3>
                            <p class="text-sm text-gray-500">قم بتحديث المعلومات المطلوبة</p>
                        </div>
                    </div>
                    <button @click="hideClientEditModal" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="saveClientUpdates" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- الاسم -->
                        <div>
                            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2 text-right">
                                <span class="text-red-500">*</span> الاسم
                            </label>
                            <input
                                type="text"
                                id="client_name"
                                v-model="editForm.name"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-lg"
                                placeholder="أدخل اسم العميل">
                        </div>

                        <!-- رقم الهاتف -->
                        <div>
                            <label for="client_phone" class="block text-sm font-medium text-gray-700 mb-2 text-right">
                                رقم الهاتف
                            </label>
                            <input
                                type="text"
                                id="client_phone"
                                v-model="editForm.phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-lg"
                                placeholder="أدخل رقم الهاتف">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            @click="hideClientEditModal"
                            class="px-6 py-3 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium">
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="editLoading"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 font-medium">
                            <svg v-if="editLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ editLoading ? 'جاري التحديث...' : 'تحديث البيانات' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- نافذة إضافة العميل -->
        <Modal :show="isAddModalVisible" @close="hideAddClientModal">
            <div class="bg-white px-6 py-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-semibold text-gray-900">إضافة عميل جديد</h3>
                            <p class="text-sm text-gray-500">أدخل معلومات العميل الجديد</p>
                        </div>
                    </div>
                    <button @click="hideAddClientModal" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="saveNewClient" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- الاسم -->
                        <div>
                            <label for="add_client_name" class="block text-sm font-medium text-gray-700 mb-2 text-right">
                                <span class="text-red-500">*</span> الاسم
                            </label>
                            <input
                                type="text"
                                id="add_client_name"
                                v-model="addForm.name"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 text-lg"
                                placeholder="أدخل اسم العميل">
                        </div>

                        <!-- رقم الهاتف -->
                        <div>
                            <label for="add_client_phone" class="block text-sm font-medium text-gray-700 mb-2 text-right">
                                رقم الهاتف
                            </label>
                            <input
                                type="text"
                                id="add_client_phone"
                                v-model="addForm.phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 text-lg"
                                placeholder="أدخل رقم الهاتف">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            @click="hideAddClientModal"
                            class="px-6 py-3 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium">
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="addLoading"
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 font-medium">
                            <svg v-if="addLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ addLoading ? 'جاري الإضافة...' : 'إضافة العميل' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>