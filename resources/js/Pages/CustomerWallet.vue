<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                إدارة قاسة الزبون
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <!-- اختيار الزبون -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium mb-4">اختيار الزبون</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div 
                                    v-for="customer in customers" 
                                    :key="customer.id"
                                    @click="selectCustomer(customer)"
                                    :class="[
                                        'p-4 border rounded-lg cursor-pointer transition-all duration-200',
                                        selectedCustomer?.id === customer.id 
                                            ? 'border-blue-500 bg-blue-50' 
                                            : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                    ]"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-medium">
                                                {{ customer.name.charAt(0) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ customer.name }}</h4>
                                            <p class="text-sm text-gray-500">{{ customer.email }}</p>
                                            <p class="text-xs text-gray-400">ID: {{ customer.id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- معلومات الزبون المحدد -->
                        <div v-if="selectedCustomer" class="mb-8">
                            <div class="bg-gradient-to-r from-green-50 to-blue-50 border-2 border-green-200 rounded-xl p-6 shadow-lg">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                            الزبون: {{ selectedCustomer.name }}
                                        </h3>
                                        <p class="text-gray-600 flex items-center">
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ selectedCustomer.email }}
                                        </p>
                                        
                                        <!-- حالة القاسة -->
                                        <div class="mt-3 flex items-center">
                                            <span class="text-sm font-medium text-gray-600 ml-2">حالة القاسة:</span>
                                            <span v-if="hasWallet" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                تم الإنشاء
                                            </span>
                                            <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                لم يتم الإنشاء
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right bg-white rounded-lg p-4 shadow-md">
                                        <div class="text-3xl font-bold text-blue-600">
                                            ${{ Math.round(walletBalance).toLocaleString() }}
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">دولار أمريكي</div>
                                        <div class="text-xs text-gray-400 mt-1">الرصيد الحالي</div>
                                    </div>
                                </div>
                                
                                <!-- أزرار الإجراءات -->
                                <div class="flex space-x-3 space-x-reverse mt-4 pt-4 border-t border-gray-200">
                                    <button
                                        v-if="!hasWallet"
                                        @click="createWallet"
                                        :disabled="loading"
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center shadow-md"
                                    >
                                        <svg v-if="!loading" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <svg v-else class="animate-spin w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span v-if="loading">جاري الإنشاء...</span>
                                        <span v-else>إنشاء القاسة</span>
                                    </button>

                                    <Link
                                        v-if="hasWallet"
                                        :href="route('simple-cash', { customer_id: selectedCustomer.id })"
                                        class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-lg font-bold transition-all duration-200 flex items-center justify-center shadow-md"
                                    >
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        الدخول إلى القاسة
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- رسائل النجاح/الخطأ -->
                        <div v-if="message" class="mb-4">
                            <div 
                                :class="[
                                    'p-4 rounded-lg',
                                    messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]"
                            >
                                {{ message }}
                            </div>
                        </div>

                        <!-- المعاملات الأخيرة -->
                        <div v-if="selectedCustomer && transactions.length > 0" class="mt-8">
                            <h3 class="text-lg font-medium mb-4">المعاملات الأخيرة</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                الوصف
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                المبلغ
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                النوع
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                التاريخ
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="transaction in transactions" :key="transaction.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ transaction.description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span 
                                                    :class="[
                                                        'font-medium',
                                                        transaction.type === 'in' ? 'text-green-600' : 'text-red-600'
                                                    ]"
                                                >
                                                    {{ transaction.type === 'in' ? '+' : '-' }}${{ Math.round(transaction.amount).toLocaleString() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span 
                                                    :class="[
                                                        'px-2 py-1 rounded-full text-xs font-medium',
                                                        transaction.type === 'in' 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-red-100 text-red-800'
                                                    ]"
                                                >
                                                    {{ transaction.type === 'in' ? 'إيداع' : 'سحب' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(transaction.created_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/inertia-vue3'
import axios from 'axios'

const props = defineProps({
    customers: Array
})

const selectedCustomer = ref(null)
const walletBalance = ref(0)
const transactions = ref([])
const loading = ref(false)
const message = ref('')
const messageType = ref('')
const hasWallet = ref(false)

const selectCustomer = async (customer) => {
    selectedCustomer.value = customer
    message.value = ''
    
    // جلب معلومات القاسة والمعاملات
    try {
        const response = await axios.get(`/api/customer-wallet/${customer.id}`)
        if (response.data.success) {
            walletBalance.value = response.data.balance
            transactions.value = response.data.transactions
            hasWallet.value = response.data.wallet && response.data.wallet.id ? true : false
        }
    } catch (error) {
        console.error('Error fetching wallet info:', error)
        hasWallet.value = false
    }
}

const createWallet = async () => {
    if (!selectedCustomer.value) return
    
    loading.value = true
    try {
        const response = await axios.post('/api/customer-wallet/create', {
            customer_id: selectedCustomer.value.id
        })
        
        if (response.data.success) {
            message.value = response.data.message
            messageType.value = 'success'
            hasWallet.value = true
            
            // تحديث معلومات القاسة
            await selectCustomer(selectedCustomer.value)
        }
    } catch (error) {
        message.value = 'حدث خطأ في إنشاء القاسة'
        messageType.value = 'error'
        console.error('Error creating wallet:', error)
    } finally {
        loading.value = false
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

