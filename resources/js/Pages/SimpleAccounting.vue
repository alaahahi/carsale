<template>
  <AuthenticatedLayout>
    <Head title="المحاسبة المبسطة" />
    
    <div class="py-6">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">المحاسبة المبسطة</h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">عرض المبالغ والمصاريف المرتبطة بالسيارات</p>
          
          <!-- Customer Info -->
          <div v-if="selectedCustomer" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-medium text-blue-800">
                  الزبون المحدد: {{ selectedCustomer.name }}
                </h3>
                <p class="text-blue-600">{{ selectedCustomer.email }}</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-800">
                  {{ customerBalance.toLocaleString() }} د.ع
                </div>
                <div class="text-sm text-blue-600">رصيد الزبون</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Balances -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- In Account -->
          <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold mb-2">حساب الدخل</h3>
                <p class="text-3xl font-bold">${{ Math.round(inAccountBalance).toLocaleString() }}</p>
                <p class="text-sm opacity-90 mt-1">إجمالي الإيداعات</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Out Account -->
          <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold mb-2">حساب المصروفات</h3>
                <p class="text-3xl font-bold">${{ Math.round(outAccountBalance).toLocaleString() }}</p>
                <p class="text-sm opacity-90 mt-1">إجمالي السحوبات</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Transfers Account -->
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold mb-2">حساب التحويلات</h3>
                <p class="text-3xl font-bold">${{ Math.round(transfersAccountBalance).toLocaleString() }}</p>
                <p class="text-sm opacity-90 mt-1">إجمالي التحويلات</p>
              </div>
              <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Cars Summary -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ملخص السيارات</h3>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">إجمالي السيارات</p>
                  <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ totalCars }}</p>
                </div>
              </div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-green-800 dark:text-green-200">السيارات المباعة</p>
                  <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ soldCars }}</p>
                </div>
              </div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">في المخزن</p>
                  <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ carsInStock }}</p>
                </div>
              </div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-purple-800 dark:text-purple-200">إجمالي التكلفة</p>
                  <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">${{ Math.round(totalCost).toLocaleString() }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Expenses -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">آخر المصروفات</h3>
          </div>
          
          <div v-if="loadingExpenses" class="p-6 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">جاري التحميل...</p>
          </div>
          
          <div v-else-if="expenses.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="mt-2">لا توجد مصروفات</p>
          </div>
          
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المبلغ</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">نوع المصروف</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">البيان</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    {{ formatDate(expense.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                    ${{ Math.round(expense.amount).toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    {{ expense.expense_type_name || 'غير محدد' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    {{ expense.note || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()

// Props
const props = defineProps({
  inAccountBalance: Number,
  outAccountBalance: Number,
  transfersAccountBalance: Number,
  totalCars: Number,
  soldCars: Number,
  carsInStock: Number,
  totalCost: Number,
  customer_id: Number
})

// Reactive data
const loadingExpenses = ref(false)
const expenses = ref([])
const selectedCustomer = ref(null)
const customerBalance = ref(0)

// Methods
const loadCustomerInfo = async () => {
  if (!props.customer_id) return
  
  try {
    const response = await axios.get(`/api/customer-wallet/${props.customer_id}`)
    if (response.data.success) {
      selectedCustomer.value = response.data.customer
      customerBalance.value = response.data.balance
    }
  } catch (error) {
    console.error('Error loading customer info:', error)
  }
}

const loadExpenses = async () => {
  loadingExpenses.value = true
  try {
    const response = await axios.get('/api/expenses?limit=10')
    expenses.value = response.data.expenses || []
  } catch (error) {
    toast.error('حدث خطأ في تحميل المصروفات')
    console.error(error)
  } finally {
    loadingExpenses.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US')
}

// Load expenses on mount
onMounted(async () => {
  await loadExpenses()
  await loadCustomerInfo()
})
</script>
