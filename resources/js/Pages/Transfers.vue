<template>
  <AuthenticatedLayout>
    <Head title="صندوق المعاملات" />
    
    <div class="py-2">
      <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm">
          <div class="p-6 dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6">
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">صندوق المعاملات</h1>
              <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">إدارة جميع الدفعات الداخلة والخارجة</p>
              
              <!-- Action Buttons -->
              <div class="mt-4 flex space-x-4">
                <button @click="showAddToBoxModal = true" 
                        class="bg-green-600 hover:bg-green-700 mx-2 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>إدخال إلى الصندوق</span>
                </button>
                
                <button @click="showWithdrawFromBoxModal = true" 
                        class="bg-red-600 hover:bg-red-700  mx-4 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                  </svg>
                  <span>سحب من الصندوق</span>
                </button>
              </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-8">
              <!-- إجمالي الدخل -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي الدخل</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                          ${{ Math.round(totalIncome).toLocaleString() }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- دخل الصندوق -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">دخل الصندوق</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                          ${{ Math.round(totalFundIncome).toLocaleString() }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- إجمالي الخرج -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي الخرج</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                          ${{ Math.round(totalExpenses).toLocaleString() }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- رصيد الصندوق -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">رصيد الصندوق</dt>
                        <dd class="text-lg font-medium" :class="cashboxBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                          ${{ Math.round(cashboxBalance).toLocaleString() }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- إجمالي الدين -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي الدين</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                          ${{ Math.round(totalDebt).toLocaleString() }}
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
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">رأس المال</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white">
                          ${{ Math.round(totalCapital).toLocaleString() }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- User Statistics Section -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">إحصائيات الشراكة</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- إجمالي إدخال المستخدمين -->
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-green-800 dark:text-green-200">إجمالي إدخال المستخدمين</p>
                      <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                        ${{ Math.round(totalUserIn).toLocaleString() }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- إجمالي سحب المستخدمين -->
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-red-800 dark:text-red-200">إجمالي سحب المستخدمين</p>
                      <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                        ${{ Math.round(totalUserOut).toLocaleString() }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- صافي رصيد المستخدمين -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-blue-800 dark:text-blue-200">صافي رصيد المستخدمين</p>
                      <p class="text-2xl font-bold" :class="netUserBalance >= 0 ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100'">
                        ${{ Math.round(netUserBalance).toLocaleString() }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">نوع المعاملة</label>
                  <select v-model="filters.type" @change="loadTransactions" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">جميع المعاملات</option>
                    <option value="in">دخل</option>
                    <option value="out">خرج</option>
                    <option value="user_in">إدخال المستخدمين</option>
                    <option value="user_out">سحب المستخدمين</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">من تاريخ</label>
                  <input type="date" v-model="filters.dateFrom" @change="loadTransactions" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">إلى تاريخ</label>
                  <input type="date" v-model="filters.dateTo" @change="loadTransactions" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div class="flex items-end">
                  <button @click="clearFilters" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    مسح الفلاتر
                  </button>
                </div>
              </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">المعاملات</h3>
              </div>
              
              <div v-if="loading" class="p-6 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">جاري التحميل...</p>
              </div>
              
              <div v-else-if="transactions.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                لا توجد معاملات
              </div>
              
              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">التاريخ</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">النوع</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المبلغ</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الوصف</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">السيارة</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الزبون</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المحفظة</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">العمليات</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ formatDate(transaction.created_at) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                              :class="transaction.type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                          {{ transaction.type === 'in' ? 'دخل' : 'خرج' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" 
                          :class="transaction.type === 'in' ? 'text-green-600' : 'text-red-600'">
                        ${{ Math.round(transaction.amount).toLocaleString() }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                        {{ transaction.description }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ transaction.car_name || 'غير مرتبط' }}
                        <span v-if="transaction.car_pin" class="text-gray-500">({{ transaction.car_pin }})</span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ transaction.client_name || 'غير مرتبط' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ transaction.wallet?.user?.name || 'غير محدد' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button @click="deleteTransaction(transaction.id)" 
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <!-- Pagination -->
              <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <div class="text-sm text-gray-700 dark:text-gray-300">
                    عرض {{ pagination.from }} إلى {{ pagination.to }} من {{ pagination.total }} نتيجة
                  </div>
                  <div class="flex space-x-2">
                    <button v-if="pagination.current_page > 1" @click="loadTransactions(pagination.current_page - 1)" 
                            class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded">
                      السابق
                    </button>
                    <button v-if="pagination.current_page < pagination.last_page" @click="loadTransactions(pagination.current_page + 1)" 
                            class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded">
                      التالي
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add to Box Modal -->
    <div v-if="showAddToBoxModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">إدخال إلى الصندوق</h3>
            <button @click="showAddToBoxModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المبلغ</label>
            <input type="number" v-model="addToBoxForm.amount" 
                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   placeholder="أدخل المبلغ">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">البيان</label>
            <textarea v-model="addToBoxForm.note" 
                      class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      rows="3" placeholder="أدخل البيان (اختياري)"></textarea>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button @click="showAddToBoxModal = false" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              إلغاء
            </button>
            <button @click="confirmAddToBox" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
              تأكيد الإدخال
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Withdraw from Box Modal -->
    <div v-if="showWithdrawFromBoxModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">سحب من الصندوق</h3>
            <button @click="showWithdrawFromBoxModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المبلغ</label>
            <input type="number" v-model="withdrawFromBoxForm.amount" 
                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   placeholder="أدخل المبلغ">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">البيان</label>
            <textarea v-model="withdrawFromBoxForm.note" 
                      class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      rows="3" placeholder="أدخل البيان (اختياري)"></textarea>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button @click="showWithdrawFromBoxModal = false" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              إلغاء
            </button>
            <button @click="confirmWithdrawFromBox" 
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
              تأكيد السحب
            </button>
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
  totalIncome: Number,
  totalExpenses: Number,
  cashboxBalance: Number,
  totalFundIncome: Number,
  totalDebt: Number,
  totalCapital: Number,
  totalUserIn: Number,
  totalUserOut: Number,
})

// Reactive data
const transactions = ref([])
const loading = ref(false)
const pagination = ref({})
const filters = ref({
  type: '',
  dateFrom: '',
  dateTo: ''
})

// Modal states
const showAddToBoxModal = ref(false)
const showWithdrawFromBoxModal = ref(false)

// Form data
const addToBoxForm = ref({
  amount: '',
  note: ''
})

const withdrawFromBoxForm = ref({
  amount: '',
  note: ''
})

// Computed
const netUserBalance = computed(() => {
  return (props.totalUserIn || 0) - (props.totalUserOut || 0)
})

// Methods
const loadTransactions = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page,
      ...filters.value
    })
    
    const response = await axios.get(`/api/transfers/transactions?${params}`)
    transactions.value = response.data.transactions.data
    pagination.value = {
      current_page: response.data.transactions.current_page,
      last_page: response.data.transactions.last_page,
      from: response.data.transactions.from,
      to: response.data.transactions.to,
      total: response.data.transactions.total
    }
  } catch (error) {
    toast.error('حدث خطأ في تحميل المعاملات')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const deleteTransaction = async (transactionId) => {
  if (confirm('هل أنت متأكد من حذف هذه المعاملة؟')) {
    try {
      await axios.delete(`/api/transfers/transaction/${transactionId}`)
      toast.success('تم حذف المعاملة بنجاح')
      loadTransactions(pagination.value.current_page)
    } catch (error) {
      toast.error('حدث خطأ في حذف المعاملة')
      console.error(error)
    }
  }
}

const clearFilters = () => {
  filters.value = {
    type: '',
    dateFrom: '',
    dateTo: ''
  }
  loadTransactions()
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('ar-SA')
}

const confirmAddToBox = async () => {
  if (!addToBoxForm.value.amount || addToBoxForm.value.amount <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح')
    return
  }
  
  try {
    const params = new URLSearchParams({
      amount: addToBoxForm.value.amount,
      note: addToBoxForm.value.note || ''
    })
    
    await axios.get(`/api/addToBox?${params}`)
    toast.success('تم إدخال المبلغ إلى الصندوق بنجاح')
    showAddToBoxModal.value = false
    addToBoxForm.value = { amount: '', note: '' }
    loadTransactions()
    // إعادة تحميل الصفحة لتحديث الإحصائيات
    window.location.reload()
  } catch (error) {
    toast.error('حدث خطأ في إدخال المبلغ')
    console.error(error)
  }
}

const confirmWithdrawFromBox = async () => {
  if (!withdrawFromBoxForm.value.amount || withdrawFromBoxForm.value.amount <= 0) {
    toast.error('يرجى إدخال مبلغ صحيح')
    return
  }
  
  try {
    const params = new URLSearchParams({
      amount: withdrawFromBoxForm.value.amount,
      note: withdrawFromBoxForm.value.note || ''
    })
    
    await axios.get(`/api/withDrawFromBox?${params}`)
    toast.success('تم سحب المبلغ من الصندوق بنجاح')
    showWithdrawFromBoxModal.value = false
    withdrawFromBoxForm.value = { amount: '', note: '' }
    loadTransactions()
    // إعادة تحميل الصفحة لتحديث الإحصائيات
    window.location.reload()
  } catch (error) {
    toast.error('حدث خطأ في سحب المبلغ')
    console.error(error)
  }
}

// Lifecycle
onMounted(() => {
  loadTransactions()
})
</script>
