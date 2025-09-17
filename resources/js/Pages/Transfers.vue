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
              <div class="mt-4 flex flex-wrap gap-4">
                <button @click="showAddToBoxModal = true" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>إدخال إلى الصندوق</span>
                </button>
                
                <button @click="showWithdrawFromBoxModal = true" 
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                  </svg>
                  <span>سحب من الصندوق</span>
                </button>

            

                <!-- User Wallet Buttons -->
                <div v-if="usersWithWallets && usersWithWallets.length > 0" class="flex flex-wrap gap-2">
                  <button v-for="user in usersWithWallets" :key="user.id"
                          @click="viewUserWallet(user.id)"
                          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <span>{{ user.name }}  </span>
                  </button>
                </div>
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

            <!-- Investment Statistics Section -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">إحصائيات الاستثمارات</h3>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- إجمالي الاستثمارات النشطة -->
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-purple-800 dark:text-purple-200">إجمالي الاستثمارات</p>
                      <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                        ${{ Math.round(totalActiveInvestments).toLocaleString() }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- رأس المال بعد الاستثمارات -->
                <div :class="finalRemainingCapital > 0 ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'" 
                     class="rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div :class="finalRemainingCapital > 0 ? 'bg-red-500' : 'bg-green-500'" 
                           class="w-8 h-8 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p :class="finalRemainingCapital > 0 ? 'text-red-800 dark:text-red-200' : 'text-green-800 dark:text-green-200'" 
                         class="text-sm font-medium">
                        {{ finalRemainingCapital > 0 ? 'رأس المال المطلوب' : 'رأس المال كافي' }}
                      </p>
                      <p :class="finalRemainingCapital > 0 ? 'text-red-900 dark:text-red-100' : 'text-green-900 dark:text-green-100'" 
                         class="text-2xl font-bold">
                        ${{ Math.round(finalRemainingCapital).toLocaleString() }}
                      </p>
                      <p v-if="finalRemainingCapital > 0" class="text-xs text-red-600 dark:text-red-400 mt-1">
                        يحتاج مزيد من الاستثمارات
                      </p>
                      <p v-else class="text-xs text-green-600 dark:text-green-400 mt-1">
                        الاستثمارات كافية
                      </p>
                    </div>
                  </div>
                </div>

                <!-- عدد المستثمرين -->
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-green-800 dark:text-green-200">عدد المستثمرين</p>
                      <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                        {{ activeInvestors ? activeInvestors.length : 0 }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- نسبة الاستثمارات -->
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-orange-800 dark:text-orange-200">نسبة الاستثمارات</p>
                      <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                        {{ investmentPercentage.toFixed(1) }}%
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- New Enhanced Statistics Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <!-- رأس المال المتبقي -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">رأس المال المتبقي</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                          ${{ Math.round(remainingCapital).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Progress Bar for Capital -->
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" 
                         :style="{ width: capitalProgressPercentage + '%' }"></div>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">نسبة المدفوع: {{ capitalProgressPercentage.toFixed(1) }}%</p>
                </div>
              </div>

              <!-- إجمالي المدفوعات -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المدفوعات</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                          ${{ Math.round(totalCarPayments).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Progress Bar for Payments -->
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" 
                         :style="{ width: paymentProgressPercentage + '%' }"></div>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">نسبة الاسترداد: {{ paymentProgressPercentage.toFixed(1) }}%</p>
                </div>
              </div>

              <!-- الربح الإجمالي -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center"
                           :class="totalProfit >= 0 ? 'bg-green-500' : 'bg-red-500'">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">الربح الإجمالي</h3>
                        <p class="text-2xl font-bold"
                           :class="totalProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                          ${{ Math.round(totalProfit).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Progress Bar for Profit -->
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full" 
                         :class="totalProfit >= 0 ? 'bg-green-600' : 'bg-red-600'"
                         :style="{ width: profitProgressPercentage + '%' }"></div>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">
                    {{ totalProfit >= 0 ? 'ربح إيجابي' : 'خسارة' }}: {{ profitProgressPercentage.toFixed(1) }}%
                  </p>
                </div>
              </div>
            </div>

            <!-- Grouped Investors Section -->
            <div v-if="groupedInvestors && groupedInvestors.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">المستثمرون النشطون (مجمعة)</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="investor in groupedInvestors" :key="investor.user.id" 
                     class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ (investor.user?.name || 'U').charAt(0) }}</span>
                      </div>
                      <div class="ml-3">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ investor.user?.name || 'مستخدم غير معروف' }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ investor.user?.email || 'بريد غير معروف' }}</p>
                      </div>
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ investor.investmentCount }} استثمار
                    </div>
                  </div>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-600 dark:text-gray-400">إجمالي المستثمر:</span>
                      <span class="font-medium text-gray-900 dark:text-white">${{ Math.round(investor.totalAmount) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-600 dark:text-gray-400">النسبة المئوية:</span>
                      <span class="font-medium text-purple-600 dark:text-purple-400">{{ (investor.totalPercentage || 0).toFixed(2) }}%</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm text-gray-600 dark:text-gray-400">نصيب الربح:</span>
                      <span :class="investor.totalProfitShare >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                            class="font-medium">${{ Math.round(investor.totalProfitShare || 0) }}</span>
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

            <!-- Fund Profit Breakdown Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <!-- ربح المستثمرين -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">ربح المستثمرين</h3>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                          ${{ Math.round(totalInvestorProfit).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    إجمالي الأرباح الموزعة للمستثمرين
                  </p>
                </div>
              </div>

              <!-- ربح السيارات بدون استثمار -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">ربح السيارات بدون استثمار</h3>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                          ${{ Math.round(nonInvestedCarsProfit).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    ربح الصندوق من السيارات غير المستثمر فيها
                  </p>
                </div>
              </div>

              <!-- ربح الصندوق من السيارات المستثمر فيها -->
              <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">ربح الصندوق من الاستثمارات</h3>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                          ${{ Math.round(investedCarsProfit).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    ربح الصندوق من السيارات المستثمر فيها
                  </p>
                </div>
              </div>
            </div>

            <!-- Fund Summary Card -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 mb-8 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold mb-2">ملخص أرباح الصندوق</h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <p class="text-sm opacity-90">إجمالي الربح</p>
                      <p class="text-xl font-bold">${{ Math.round(totalProfit).toLocaleString() }}</p>
                    </div>
                    <div>
                      <p class="text-sm opacity-90">ربح المستثمرين</p>
                      <p class="text-xl font-bold">${{ Math.round(totalInvestorProfit).toLocaleString() }}</p>
                    </div>
                    <div>
                      <p class="text-sm opacity-90">ربح الصندوق الصافي</p>
                      <p class="text-xl font-bold">${{ Math.round(totalProfit - totalInvestorProfit).toLocaleString() }}</p>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cars Needing Investment Completion Section -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-8">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">السيارات التي تحتاج إكمال استثمار</h3>
                  <button @click="loadCarsNeedingCompletion" 
                          :disabled="loadingCarsCompletion"
                          class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-3 py-1 rounded text-sm">
                    {{ loadingCarsCompletion ? 'جاري التحميل...' : 'تحديث' }}
                  </button>
                </div>
              </div>
              
              <!-- Loading State -->
              <div v-if="loadingCarsCompletion" class="p-6 text-center">
                <div class="inline-flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  جاري تحميل السيارات...
                </div>
              </div>
              
              <!-- No Cars Message -->
              <div v-else-if="carsNeedingCompletion.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2">لا توجد سيارات تحتاج إكمال استثمار حالياً</p>
                <p class="text-sm">جميع السيارات المستثمر فيها مكتملة التمويل</p>
              </div>
              
              <!-- Cars List -->
              <div v-else class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div v-for="car in carsNeedingCompletion" :key="car.id" 
                       class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                      <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">السيارة رقم {{ car.no }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ car.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">PIN: {{ car.pin }}</p>
                      </div>
                      <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                          {{ car.investment_percentage.toFixed(1) }}% مستثمر
                        </span>
                      </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                      <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">التكلفة الإجمالية:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${{ car.total_cost.toLocaleString() }}</span>
                      </div>
                      <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">المستثمر:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${{ car.total_invested.toLocaleString() }}</span>
                      </div>
                      <div class="flex justify-between text-sm font-semibold">
                        <span class="text-red-600 dark:text-red-400">المتبقي:</span>
                        <span class="text-red-600 dark:text-red-400">${{ car.remaining_amount.toLocaleString() }}</span>
                      </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                      <div class="bg-orange-500 h-2 rounded-full" 
                           :style="{ width: car.investment_percentage + '%' }"></div>
                    </div>
                    
                    <!-- Investors List -->
                    <div class="mb-3">
                      <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">المستثمرون الحاليون:</p>
                      <div class="space-y-1">
                        <div v-for="investor in car.investors" :key="investor.investor_name" 
                             class="flex justify-between text-xs">
                          <span class="text-gray-600 dark:text-gray-400">{{ investor.investor_name }}</span>
                          <span class="text-gray-900 dark:text-white">${{ investor.invested_amount.toLocaleString() }}</span>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Action Button -->
                    <button @click="completeInvestment(car)" 
                            class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>إكمال الاستثمار</span>
                    </button>
                  </div>
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

    <!-- Car Investment Modal -->
    <div v-if="showCarInvestmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">إضافة استثمار في سيارات</h3>
            <button @click="showCarInvestmentModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المستثمر</label>
            <select v-model="carInvestmentForm.user_id" 
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <option value="">اختر المستثمر</option>
              <option v-for="user in usersWithWallets" :key="user.id" :value="user.id">
                {{ user.name }} ({{ Math.round(user.wallet.balance).toLocaleString() }}$)
              </option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المبلغ الإجمالي</label>
            <input type="number" v-model="carInvestmentForm.amount" 
                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   placeholder="أدخل المبلغ الإجمالي للاستثمار">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ملاحظات</label>
            <textarea v-model="carInvestmentForm.note" 
                      class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      rows="3" placeholder="ملاحظات حول الاستثمار (اختياري)"></textarea>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button @click="showCarInvestmentModal = false" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              إلغاء
            </button>
            <button @click="confirmAddCarInvestment" 
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
              إضافة الاستثمار في السيارات
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
  totalPaidFromCashbox: Number,
  remainingCapital: Number,
  totalCarPayments: Number,
  totalProfit: Number,
  totalSoldCarsCost: Number,
  usersWithWallets: Array,
  totalActiveInvestments: Number,
  activeInvestors: Array,
  groupedInvestors: Array,
  capitalAfterInvestments: Number,
  finalRemainingCapital: Number,
  capitalStatus: String,
  profitStatus: String,
  totalInvestorProfit: Number,
  nonInvestedCarsProfit: Number,
  investedCarsProfit: Number,
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

// Cars needing investment completion
const carsNeedingCompletion = ref([])
const loadingCarsCompletion = ref(false)

// Modal states
const showAddToBoxModal = ref(false)
const showWithdrawFromBoxModal = ref(false)
const showCarInvestmentModal = ref(false)

// Form data
const addToBoxForm = ref({
  amount: '',
  note: ''
})

const withdrawFromBoxForm = ref({
  amount: '',
  note: ''
})

const carInvestmentForm = ref({
  user_id: '',
  amount: '',
  cars: [],
  note: ''
})

// Computed
const netUserBalance = computed(() => {
  return (props.totalUserIn || 0) - (props.totalUserOut || 0)
})

// Progress calculations
const capitalProgressPercentage = computed(() => {
  if (!props.totalCapital || props.totalCapital === 0) return 0
  return Math.min(((props.totalPaidFromCashbox || 0) / props.totalCapital) * 100, 100)
})

const paymentProgressPercentage = computed(() => {
  if (!props.totalCapital || props.totalCapital === 0) return 0
  return Math.min(((props.totalCarPayments || 0) / props.totalCapital) * 100, 100)
})

const profitProgressPercentage = computed(() => {
  if (!props.totalCapital || props.totalCapital === 0) return 0
  const profitRatio = Math.abs(props.totalProfit || 0) / props.totalCapital
  return Math.min(profitRatio * 100, 100)
})

// Investment calculations
const investmentPercentage = computed(() => {
  if (!props.totalCapital || props.totalCapital === 0) return 0
  return ((props.totalActiveInvestments || 0) / props.totalCapital) * 100
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

const viewUserWallet = (userId) => {
  // Redirect to user wallet page
  window.open(`/user-wallet/${userId}`, '_blank')
}

const confirmAddCarInvestment = async () => {
  if (!carInvestmentForm.value.user_id || !carInvestmentForm.value.amount || carInvestmentForm.value.amount <= 0) {
    toast.error('يرجى إدخال جميع البيانات المطلوبة')
    return
  }
  
  try {
    const response = await axios.post('/api/investments/add', carInvestmentForm.value)
    
    if (response.data.success) {
      toast.success(response.data.message)
      showCarInvestmentModal.value = false
      carInvestmentForm.value = { user_id: '', amount: '', cars: [], note: '' }
      // إعادة تحميل الصفحة لتحديث البيانات
      window.location.reload()
    } else {
      toast.error(response.data.error || 'حدث خطأ في إضافة الاستثمار')
    }
  } catch (error) {
    toast.error(error.response?.data?.error || 'حدث خطأ في إضافة الاستثمار')
    console.error(error)
  }
}

const withdrawInvestment = async (investmentId) => {
  if (confirm('هل أنت متأكد من سحب هذا الاستثمار؟ سيتم إرجاع المبلغ إلى محفظة المستثمر.')) {
    try {
      const response = await axios.post(`/api/investments/${investmentId}/withdraw`)
      
      if (response.data.success) {
        toast.success(response.data.message)
        // إعادة تحميل الصفحة لتحديث البيانات
        window.location.reload()
      } else {
        toast.error(response.data.error || 'حدث خطأ في سحب الاستثمار')
      }
    } catch (error) {
      toast.error(error.response?.data?.error || 'حدث خطأ في سحب الاستثمار')
      console.error(error)
    }
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US')
}

// Load cars needing investment completion
const loadCarsNeedingCompletion = async () => {
  loadingCarsCompletion.value = true
  console.log('=== بدء تحميل السيارات التي تحتاج إكمال استثمار ===')
  
  try {
    const response = await axios.get('/getCarsNeedingInvestmentCompletion')
    console.log('استجابة API:', response.data)
    
    if (response.data.success) {
      carsNeedingCompletion.value = response.data.cars
      console.log('تم تحميل السيارات:', carsNeedingCompletion.value)
      console.log('عدد السيارات المحملة:', carsNeedingCompletion.value.length)
      
      if (response.data.debug_info) {
        console.log('معلومات التشخيص:', response.data.debug_info)
      }
    } else {
      console.error('فشل في جلب البيانات:', response.data)
      toast.error('فشل في جلب بيانات السيارات')
    }
  } catch (error) {
    console.error('خطأ في تحميل السيارات التي تحتاج إكمال:', error)
    console.error('تفاصيل الخطأ:', error.response?.data)
    toast.error('حدث خطأ في تحميل السيارات: ' + (error.response?.data?.message || error.message))
  } finally {
    loadingCarsCompletion.value = false
    console.log('=== انتهاء تحميل السيارات التي تحتاج إكمال استثمار ===')
  }
}

// Complete investment for a car
const completeInvestment = (car) => {
  // Redirect to user wallet with pre-filled amount
  const amount = car.remaining_amount
  const description = `إكمال استثمار السيارة رقم ${car.no} - ${car.name}`
  
  // Store car info in session storage for the investment form
  sessionStorage.setItem('investmentCompletion', JSON.stringify({
    carId: car.id,
    carNo: car.no,
    carName: car.name,
    carPin: car.pin, // رقم الشاسي
    amount: amount,
    description: description
  }))
  
  // Redirect to user wallet page
  window.location.href = '/user-wallet'
}

// Load cars on component mount
onMounted(() => {
  loadTransactions()
  loadCarsNeedingCompletion()
})

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

