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
                        <!-- Investment Section -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">الاستثمارات</h3>
                                <button @click="showInvestmentModal = true" 
                                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                    <span>إضافة استثمار</span>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <!-- إجمالي الاستثمارات -->
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
                                                ${{ formatNumber(userInvestments?.totalAmount) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- النسبة من رأس المال -->
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">النسبة من رأس المال</p>
                                            <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">
                                                {{ userInvestments.totalPercentage.toFixed(2) }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- نصيب الربح -->
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-green-800 dark:text-green-200">نصيب الربح</p>
                                            <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                                ${{ formatNumber(userInvestments.totalProfitShare) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- رأس المال - الاستثمار مع اقتراح -->
                                <div :class="capitalInvestmentDifference > 0 ? 'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700' : 'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700'" 
                                     class="rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div :class="capitalInvestmentDifference > 0 ? 'bg-red-500 dark:bg-red-600' : 'bg-blue-500 dark:bg-blue-600'" 
                                                     class="w-10 h-10 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="mr-4 text-right">
                                                <p :class="capitalInvestmentDifference > 0 ? 'text-red-800 dark:text-red-100' : 'text-blue-800 dark:text-blue-100'" 
                                                   class="text-sm font-medium">
                                                    رأس المال - الاستثمار
                                                </p>
                                                <p :class="capitalInvestmentDifference > 0 ? 'text-red-900 dark:text-red-50' : 'text-blue-900 dark:text-blue-50'" 
                                                   class="text-2xl font-bold">
                                                    ${{ formatNumber(capitalInvestmentDifference) }}
                                                </p>
                                                <p v-if="capitalInvestmentDifference > 0" class="text-xs text-red-700 dark:text-red-300 mt-1">
                                                    يحتاج استثمار إضافي
                                                </p>
                                                <p v-else class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                                    الاستثمارات كافية
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="capitalInvestmentDifference > 0" class="text-left">
                                            <p class="text-xs text-gray-600 dark:text-gray-300 mb-2">الاستثمار المقترح</p>
                                            <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                                ${{ formatNumber(suggestedInvestmentAmount) }}
                                            </p>
                                            <button @click="openDirectInvestmentModal" 
                                                    class="mt-2 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 shadow-sm">
                                                استثمر الآن
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- قائمة الاستثمارات النشطة -->
                            <div v-if="userInvestments.activeInvestments.length > 0">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">الاستثمارات النشطة</h4>
                                <div class="space-y-3">
                                    <div v-for="investment in userInvestments.activeInvestments" :key="investment.id"
                                         class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                        استثمار بتاريخ {{ formatDate(investment.created_at) }}
                                                    </span>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ investment.percentage  }}% من رأس المال
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                                        ${{ formatNumber(investment.amount) }}
                                                    </span>
                                                    <span class="text-sm text-green-600 dark:text-green-400">
                                                        ربح: ${{ formatNumber(investment.profit_share) }}
                                                    </span>
                                                </div>
                                                <p v-if="investment.note" class="text-xs text-gray-500 dark:text-gray-400 mt-2 italic">
                                                    {{ investment.note }}
                                                </p>
                                            </div>
                                            <button @click="withdrawUserInvestment(investment.id)" 
                                                    class="ml-4 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                                سحب
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- رسالة عدم وجود استثمارات -->
                            <div v-else class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد استثمارات نشطة</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">ابدأ استثمارك الأول من المحفظة</p>
                            </div>

                            <!-- خيارات السحب السريع -->
                            <div v-if="userInvestments.activeInvestments.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">سحب سريع من الاستثمارات</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <button @click="quickWithdrawFromInvestment('partial')" 
                                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <span>سحب جزئي من الاستثمارات</span>
                                    </button>
                                    <button @click="quickWithdrawFromInvestment('full')" 
                                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                        <span>سحب كامل من الاستثمارات</span>
                                    </button>
                                </div>
                            </div>
                        </div>

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
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3 text-right">
                                            نوع الإضافة
                                        </label>
                                        <div class="space-y-3">
                                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200">
                                                <input type="radio" v-model="addForm.type" value="wallet" 
                                                       class="mr-3 text-green-600 focus:ring-green-500">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                            </svg>
                                                        </div>
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">إضافة إلى الرصيد العادي</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">المبلغ سيضاف إلى الرصيد العام للمحفظة</p>
                                                </div>
                                            </label>
                                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200">
                                                <input type="radio" v-model="addForm.type" value="investment" 
                                                       class="mr-3 text-purple-600 focus:ring-purple-500">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center mr-2">
                                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                            </svg>
                                                        </div>
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">استثمار مباشر</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">المبلغ سيضاف للقاسة ثم يستثمر بنفس القيمة</p>
                                                </div>
                                            </label>
                                        </div>
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

        <!-- Investment Modal -->
        <Modal :show="showInvestmentModal" @close="showInvestmentModal = false">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">إضافة استثمار جديد</h3>
                    <button @click="showInvestmentModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="confirmAddInvestment">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المبلغ</label>
                        <input type="number" v-model="investmentForm.amount" 
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                               placeholder="أدخل مبلغ الاستثمار" required>
                        <p class="text-xs text-gray-500 mt-1">الرصيد المتاح: ${{ formatNumber(userWalletBalance) }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ملاحظات</label>
                        <textarea v-model="investmentForm.note" 
                                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                  rows="3" placeholder="ملاحظات حول الاستثمار (اختياري)"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="showInvestmentModal = false" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            إلغاء
                        </button>
                        <button type="submit" :disabled="investmentLoading"
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 flex items-center space-x-2">
                            <svg v-if="investmentLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ investmentLoading ? 'جاري الإضافة...' : 'إضافة الاستثمار' }}
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
    capital: Number,
    userInvestments: Object,
    capitalInvestmentDifference: Number,
    suggestedInvestmentAmount: Number
})

// Reactive data
const showAddModal = ref(false)
const showWithdrawModal = ref(false)
const showInvestmentModal = ref(false)
const addLoading = ref(false)
const withdrawLoading = ref(false)
const investmentLoading = ref(false)

const addForm = ref({
    amount: '',
    description: '',
    type: 'wallet' // نوع الإضافة: wallet أو investment
})

const withdrawForm = ref({
    amount: '',
    description: ''
})

const investmentForm = ref({
    amount: '',
    note: ''
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
        let response;
        
        if (addForm.value.type === 'investment') {
            // إضافة مباشرة للاستثمار (إضافة للقاسة ثم استثمار نفس المبلغ)
            response = await axios.post('/api/user-wallet/direct-investment', {
                user_id: props.user.id,
                amount: addForm.value.amount,
                note: addForm.value.description
            });
        } else {
            // إضافة للرصيد العادي
            response = await axios.post('/api/user-wallet/add', {
                amount: addForm.value.amount,
                description: addForm.value.description,
                user_id: props.user.id
            });
        }

        if (response.data.success) {
            const message = addForm.value.type === 'investment' ? 'تم إضافة الاستثمار المباشر بنجاح (تم إضافة المبلغ للقاسة ثم استثماره)' : 'تم إضافة المبلغ بنجاح';
            toast.success(message);
            showAddModal.value = false;
            addForm.value = { amount: '', description: '', type: 'wallet' };
            // Refresh the page to get updated data
            window.location.reload();
        } else {
            toast.error(response.data.message || 'حدث خطأ أثناء الإضافة');
        }
    } catch (error) {
        console.error('Error adding to wallet:', error);
        toast.error('حدث خطأ أثناء الإضافة');
    } finally {
        addLoading.value = false;
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

// Investment functions
const confirmAddInvestment = async () => {
    if (!investmentForm.value.amount || investmentForm.value.amount <= 0) {
        toast.error('يرجى إدخال مبلغ صحيح')
        return
    }
    
    if (parseFloat(investmentForm.value.amount) > props.userWalletBalance) {
        toast.error('المبلغ المطلوب أكبر من الرصيد المتاح')
        return
    }
    
    investmentLoading.value = true
    
    try {
        const response = await axios.post('/api/investments/add', {
            user_id: props.user.id,
            amount: investmentForm.value.amount,
            note: investmentForm.value.note
        })
        
        if (response.data.success) {
            toast.success(response.data.message)
            showInvestmentModal.value = false
            investmentForm.value = { amount: '', note: '' }
            // إعادة تحميل الصفحة لتحديث البيانات
            window.location.reload()
        } else {
            toast.error(response.data.error || 'حدث خطأ في إضافة الاستثمار')
        }
    } catch (error) {
        toast.error(error.response?.data?.error || 'حدث خطأ في إضافة الاستثمار')
        console.error(error)
    } finally {
        investmentLoading.value = false
    }
}

// فتح modal الاستثمار مع المبلغ المقترح
const openInvestmentModalWithAmount = () => {
    investmentForm.value.amount = props.suggestedInvestmentAmount.toString()
    investmentForm.value.note = `استثمار مقترح لتغطية رأس المال المتبقي`
    showInvestmentModal.value = true
}

// فتح modal إضافة إلى القاسة مع الخيار المباشر
const openDirectInvestmentModal = () => {
    addForm.value.amount = props.suggestedInvestmentAmount.toString()
    addForm.value.description = `استثمار مقترح لتغطية رأس المال المتبقي`
    addForm.value.type = 'investment' // تحديد الاستثمار المباشر
    showAddModal.value = true
}

const withdrawUserInvestment = async (investmentId) => {
    if (confirm('هل أنت متأكد من سحب هذا الاستثمار؟ سيتم إرجاع المبلغ إلى المحفظة.')) {
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

// وظيفة السحب السريع من الاستثمارات
const quickWithdrawFromInvestment = async (type) => {
    if (!props.userInvestments?.activeInvestments?.length) {
        toast.error('لا توجد استثمارات نشطة للسحب');
        return;
    }

    const totalInvestment = props.userInvestments.totalAmount || 0;
    if (totalInvestment <= 0) {
        toast.error('لا توجد استثمارات للسحب');
        return;
    }

    let amount;
    let confirmMessage;

    if (type === 'full') {
        amount = totalInvestment;
        confirmMessage = `هل أنت متأكد من سحب كامل الاستثمارات ($${formatNumber(amount)})؟`;
    } else {
        // السحب الجزئي - يمكن للمستخدم تحديد المبلغ
        const userAmount = prompt(`أدخل المبلغ للسحب (الحد الأقصى: $${formatNumber(totalInvestment)}):`);
        if (!userAmount || isNaN(userAmount) || parseFloat(userAmount) <= 0) {
            return;
        }
        amount = parseFloat(userAmount);
        if (amount > totalInvestment) {
            toast.error('المبلغ المدخل أكبر من إجمالي الاستثمارات');
            return;
        }
        confirmMessage = `هل أنت متأكد من سحب $${formatNumber(amount)} من الاستثمارات؟`;
    }

    if (!confirm(confirmMessage)) {
        return;
    }

    try {
        // إذا كان السحب كامل، نسحب جميع الاستثمارات
        if (type === 'full') {
            const investmentIds = props.userInvestments.activeInvestments.map(inv => inv.id);
            
            for (const investmentId of investmentIds) {
                await axios.post(`/api/investments/${investmentId}/withdraw`);
            }
            
            toast.success('تم سحب جميع الاستثمارات بنجاح');
        } else {
            // للسحب الجزئي، نحتاج لتحديد أي استثمار نريد سحبه
            // في هذه الحالة، سنسحب من الاستثمار الأول أو نطلب من المستخدم تحديد الاستثمار
            const firstInvestment = props.userInvestments.activeInvestments[0];
            if (amount >= firstInvestment.amount) {
                // سحب كامل للاستثمار الأول
                await axios.post(`/api/investments/${firstInvestment.id}/withdraw`);
                toast.success('تم سحب الاستثمار بنجاح');
            } else {
                toast.error('للسحب الجزئي، يرجى استخدام زر "سحب" المخصص لكل استثمار');
                return;
            }
        }

        // Refresh the page to get updated data
        window.location.reload();
    } catch (error) {
        console.error('Error withdrawing investments:', error);
        toast.error('حدث خطأ أثناء السحب');
    }
}
</script>
