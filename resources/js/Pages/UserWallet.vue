<template>
    <AuthenticatedLayout>
        <Head title="محفظة المستخدم" />
        
        <div class="py-2">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm">
                    <div class="p-6 dark:bg-gray-900">
                        <!-- Header -->
                        <div class="mb-6">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">قاسة {{ user.name }}</h1>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">إدارة المعاملات الشخصية والشراكة</p>
                            
                            <!-- Current Balance -->
                            <div class="mt-4 bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-200">الرصيد الحالي</p>
                                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">${{ formatNumber(userWalletBalance) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ,
                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <!-- إجمالي الإدخال -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي الإدخال</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    ${{ formatNumber(totalIn) }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- إجمالي السحب -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي السحب</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    ${{ formatNumber(totalOut) }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- عدد المعاملات -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">عدد المعاملات</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ userTransactions.length }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- رأس المال -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">رأس المال</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    ${{ formatNumber(capital) }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
            </div>

                        <!-- Action Buttons -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-8">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">عمليات القاسة</h3>
                            </div>
                            <div class="p-6">
                                <div class="flex flex-wrap gap-4">
                                    <button
                                        @click="showAddModal = true"
                                        class="flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        إضافة إلى القاسة
                                    </button>
                                    <button
                                        @click="showWithdrawModal = true"
                                        class="flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                        سحب من القاسة
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">سجل المعاملات</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">النوع</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المبلغ</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">البيان</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-if="userTransactions.length === 0">
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                    </svg>
                                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد معاملات</h3>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">ابدأ بإضافة معاملة جديدة.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-for="transaction in userTransactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ transaction.id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ formatDate(transaction.created_at) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getTransactionTypeClass(transaction.type)">
                                                    {{ getTransactionTypeText(transaction.type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="getAmountColor(transaction.type)">
                                                {{ getAmountPrefix(transaction.type) }}${{ formatNumber(transaction.amount) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ transaction.description || 'لا يوجد بيان' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button
                                                    @click="deleteTransaction(transaction.id)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

                        <!-- Add Modal -->
                        <Modal :show="showAddModal" @close="showAddModal = false">
                            <div class="bg-white dark:bg-gray-800 px-6 py-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">إضافة إلى القاسة</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">أدخل المبلغ والبيان</p>
                                        </div>
                                    </div>
                                    <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <form @submit.prevent="addToWallet" class="space-y-6">
                                    <div>
                                        <label for="add_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                                            <span class="text-red-500">*</span> المبلغ
                                        </label>
                                        <input
                                            type="number"
                                            id="add_amount"
                                            v-model="addForm.amount"
                                            required
                                            min="0.01"
                                            step="0.01"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 text-lg"
                                            placeholder="أدخل المبلغ">
                                    </div>

                                    <div>
                                        <label for="add_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                                            البيان
                                        </label>
                                        <textarea
                                            id="add_description"
                                            v-model="addForm.description"
                                            rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 text-lg"
                                            placeholder="أدخل البيان (اختياري)"></textarea>
                                    </div>

                                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                                        <button
                                            type="button"
                                            @click="showAddModal = false"
                                            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 font-medium">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ addLoading ? 'جاري الإضافة...' : 'إضافة' }}
                                        </button>
                                    </div>
                </form>
            </div>
        </Modal>

                        <!-- Withdraw Modal -->
                        <Modal :show="showWithdrawModal" @close="showWithdrawModal = false">
                            <div class="bg-white dark:bg-gray-800 px-6 py-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">سحب من القاسة</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">أدخل المبلغ والبيان</p>
                                        </div>
                                    </div>
                                    <button @click="showWithdrawModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <form @submit.prevent="withdrawFromWallet" class="space-y-6">
                                    <div>
                                        <label for="withdraw_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                                            <span class="text-red-500">*</span> المبلغ
                                        </label>
                                        <input
                                            type="number"
                                            id="withdraw_amount"
                                            v-model="withdrawForm.amount"
                                            required
                                            min="0.01"
                                            step="0.01"
                                            :max="userWalletBalance"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors duration-200 text-lg"
                                            placeholder="أدخل المبلغ">
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">الرصيد المتاح: ${{ formatNumber(userWalletBalance) }}</p>
                                    </div>

                                    <div>
                                        <label for="withdraw_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                                            البيان
                                        </label>
                                        <textarea
                                            id="withdraw_description"
                                            v-model="withdrawForm.description"
                                            rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors duration-200 text-lg"
                                            placeholder="أدخل البيان (اختياري)"></textarea>
                                    </div>

                                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                                        <button
                                            type="button"
                                            @click="showWithdrawModal = false"
                                            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 font-medium">
                                            إلغاء
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="withdrawLoading || withdrawForm.amount > userWalletBalance"
                                            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 font-medium">
                                            <svg v-if="withdrawLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ withdrawLoading ? 'جاري السحب...' : 'سحب' }}
                                        </button>
                                    </div>
                </form>
            </div>
        </Modal>
    </div>  
    </div>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/inertia-vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()
 
// Props
const props = defineProps({
    user: Object,
    userTransactions: Array,
    userWalletBalance: Number,
    capital: Number
})

// Reactive data
const showAddModal = ref(false)
const showWithdrawModal = ref(false)
const addLoading = ref(false)
const withdrawLoading = ref(false)

const addForm = ref({
    amount: '',
    description: ''
})

const withdrawForm = ref({
    amount: '',
    description: ''
})

// Computed
const totalIn = computed(() => {
    return props.userTransactions
        .filter(t => t.type === 'user_in')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
})

const totalOut = computed(() => {
    return props.userTransactions
        .filter(t => t.type === 'user_out')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
})

// Methods
const formatNumber = (number) => {
    return Math.round(number).toLocaleString()
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US')
}

const getTransactionTypeClass = (type) => {
    switch (type) {
        case 'user_in':
            return 'bg-green-100 text-green-800'
        case 'user_out':
            return 'bg-red-100 text-red-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const getTransactionTypeText = (type) => {
    switch (type) {
        case 'user_in':
            return 'إدخال'
        case 'user_out':
            return 'سحب'
        default:
            return 'غير محدد'
    }
}

const getAmountColor = (type) => {
    switch (type) {
        case 'user_in':
            return 'text-green-600'
        case 'user_out':
            return 'text-red-600'
        default:
            return 'text-gray-600'
    }
}

const getAmountPrefix = (type) => {
    switch (type) {
        case 'user_in':
            return '+'
        case 'user_out':
            return '-'
        default:
            return ''
    }
}

const addToWallet = async () => {
    if (!addForm.value.amount || addForm.value.amount <= 0) {
        toast.error('يرجى إدخال مبلغ صحيح')
        return
    }

    addLoading.value = true

    try {
        const response = await axios.post('/api/user-wallet/add', {
            amount: addForm.value.amount,
            description: addForm.value.description,
            user_id: props.user.id
        })

        if (response.data.success) {
            toast.success('تم إضافة المبلغ بنجاح')
            showAddModal.value = false
            addForm.value = { amount: '', description: '' }
            // Refresh the page to get updated data
            window.location.reload()
        } else {
            toast.error(response.data.message || 'حدث خطأ أثناء الإضافة')
        }
    } catch (error) {
        console.error('Error adding to wallet:', error)
        toast.error('حدث خطأ أثناء الإضافة')
    } finally {
        addLoading.value = false
    }
}

const withdrawFromWallet = async () => {
    if (!withdrawForm.value.amount || withdrawForm.value.amount <= 0) {
        toast.error('يرجى إدخال مبلغ صحيح')
        return
    }

    if (withdrawForm.value.amount > props.userWalletBalance) {
        toast.error('المبلغ المطلوب أكبر من الرصيد المتاح')
        return
    }

    withdrawLoading.value = true

    try {
        const response = await axios.post('/api/user-wallet/withdraw', {
            amount: withdrawForm.value.amount,
            description: withdrawForm.value.description,
            user_id: props.user.id
        })

        if (response.data.success) {
            toast.success('تم سحب المبلغ بنجاح')
            showWithdrawModal.value = false
            withdrawForm.value = { amount: '', description: '' }
            // Refresh the page to get updated data
            window.location.reload()
        } else {
            toast.error(response.data.message || 'حدث خطأ أثناء السحب')
        }
    } catch (error) {
        console.error('Error withdrawing from wallet:', error)
        toast.error('حدث خطأ أثناء السحب')
    } finally {
        withdrawLoading.value = false
    }
}

const deleteTransaction = async (transactionId) => {
    if (!confirm('هل أنت متأكد من حذف هذه المعاملة؟')) {
        return
    }

    try {
        const response = await axios.delete(`/api/user-wallet/transactions/${transactionId}`, {
            data: { user_id: props.user.id }
        })

        if (response.data.success) {
            toast.success('تم حذف المعاملة بنجاح')
            // Refresh the page to get updated data
            window.location.reload()
        } else {
            toast.error(response.data.message || 'حدث خطأ أثناء الحذف')
        }
    } catch (error) {
        console.error('Error deleting transaction:', error)
        toast.error('حدث خطأ أثناء الحذف')
    }
}
</script>
