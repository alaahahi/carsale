<template>
  <AuthenticatedLayout>
    <Head title="إدارة الصندوق والقاسات" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">إدارة الصندوق والقاسات</h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">إدارة شاملة للصندوق وقاسات الزبائن</p>
        </div>
 
        <!-- Summary Cards - Only show when NOT viewing specific customer -->
        <div v-if="!customer_id" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Total Customer Wallets -->
          <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold mb-1 opacity-90">مجموع القاسات</h3>
                <p class="text-3xl font-bold">${{ Math.round(totalCustomerWallets).toLocaleString() }}</p>
              </div>
              <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Difference (Capital - Customer Wallets) -->
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold mb-1 opacity-90">الفرق (رأس المال - القاسات)</h3>
                <p class="text-3xl font-bold" :class="(capital - totalCustomerWallets) >= 0 ? '' : 'text-red-200'">${{ Math.round(capital - totalCustomerWallets).toLocaleString() }}</p>
                <p class="text-xs mt-1 opacity-75">${{ Math.round(capital).toLocaleString() }} - ${{ Math.round(totalCustomerWallets).toLocaleString() }}</p>
              </div>
              <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Capital (Cars + Expenses) -->
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold mb-1 opacity-90">رأس المال</h3>
                <p class="text-3xl font-bold">${{ Math.round(capital).toLocaleString() }}</p>
                <p class="text-xs mt-1 opacity-75">السيارات + المصاريف (${{ Math.round(totalExpenses).toLocaleString() }})</p>
              </div>
              <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Customer Wallets List - Only show when NOT viewing specific customer -->
        <div v-if="!customer_id && Array.isArray(allCustomers) && allCustomers.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">قائمة الزبائن والقاسات</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">اسم الزبون</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الرصيد</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">حالة القاسة</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">إجراءات</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="customer in allCustomers" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    {{ customer.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" 
                      :class="getCustomerBalance(customer) >= 0 ? 'text-green-600' : 'text-red-600'">
                    ${{ Math.round(getCustomerBalance(customer)).toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span v-if="hasCustomerWallet(customer)" 
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                      <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                      </svg>
                      تم الإنشاء
                    </span>
                    <span v-else 
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                      <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                      </svg>
                      لم يتم الإنشاء
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2 space-x-reverse">
                    <button v-if="!hasCustomerWallet(customer)"
                            @click="createWalletForCustomer(customer)"
                            :disabled="creatingWallet"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md disabled:opacity-50 transition-colors">
                      <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      إنشاء قاسة
                    </button>
                    <button @click="selectCustomerForView(customer)"
                            class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-md transition-colors">
                      <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      عرض
                    </button>
                    <button v-if="hasCustomerWallet(customer)"
                            @click="showDepositModal(customer)"
                            class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-md transition-colors">
                      <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      إيداع
                    </button>
                    <button v-if="hasCustomerWallet(customer)"
                            @click="showWithdrawModal(customer)"
                            class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors">
                      <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                      </svg>
                      سحب
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Selected Customer Details -->
        <div v-if="viewingCustomer" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">تفاصيل الزبون: {{ viewingCustomer.name }}</h3>
            <button @click="clearSelectedCustomer" 
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg p-4">
                <div class="text-sm text-blue-600 dark:text-blue-300 mb-1">الزبون</div>
                <div class="text-xl font-bold text-blue-900 dark:text-white">{{ viewingCustomer.name }}</div>
                <div class="text-xs text-blue-500 dark:text-blue-400 mt-1">{{ viewingCustomer.email }}</div>
              </div>
              <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg p-4">
                <div class="text-sm text-green-600 dark:text-green-300 mb-1">الرصيد الحالي</div>
                <div class="text-2xl font-bold text-green-900 dark:text-white">${{ Math.round(viewingCustomerBalance).toLocaleString() }}</div>
              </div>
              <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-lg p-4">
                <div class="text-sm text-purple-600 dark:text-purple-300 mb-1">حالة القاسة</div>
                <div class="text-lg font-semibold text-purple-900 dark:text-white">
                  {{ hasCustomerWallet(viewingCustomer) ? 'نشطة' : 'غير منشأة' }}
                </div>
              </div>
            </div>
            <div class="flex space-x-3 space-x-reverse">
              <button v-if="hasCustomerWallet(viewingCustomer)"
                      @click="showDepositModal(viewingCustomer)"
                      class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                إيداع مبلغ
              </button>
              <button v-if="hasCustomerWallet(viewingCustomer)"
                      @click="showWithdrawModal(viewingCustomer)"
                      class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
                سحب مبلغ
              </button>
              <button v-if="!hasCustomerWallet(viewingCustomer)"
                      @click="createWalletForCustomer(viewingCustomer)"
                      :disabled="creatingWallet"
                      class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium disabled:opacity-50 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ creatingWallet ? 'جاري الإنشاء...' : 'إنشاء القاسة' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Recent Transactions - Show for viewing customer only -->
        <div v-if="viewingCustomer && viewingCustomerTransactions.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">المعاملات الأخيرة</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">النوع</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المبلغ</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الوصف</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الرصيد المتراكم</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(transaction, index) in viewingCustomerTransactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                    {{ new Date(transaction.created_at).toLocaleDateString('en-US') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="transaction.type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'" 
                          class="px-2 py-1 rounded-full text-xs font-semibold">
                      {{ transaction.type === 'in' ? 'إيداع' : 'سحب' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" 
                      :class="transaction.type === 'in' ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.type === 'in' ? '+' : '-' }}${{ Math.round(transaction.amount).toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ transaction.description || 'لا يوجد وصف' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" 
                      :class="getRunningBalanceForViewing(index) >= 0 ? 'text-blue-600' : 'text-red-600'">
                    ${{ Math.round(getRunningBalanceForViewing(index)).toLocaleString() }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Deposit Modal -->
        <div v-if="showingDepositModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeModals">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">إيداع مبلغ - {{ modalCustomer?.name }}</h3>
              <button @click="closeModals" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <form @submit.prevent="handleDeposit" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المبلغ *</label>
                <input type="number" v-model="transactionForm.amount" required min="0" step="0.01"
                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200"
                       placeholder="أدخل المبلغ">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">البيان (اختياري)</label>
                <textarea v-model="transactionForm.note" rows="3"
                          class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200"
                          placeholder="وصف العملية"></textarea>
              </div>
              <div class="flex space-x-3 space-x-reverse pt-4">
                <button type="button" @click="closeModals"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium transition-colors">
                  إلغاء
                </button>
                <button type="submit" :disabled="transactionLoading"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 transition-colors flex items-center justify-center">
                  <svg v-if="transactionLoading" class="animate-spin w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ transactionLoading ? 'جاري الإيداع...' : 'إيداع' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showingWithdrawModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeModals">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">سحب مبلغ - {{ modalCustomer?.name }}</h3>
              <button @click="closeModals" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <form @submit.prevent="handleWithdraw" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المبلغ *</label>
                <input type="number" v-model="transactionForm.amount" required min="0" step="0.01"
                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                       placeholder="أدخل المبلغ">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">البيان (اختياري)</label>
                <textarea v-model="transactionForm.note" rows="3"
                          class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-red-500 focus:ring focus:ring-red-200"
                          placeholder="وصف العملية"></textarea>
              </div>
              <div class="flex space-x-3 space-x-reverse pt-4">
                <button type="button" @click="closeModals"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium transition-colors">
                  إلغاء
                </button>
                <button type="submit" :disabled="transactionLoading"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 transition-colors flex items-center justify-center">
                  <svg v-if="transactionLoading" class="animate-spin w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ transactionLoading ? 'جاري السحب...' : 'سحب' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { ref, onMounted, computed } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()

// Props
const props = defineProps({
  cashboxBalance: Number,
  cars: Array,
  customer_id: Number,
  customerWallets: Array,
  totalCustomerWallets: Number,
  capital: Number,
  totalCarsPrice: Number,
  totalExpenses: Number,
  allCustomers: Array
})

// Reactive data
const loading = ref(false)
const creatingWallet = ref(false)
const transactionLoading = ref(false)
const viewingCustomer = ref(null)
const viewingCustomerBalance = ref(0)
const viewingCustomerTransactions = ref([])
const showingDepositModal = ref(false)
const showingWithdrawModal = ref(false)
const modalCustomer = ref(null)

// Forms
const transactionForm = ref({
  amount: '',
  note: ''
})

// Debug on mount
onMounted(() => {
  console.log('allCustomers:', props.allCustomers)
  console.log('allCustomers type:', typeof props.allCustomers)
  console.log('allCustomers is Array:', Array.isArray(props.allCustomers))
  console.log('allCustomers length:', props.allCustomers?.length)
})

// Helper Methods
const hasCustomerWallet = (customer) => {
  const wallet = props.customerWallets?.find(w => w.customer_id === customer.id)
  return wallet ? true : false
}

const getCustomerBalance = (customer) => {
  const wallet = props.customerWallets?.find(w => w.customer_id === customer.id)
  return wallet ? wallet.balance : 0
}

// Customer Selection
const selectCustomerForView = async (customer) => {
  viewingCustomer.value = customer
  loading.value = true
  
  try {
    const response = await axios.get(`/api/customer-wallet/${customer.id}`)
    if (response.data.success) {
      viewingCustomerBalance.value = response.data.balance
      viewingCustomerTransactions.value = response.data.transactions || []
    } else {
      viewingCustomerBalance.value = 0
      viewingCustomerTransactions.value = []
      toast.warning(response.data.message || 'لم يتم إنشاء قاسة لهذا الزبون بعد')
    }
  } catch (error) {
    console.error('Error loading customer wallet:', error)
    toast.error('حدث خطأ في تحميل معلومات القاسة')
  } finally {
    loading.value = false
  }
}

const clearSelectedCustomer = () => {
  viewingCustomer.value = null
  viewingCustomerBalance.value = 0
  viewingCustomerTransactions.value = []
}

// Wallet Creation
const createWalletForCustomer = async (customer) => {
  creatingWallet.value = true
  
  try {
    const response = await axios.post('/api/customer-wallet/create', {
      customer_id: customer.id
    })
    
    if (response.data.success) {
      toast.success('تم إنشاء القاسة بنجاح')
      // Reload page after delay to update wallet list
      setTimeout(() => {
        window.location.href = window.location.href
      }, 1000)
    }
  } catch (error) {
    console.error('Error creating wallet:', error)
    toast.error('حدث خطأ في إنشاء القاسة')
  } finally {
    creatingWallet.value = false
  }
}

// Modal Methods
const showDepositModal = (customer) => {
  modalCustomer.value = customer
  showingDepositModal.value = true
  transactionForm.value = { amount: '', note: '' }
}

const showWithdrawModal = (customer) => {
  modalCustomer.value = customer
  showingWithdrawModal.value = true
  transactionForm.value = { amount: '', note: '' }
}

const closeModals = () => {
  showingDepositModal.value = false
  showingWithdrawModal.value = false
  modalCustomer.value = null
  transactionForm.value = { amount: '', note: '' }
}

// Transaction Methods
const handleDeposit = async () => {
  if (!transactionForm.value.amount || transactionForm.value.amount <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح')
    return
  }
  
  transactionLoading.value = true
  
  try {
    const params = new URLSearchParams({
      amount: transactionForm.value.amount,
      note: transactionForm.value.note || '',
      customer_id: modalCustomer.value.id
    })
    
    await axios.get(`/api/addToBox?${params}`)
    toast.success('تم إيداع المبلغ بنجاح')
    
    const customerId = modalCustomer.value.id
    closeModals()
    
    // Refresh customer data if viewing
    if (viewingCustomer.value?.id === customerId) {
      await selectCustomerForView({ id: customerId })
    }
    
    // Reload page to update wallet list
    setTimeout(() => {
      window.location.href = window.location.href
    }, 800)
  } catch (error) {
    console.error('Error depositing:', error)
    toast.error('حدث خطأ في الإيداع')
  } finally {
    transactionLoading.value = false
  }
}

const handleWithdraw = async () => {
  if (!transactionForm.value.amount || transactionForm.value.amount <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح')
    return
  }
  
  transactionLoading.value = true
  
  try {
    const params = new URLSearchParams({
      amount: transactionForm.value.amount,
      note: transactionForm.value.note || '',
      customer_id: modalCustomer.value.id
    })
    
    await axios.get(`/api/withDrawFromBox?${params}`)
    toast.success('تم سحب المبلغ بنجاح')
    
    const customerId = modalCustomer.value.id
    closeModals()
    
    // Refresh customer data if viewing
    if (viewingCustomer.value?.id === customerId) {
      await selectCustomerForView({ id: customerId })
    }
    
    // Reload page to update wallet list
    setTimeout(() => {
      window.location.href = window.location.href
    }, 800)
  } catch (error) {
    console.error('Error withdrawing:', error)
    toast.error('حدث خطأ في السحب')
  } finally {
    transactionLoading.value = false
  }
}

// Running Balance Calculation
const getRunningBalanceForViewing = (index) => {
  let balance = 0
  for (let i = viewingCustomerTransactions.value.length - 1; i >= index; i--) {
    const transaction = viewingCustomerTransactions.value[i]
    if (transaction.type === 'in') {
      balance += parseFloat(transaction.amount)
    } else {
      balance -= parseFloat(transaction.amount)
    }
  }
  return balance
}
</script>

