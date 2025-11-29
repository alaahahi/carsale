 <template>
    <AuthenticatedLayout>
        <Head title="معلومات التاجر" />
        
        <div class="py-2">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm">
                    <div class="p-6 dark:bg-gray-900">
                        <!-- Header -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">معلومات التاجر</h1>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">عرض بيانات التاجر من المشروع الثاني</p>
                                </div>
                                <button @click="goBack" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    <span>رجوع</span>
                                </button>
                            </div>
                            
                            <!-- Merchant Selector (if multiple merchants) -->
                            <div v-if="merchantIds && merchantIds.length > 1" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    اختر التاجر:
                                </label>
                                <select 
                                    v-model="selectedMerchantId"
                                    @change="onMerchantChange"
                                    class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option v-for="merchantId in merchantIds" :key="merchantId" :value="merchantId">
                                        التاجر #{{ merchantId }} {{ getMerchantName(merchantId) }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="flex justify-center items-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>

                        <!-- Error State -->
                        <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-6">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-lg font-medium text-red-800 dark:text-red-300">خطأ في تحميل البيانات</h3>
                                    <p class="mt-1 text-sm text-red-700 dark:text-red-400">{{ error }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div v-else-if="merchantData">
                            <!-- Client Info Card -->
                            <div :class="getCardColorClass()" class="rounded-lg p-6 mb-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-2xl font-bold mb-2">{{ getClientName() || 'غير معروف' }}</h2>
                                        <p :class="getTextColorClass()">رقم التاجر: {{ selectedMerchantId || 'غير محدد' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p :class="getTextColorClass()" class="text-sm mb-1">الرصيد</p>
                                        <p class="text-3xl font-bold text-white">
                                            {{ formatCurrencyWithSign(getWalletBalance()) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
                                <!-- إجمالي السيارات -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي السيارات</dt>
                                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                        {{ salesSummary?.car_total || 0 }}
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- إجمالي المبيعات -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي المبيعات</dt>
                                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                        ${{ formatNumber(salesSummary?.cars_sum || 0) }}
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- مجموع الدفعات -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">مجموع الدفعات</dt>
                                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                        ${{ formatNumber(getTotalPayments()) }}
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- المطلوب -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">المطلوب</dt>
                                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                        ${{ formatNumber(getRemainingAmount()) }}
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- الرصيد غير الموزع -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">الرصيد غير الموزع</dt>
                                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                        ${{ formatNumber(getUnallocatedBalance()) }}
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <div class="mb-6">
                                <div class="border-b border-gray-200 dark:border-gray-700">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        <button
                                            @click="activeTab = 'cars'"
                                            :class="[
                                                activeTab === 'cars'
                                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                            ]">
                                            السيارات ({{ cars.length }})
                                        </button>
                                        <button
                                            @click="activeTab = 'payments'"
                                            :class="[
                                                activeTab === 'payments'
                                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                            ]">
                                            الدفعات ({{ payments.length }})
                                        </button>
                                    </nav>
                                </div>
                            </div>

                            <!-- Cars Tab -->
                            <div v-show="activeTab === 'cars'" class="bg-white dark:bg-gray-800 shadow rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">قائمة السيارات</h3>
                                </div>
                                
                                <div v-if="cars.length === 0" class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد سيارات</h3>
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">VIN</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">النوع</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">السنة</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">اللون</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الإجمالي</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المدفوع</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المتبقي</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="car in cars" :key="car.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ car.id }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ car.vin }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ car.car_type }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ car.year }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ car.car_color }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ formatDate(car.date) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    ${{ formatNumber(car.total_s || 0) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
                                                    ${{ formatNumber(car.paid || 0) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="getDebtColor((car.total_s || 0) - (car.paid || 0))">
                                                    ${{ formatNumber((car.total_s || 0) - (car.paid || 0)) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Payments Tab -->
                            <div v-show="activeTab === 'payments'" class="bg-white dark:bg-gray-800 shadow rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">قائمة الدفعات</h3>
                                    <div class="flex gap-4">
                                        <input
                                            v-model="dateFrom"
                                            type="date"
                                            @change="loadData"
                                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm">
                                        <input
                                            v-model="dateTo"
                                            type="date"
                                            @change="loadData"
                                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm">
                                    </div>
                                </div>
                                
                                <div v-if="payments.length === 0" class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد دفعات</h3>
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المبلغ</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">العملة</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الوصف</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">النوع</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ payment.id }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="payment.type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                                    {{ formatNumber(payment.amount) }} {{ payment.currency }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ payment.currency }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    {{ payment.description }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ formatDate(payment.date) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="payment.type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'">
                                                        {{ payment.type === 'in' ? 'وارد' : 'صادر' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Payments Summary -->
                                <div v-if="paymentsSummary" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي الدفعات بالدولار</p>
                                            <p class="text-lg font-bold text-gray-900 dark:text-white">${{ formatNumber(paymentsSummary.total_payments_dollar || 0) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي الدفعات بالدينار</p>
                                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatNumber(paymentsSummary.total_payments_dinar || 0) }} د.ع</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">عدد الدفعات</p>
                                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ paymentsSummary.count || 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/inertia-vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()

// Props
const props = defineProps({
    systemConfig: Object,
    merchantIds: Array,
    currentMerchantId: Number
})

// Reactive data
const loading = ref(true)
const error = ref(null)
const merchantData = ref(null)
const activeTab = ref('cars')
const dateFrom = ref(new Date().getFullYear() + '-01-01')
const dateTo = ref(new Date().getFullYear() + '-12-31')
const selectedMerchantId = ref(props.currentMerchantId)
const merchantsList = ref([]) // قائمة بأسماء التجار

// Computed
const cars = computed(() => {
    return merchantData.value?.sales?.cars || []
})

const payments = computed(() => {
    return merchantData.value?.payments?.payments || []
})

const salesSummary = computed(() => {
    return merchantData.value?.sales?.summary || {}
})

const paymentsSummary = computed(() => {
    return merchantData.value?.payments?.summary || {}
})

// Methods
const formatNumber = (number) => {
    return Math.round(number || 0).toLocaleString('en-US')
}

const formatCurrency = (amount) => {
    const formatted = formatNumber(Math.abs(amount))
    return amount >= 0 ? `$${formatted}` : `-$${formatted}`
}

const formatDate = (date) => {
    if (!date) return 'غير محدد'
    return new Date(date).toLocaleDateString('en-US')
}

const getBalanceColor = (balance) => {
    if (balance === 0) return 'text-green-200'
    if (balance < 0) return 'text-red-200'
    return 'text-green-200' // إذا كان سالب (رصيد إيجابي)
}

const getCardColorClass = () => {
    const balance = getWalletBalance()
    if (balance === 0) {
        return 'bg-gradient-to-r from-green-500 to-green-600'
    }
    if (balance < 0) {
        return 'bg-gradient-to-r from-red-500 to-red-600'
    }
    return 'bg-gradient-to-r from-green-500 to-green-600' // إذا كان سالب (رصيد إيجابي)
}

const getTextColorClass = () => {
    const balance = getWalletBalance()
    if (balance === 0) {
        return 'text-green-100'
    }
    if (balance > 0) {
        return 'text-red-100'
    }
    return 'text-green-100' // إذا كان سالب (رصيد إيجابي)
}

const getDebtColor = (debt) => {
    if (debt === 0) return 'text-green-600 dark:text-green-400'
    if (debt > 0) return 'text-red-600 dark:text-red-400'
    return 'text-gray-600 dark:text-gray-400'
}

const loadData = async () => {
    if (!selectedMerchantId.value) {
        error.value = 'لم يتم تحديد تاجر'
        loading.value = false
        return
    }
    
    loading.value = true
    error.value = null
    
    try {
        const params = new URLSearchParams({
            id: selectedMerchantId.value,
            from: dateFrom.value,
            to: dateTo.value
        })
        
        const response = await axios.get(`/api/external-merchant/sales?${params}`)
        
        if (response.data.success) {
            merchantData.value = response.data
            // حفظ اسم التاجر في القائمة (من بيانات المبيعات أو الدفعات)
            const clientName = merchantData.value?.sales?.cars?.[0]?.client?.name 
                || merchantData.value?.payments?.client?.name
                || `التاجر #${selectedMerchantId.value}`
            
            if (clientName) {
                const merchantIndex = merchantsList.value.findIndex(m => m.id === selectedMerchantId.value)
                if (merchantIndex === -1) {
                    merchantsList.value.push({
                        id: selectedMerchantId.value,
                        name: clientName
                    })
                } else {
                    merchantsList.value[merchantIndex].name = clientName
                }
            }
        } else {
            error.value = response.data.message || 'حدث خطأ أثناء جلب البيانات'
            toast.error(error.value)
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'حدث خطأ أثناء الاتصال بالخادم'
        toast.error(error.value)
        console.error('Error loading merchant data:', err)
    } finally {
        loading.value = false
    }
}

const onMerchantChange = () => {
    // تحديث URL بدون إعادة تحميل الصفحة
    const url = new URL(window.location.href)
    url.searchParams.set('merchant_id', selectedMerchantId.value)
    window.history.pushState({}, '', url)
    loadData()
}

const getMerchantName = (merchantId) => {
    const merchant = merchantsList.value.find(m => m.id === merchantId)
    return merchant ? `- ${merchant.name}` : ''
}

const getClientName = () => {
    return merchantData.value?.sales?.cars?.[0]?.client?.name 
        || merchantData.value?.payments?.client?.name
        || `التاجر #${selectedMerchantId.value}`
}

const getWalletBalance = () => {
    const balance = merchantData.value?.payments?.client?.wallet_balance || 0
    return balance * -1
}

const getTotalPayments = () => {
    const totalDollar = paymentsSummary.value?.total_payments_dollar || 0
    return totalDollar
}

const getRemainingAmount = () => {
    const totalCars = salesSummary.value?.cars_sum || 0
    const totalPayments = paymentsSummary.value?.total_payments_dollar || 0
    return totalCars - totalPayments
}

const getUnallocatedBalance = () => {
    const totalPayments = paymentsSummary.value?.total_payments_dollar || 0
    const paidCars = salesSummary.value?.cars_paid || 0
    return totalPayments - paidCars
}

const formatCurrencyWithSign = (amount) => {
    const formatted = formatNumber(Math.abs(amount))
    if (amount >= 0) {
        return `+$${formatted}`
    } else {
        return `-$${formatted}`
    }
}

const goBack = () => {
    window.history.back()
}

// Lifecycle
onMounted(() => {
    // تهيئة معرف التاجر من props أو من URL
    if (props.currentMerchantId) {
        selectedMerchantId.value = props.currentMerchantId
    } else if (props.merchantIds && props.merchantIds.length > 0) {
        selectedMerchantId.value = props.merchantIds[0]
    }
    
    if (selectedMerchantId.value) {
        loadData()
    } else {
        error.value = 'لا توجد معرفات تجار محددة في إعدادات النظام'
        loading.value = false
    }
})
</script>

