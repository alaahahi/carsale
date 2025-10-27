<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    url: String
});

const toast = useToast();
const fileInput = ref(null);
const isUploading = ref(false);
const uploadProgress = ref(0);
const importResults = ref(null);

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        // التحقق من نوع الملف
        const validExtensions = ['xlsx', 'xls', 'csv'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!validExtensions.includes(fileExtension)) {
            toast.error('نوع الملف غير مدعوم. يرجى استخدام ملف Excel (.xlsx, .xls) أو CSV');
            event.target.value = '';
            return;
        }
        
        // التحقق من حجم الملف (مثلاً 10MB)
        if (file.size > 10 * 1024 * 1024) {
            toast.error('حجم الملف كبير جداً. الحد الأقصى 10MB');
            event.target.value = '';
            return;
        }
    }
};

const uploadFile = async () => {
    if (!fileInput.value || !fileInput.value.files[0]) {
        toast.error('يرجى اختيار ملف للاستيراد');
        return;
    }

    const file = fileInput.value.files[0];
    const formData = new FormData();
    formData.append('file', file);

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const response = await axios.post(route('car.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                }
            },
        });

        isUploading.value = false;
        importResults.value = response.data;

        if (response.data.success) {
            toast.success(response.data.message);
            
            // عرض نتائج الاستيراد
            if (response.data.errors && response.data.errors.length > 0) {
                console.warn('Import completed with errors:', response.data.errors);
            }
        } else {
            toast.error(response.data.message);
        }

    } catch (error) {
        isUploading.value = false;
        
        if (error.response) {
            const response = error.response.data;
            toast.error(response.message || 'حدث خطأ أثناء الاستيراد');
            
            importResults.value = {
                success: false,
                message: response.message,
                errors: response.errors || [],
                success_count: response.success_count || 0,
                error_count: response.error_count || 0,
                skip_count: response.skip_count || 0
            };
        } else {
            toast.error('حدث خطأ في الاتصال بالخادم');
        }
    }
};

const downloadTemplate = () => {
    // يمكن إضافة ملف قالب للتنزيل
    toast.info('سيتم إضافة ملف القالب قريباً');
};

const clearResults = () => {
    importResults.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <Head title="استيراد السيارات" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
                        استيراد السيارات من Excel
                    </h2>

                    <!-- تعليمات الاستيراد -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                            معلومات هامة
                        </h3>
                        <ul class="list-disc list-inside space-y-2 text-sm text-blue-800 dark:text-blue-200">
                            <li>يرجى التأكد من أن الملف بصيغة Excel (.xlsx, .xls) أو CSV</li>
                            <li>الصف الأول يجب أن يحتوي على العناوين</li>
                            <li>العمود الثاني (PIN) مطلوب ولا يمكن أن يكون فارغاً</li>
                            <li>سيتم تحديث السيارات الموجودة إذا كان رقم PIN متطابقاً</li>
                            <li>العملية تستخدم نظام Rollback في حال الفشل</li>
                        </ul>
                    </div>

                    <!-- نموذج الاستيراد -->
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 mb-6">
                        <div class="mb-4">
                            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                اختر ملف Excel
                            </label>
                            <input
                                ref="fileInput"
                                type="file"
                                id="file"
                                accept=".xlsx,.xls,.csv"
                                @change="handleFileSelect"
                                class="block w-full text-sm text-gray-500
                                       file:mr-4 file:py-2 file:px-4
                                       file:rounded-full file:border-0
                                       file:text-sm file:font-semibold
                                       file:bg-blue-50 file:text-blue-700
                                       hover:file:bg-blue-100
                                       dark:file:bg-blue-900 dark:file:text-blue-300"
                            />
                        </div>

                        <!-- شريط التقدم -->
                        <div v-if="isUploading" class="mb-4">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div
                                    class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                    :style="{ width: uploadProgress + '%' }"
                                ></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 text-center">
                                جاري الاستيراد... {{ uploadProgress }}%
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <button
                                @click="uploadFile"
                                :disabled="isUploading"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 
                                       disabled:bg-gray-400 disabled:cursor-not-allowed
                                       dark:bg-blue-500 dark:hover:bg-blue-600"
                            >
                                {{ isUploading ? 'جاري الاستيراد...' : 'بدء الاستيراد' }}
                            </button>

                            <button
                                @click="downloadTemplate"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300
                                       dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                تحميل ملف القالب
                            </button>

                            <button
                                v-if="importResults"
                                @click="clearResults"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300
                                       dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                مسح النتائج
                            </button>
                        </div>
                    </div>

                    <!-- نتائج الاستيراد -->
                    <div v-if="importResults" class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            نتائج الاستيراد
                        </h3>

                        <!-- حالة النجاح -->
                        <div v-if="importResults.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-green-800 dark:text-green-200 font-medium mb-2">
                                        {{ importResults.message }}
                                    </p>
                                    <div class="grid grid-cols-3 gap-4 text-sm text-green-700 dark:text-green-300">
                                        <div>
                                            <span class="font-semibold">نجح:</span>
                                            {{ importResults.success_count }} سيارة
                                        </div>
                                        <div v-if="importResults.error_count > 0">
                                            <span class="font-semibold">فشل:</span>
                                            {{ importResults.error_count }} سطر
                                        </div>
                                        <div v-if="importResults.skip_count > 0">
                                            <span class="font-semibold">تم التخطي:</span>
                                            {{ importResults.skip_count }} سطر
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- حالة الفشل -->
                        <div v-else class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-red-800 dark:text-red-200 font-medium mb-2">
                                        {{ importResults.message }}
                                    </p>
                                    <div class="grid grid-cols-3 gap-4 text-sm text-red-700 dark:text-red-300">
                                        <div>
                                            <span class="font-semibold">نجح:</span>
                                            {{ importResults.success_count }} سيارة
                                        </div>
                                        <div>
                                            <span class="font-semibold">فشل:</span>
                                            {{ importResults.error_count }} سطر
                                        </div>
                                        <div v-if="importResults.skip_count > 0">
                                            <span class="font-semibold">تم التخطي:</span>
                                            {{ importResults.skip_count }} سطر
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- قائمة الأخطاء -->
                        <div v-if="importResults.errors && importResults.errors.length > 0" class="mt-4">
                            <h4 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">
                                تفاصيل الأخطاء:
                            </h4>
                            <div class="max-h-60 overflow-y-auto">
                                <div
                                    v-for="(error, index) in importResults.errors"
                                    :key="index"
                                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-3 mb-2"
                                >
                                    <div class="text-sm">
                                        <span class="font-semibold text-yellow-800 dark:text-yellow-300">
                                            السطر {{ error.row }}:
                                        </span>
                                        <span class="text-yellow-700 dark:text-yellow-400 ml-2">
                                            {{ error.message }}
                                        </span>
                                        <span v-if="error.pin" class="text-yellow-600 dark:text-yellow-500 block mt-1">
                                            PIN: {{ error.pin }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معلومات إضافية -->
                    <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h4 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">
                            هيكل ملف Excel المطلوب:
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2 text-right border border-gray-300 dark:border-gray-600">العمود</th>
                                        <th class="px-3 py-2 text-right border border-gray-300 dark:border-gray-600">الحقل</th>
                                        <th class="px-3 py-2 text-right border border-gray-300 dark:border-gray-600">النوع</th>
                                        <th class="px-3 py-2 text-right border border-gray-300 dark:border-gray-600">مطلوب</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700">
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">A</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">No</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">B</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">PIN</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نعم</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">C</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Name Part 1</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">D</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Color</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">E</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Model</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">F</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Purchase Price</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">رقم</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">G</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Dubai Shipping</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">رقم</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">H</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Dubai Exp</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">رقم</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">I</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Erbil Shipping</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">رقم</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">J</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Erbil Exp</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">رقم</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">K</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Name Part 2</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">L</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">Source</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">نص</td>
                                        <td class="px-3 py-2 border border-gray-300 dark:border-gray-600">لا</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

