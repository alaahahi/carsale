<template>
    <AuthenticatedLayout>
        <Head title="محفظة المستخدم" />
        
        <div class="py-2">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm">
                    <div class="p-6 dark:bg-gray-900">
                        <!-- Header -->
                        <div class="mb-6">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">قاسة {{ props.user.name }}</h1>
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
                                <div class="flex space-x-2">
                                    <button @click="openCarInvestmentModal" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <span>استثمار في سيارات</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Investment Flow Info -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="mr-3">
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">كيف يعمل الاستثمار؟</h4>
                                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                            <p><strong>الخطوة 1:</strong> أضف المبلغ للقاسة أولاً</p>
                                            <p><strong>الخطوة 2:</strong> استثمر من الرصيد المتاح فقط</p>
                                            <p><strong>الخطوة 3:</strong> يتم سحب المبلغ من القاسة وإيداعه في الصندوق الرئيسي</p>
                                        </div>
                                    </div>
                                </div>
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
                                                {{ userInvestments.totalPercentage.toFixed(2)  }}%
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
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- مؤشر الرصيد المطلوب للاستثمار -->
                                <div v-if="capitalInvestmentDifference > 0 && suggestedInvestmentAmount > userWalletBalance" 
                                     class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-orange-500 dark:bg-orange-600 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="mr-4 text-right">
                                            <p class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                                رصيد غير كافي للاستثمار
                                            </p>
                                            <p class="text-lg font-bold text-orange-900 dark:text-orange-100">
                                                المطلوب: ${{ formatNumber(suggestedInvestmentAmount) }}
                                            </p>
                                            <p class="text-sm text-orange-700 dark:text-orange-300">
                                                المتاح: ${{ formatNumber(userWalletBalance) }}
                                            </p>
                                            <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                                                أضف ${{ formatNumber(suggestedInvestmentAmount - userWalletBalance) }} للقاسة أولاً
                                            </p>
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

                            <!-- قائمة الاستثمارات المؤرشفة (المباعة مع الربح) -->
                            <div v-if="userInvestments.archivedInvestments && userInvestments.archivedInvestments.length > 0" class="mt-8">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <span>الاستثمارات المؤرشفة (مباعة)</span>
                                    <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded-full text-xs font-medium">
                                        {{ userInvestments.archivedInvestments.length }}
                                    </span>
                                </h4>
                                <div class="space-y-3">
                                    <div v-for="investment in userInvestments.archivedInvestments" :key="'archived-' + investment.id"
                                         class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-600">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                        استثمار مؤرشف #{{ investment.id }}
                                                    </span>
                                                    <span class="bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 px-2 py-1 rounded-full text-xs font-bold">
                                                        مباع
                                                    </span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-400">المبلغ المستثمر:</span>
                                                        <span class="font-bold text-gray-900 dark:text-white">${{ formatNumber(investment.amount) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-400">إجمالي الربح:</span>
                                                        <span class="font-bold text-green-600 dark:text-green-400">
                                                            ${{ formatNumber(calculateInvestmentProfit(investment)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-if="investment.note" class="text-xs text-gray-500 dark:text-gray-400 mt-2 italic">
                                                    {{ investment.note }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Car Details for Archived Investment -->
                                        <div v-if="investment.investment_cars && investment.investment_cars.length > 0" class="mt-4 pt-4 border-t border-green-200 dark:border-green-700">
                                            <h5 class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-3">السيارات المباعة:</h5>
                                            <div class="grid grid-cols-1 gap-3">
                                                <div v-for="carInvestment in investment.investment_cars" :key="'archived-car-' + carInvestment.id" 
                                                     class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-green-200 dark:border-green-600">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <p class="font-medium text-gray-900 dark:text-white">
                                                                السيارة رقم {{ carInvestment.car?.no || 'غير معروف' }}
                                                            </p>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                {{ carInvestment.car?.name || 'غير محدد' }}
                                                            </p>
                                                            <p class="text-xs text-green-600 dark:text-green-400">
                                                                PIN: {{ carInvestment.car?.pin || 'غير محدد' }}
                                                            </p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-sm">
                                                                <span class="text-gray-600 dark:text-gray-400">المستثمر:</span>
                                                                <span class="font-bold">${{ formatNumber(carInvestment.invested_amount) }}</span>
                                                            </p>
                                                            <p class="text-sm">
                                                                <span class="text-gray-600 dark:text-gray-400">الربح:</span>
                                                                <span class="font-bold text-green-600 dark:text-green-400">
                                                                    ${{ formatNumber(carInvestment.profit_share || 0) }}
                                                                </span>
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ carInvestment.percentage || 0 }}% نسبة
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                          
                        </div>

                        <!-- Car Investment Profit Details -->
                        <div v-if="userInvestments.activeInvestments && userInvestments.activeInvestments.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">تفاصيل الربح من السيارات</h3>
                            
                            <div class="space-y-4">
                                <div v-for="investment in userInvestments.activeInvestments" :key="investment.id" 
                                     class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">استثمار #{{ investment.id }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ investment.note || 'بدون ملاحظات' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-lg text-gray-900 dark:text-white">${{ formatNumber(investment.amount) }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Car Investment Details -->
                                    <div v-if="investment.investment_cars && investment.investment_cars.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <h5 class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-3">السيارات المستثمرة:</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="carInvestment in investment.investment_cars" :key="carInvestment.id" 
                                                 class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ carInvestment.car?.name || 'سيارة غير معروفة' }}</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">رقم: {{ carInvestment.car?.no || 'غير محدد' }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-500">PIN: {{ carInvestment.car?.pin || 'غير محدد' }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-500">التكلفة الإجمالية: ${{ formatNumber(carInvestment.car?.total_cost) }}</p>
                                                        <p v-if="carInvestment.car?.results != 0" class="text-sm text-gray-500 dark:text-gray-500">سعر البيع: ${{ formatNumber(carInvestment.car?.pay_price) }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-bold text-gray-900 dark:text-white">المستثمر: ${{ formatNumber(carInvestment.invested_amount) }}</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ carInvestment.percentage  }}%</p>
                                                        <p :class="carInvestment.profit_share >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                                                           class="text-sm font-medium">
                                                            ربح السيارة: ${{ formatNumber(carInvestment.car?.profit || 0) }}
                                                        </p>
                                                        <p :class="carInvestment.profit_share >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                                                           class="text-sm font-bold">
                                                            نصيبك: ${{ formatNumber(carInvestment.profit_share) }}
                                                        </p>
                                                        <span :class="carInvestment.car?.results == 0 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'" 
                                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                            {{ carInvestment.car?.results == 0 ? 'في المخزن' : 'مباعة' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                        <!-- Investment Completion Section -->
                        <div v-if="investmentCompletion" class="mb-6 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">إكمال استثمار السيارة</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-sm opacity-90">السيارة رقم</p>
                                            <p class="text-xl font-bold">{{ investmentCompletion.carNo }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm opacity-90">اسم السيارة</p>
                                            <p class="text-xl font-bold">{{ investmentCompletion.carName }}</p>
                                        </div>
                                        <div v-if="investmentCompletion.carPin">
                                            <p class="text-sm opacity-90">رقم الشاسي</p>
                                            <p class="text-lg font-bold">{{ investmentCompletion.carPin }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm opacity-90">المبلغ المطلوب</p>
                                            <p class="text-xl font-bold">${{ formatNumber(investmentCompletion.amount) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button @click="startInvestmentCompletion" 
                                            class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200">
                                        إكمال الاستثمار
                                    </button>
                                </div>
                            </div>
                        </div>

                     

                        <!-- Cars Needing Investment Completion Section -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-8">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">السيارات التي تحتاج إكمال استثمار</h3>
                                        <span v-if="carsNeedingCompletion.length > 0" class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-1 rounded-full text-xs font-medium">
                                            {{ carsNeedingCompletion.length }}
                                        </span>
                                    </div>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="mt-2 font-medium">لا توجد سيارات تحتاج إكمال استثمار</p>
                                <p class="text-sm mt-1">جميع استثماراتك مكتملة أو لا توجد استثمارات جزئية</p>
                                <p class="text-xs mt-1 text-blue-600">المستخدم: {{ props.user.name }} ({{ props.user.id }})</p>
                                <button @click="loadCarsNeedingCompletion" 
                                        class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                                    إعادة التحميل
                                </button>
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
                                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">رقم الشاسي: {{ car.pin }}</p>
                                                <div class="flex gap-2 mt-2">
                                                    <span v-if="car.color" class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded font-medium">{{ car.color }}</span>
                                                    <span v-if="car.model" class="text-xs bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 px-2 py-1 rounded font-medium">{{ car.model }}</span>
                                                    <span v-if="car.source" class="text-xs bg-purple-100 dark:bg-purple-800 text-purple-800 dark:text-purple-200 px-2 py-1 rounded font-medium">{{ car.source }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                    {{ car.investment_percentage ? car.investment_percentage.toFixed(1) : 0 }}% مستثمر
                                                </span>
                                                <div v-if="car.is_user_investor" class="mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        مستثمر حالياً
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mb-4">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">التكلفة الإجمالية:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">${{ Number(car.total_cost || 0).toLocaleString() }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">إجمالي المستثمر:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">${{ Number(car.total_invested || 0).toLocaleString() }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 dark:text-gray-400">استثمارك:</span>
                                                <span class="font-medium text-blue-600 dark:text-blue-400">${{ Number(car.user_invested || 0).toLocaleString() }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm font-semibold">
                                                <span class="text-red-600 dark:text-red-400">المتبقي:</span>
                                                <span class="text-red-600 dark:text-red-400">${{ Number(car.remaining_amount || 0).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Progress Bar -->
                                        <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                            <div class="bg-orange-500 h-2 rounded-full" 
                                                 :style="{ width: (car.investment_percentage || 0) + '%' }"></div>
                                        </div>
                                        
                                        <!-- All Investors List -->
                                        <div class="mb-3" v-if="car.all_investors && car.all_investors.length > 0">
                                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">جميع المستثمرين:</p>
                                            <div class="space-y-1">
                                                <div v-for="investor in car.all_investors" :key="investor.investor_name" 
                                                     class="flex justify-between text-xs">
                                                    <span class="text-gray-600 dark:text-gray-400" 
                                                          :class="{ 'font-semibold text-blue-600 dark:text-blue-400': investor.investor_name === props.user.name }">
                                                        {{ investor.investor_name }}
                                                        <span v-if="investor.investor_name === props.user.name" class="text-blue-500">(أنت)</span>
                                                    </span>
                                                    <span class="text-gray-900 dark:text-white">${{ Number(investor.invested_amount || 0).toLocaleString() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Button -->
                                        <button @click="completeCarInvestment(car)" 
                                                :disabled="completingInvestment"
                                                :class="car.is_user_investor ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700' : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700'"
                                                class="w-full disabled:from-gray-400 disabled:to-gray-500 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2">
                                            <svg v-if="completingInvestment" class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            <span v-if="completingInvestment">جاري المعالجة...</span>
                                            <span v-else-if="car.is_user_investor">إكمال استثماري - ${{ Number(car.remaining_amount || 0).toLocaleString() }}</span>
                                            <span v-else>استثمار جديد - ${{ Number(car.remaining_amount || 0).toLocaleString() }}</span>
                                        </button>
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
            <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-right">إضافة استثمار جديد</h3>
                    <button @click="showInvestmentModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Investment Flow Reminder -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mr-3">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">تذكير مهم</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                سيتم سحب المبلغ من رصيدك الحالي وإيداعه في الصندوق الرئيسي للاستثمار
                            </p>
                        </div>
                    </div>
                </div>
                
                <form @submit.prevent="confirmAddInvestment" class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 text-right">المبلغ *</label>
                        <input type="number" v-model="investmentForm.amount" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 text-right"
                               placeholder="أدخل مبلغ الاستثمار" required>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-right">الرصيد المتاح: ${{ formatNumber(userWalletBalance) }}</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 text-right">ملاحظات</label>
                        <textarea v-model="investmentForm.note" 
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 text-right resize-none"
                                  rows="3" placeholder="ملاحظات حول الاستثمار (اختياري)"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button type="button" @click="showInvestmentModal = false" 
                                class="px-6 py-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-all duration-200 font-medium">
                            إلغاء
                        </button>
                        <button type="submit" :disabled="investmentLoading"
                                class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2 transition-all duration-200 font-medium shadow-lg hover:shadow-xl">
                            <svg v-if="investmentLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ investmentLoading ? 'جاري الإضافة...' : 'إضافة الاستثمار' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Car Investment Modal -->
        <div v-if="showCarInvestmentModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showCarInvestmentModal = false"></div>

                <!-- Modal panel -->
                <div class="relative w-[95vw] max-w-[95vw] h-[95vh] max-h-[95vh] bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-2xl transform transition-all flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-right">الاستثمار في سيارات محددة</h3>
                    <button @click="showCarInvestmentModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Investment Flow Info -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="mr-3">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">كيف يعمل الاستثمار في السيارات؟</h4>
                                <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                    <p><strong>الخطوة 1:</strong> تأكد من وجود رصيد كافي في القاسة</p>
                                    <p><strong>الخطوة 2:</strong> اختر السيارات والمبالغ المراد استثمارها</p>
                                    <p><strong>الخطوة 3:</strong> سيتم سحب المبلغ من القاسة وإيداعه في الصندوق الرئيسي</p>
                                    <p><strong>الخطوة 4:</strong> عند بيع السيارة، ستحصل على رأس مالك + ربحك</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">

                <!-- Search Cars -->
                <div class="mb-8">
                    <div class="relative">
                        <input
                            v-model="carSearchTerm"
                            @input="searchCars"
                            type="text"
                            placeholder="البحث عن سيارة بالاسم أو الرقم التسلسلي..."
                            class="w-full px-4 py-4 pr-12 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-right shadow-sm">
                        <svg class="absolute right-4 top-4 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Available Cars Grid -->
                <div class="mb-8">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6 text-right">السيارات المتاحة للاستثمار</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-4 pr-2">
                        <div v-for="car in availableCars" :key="car.id" 
                             :class="[
                                 'border border-gray-200 dark:border-gray-600 rounded-xl p-5 hover:shadow-lg transition-all duration-300 bg-gray-50 dark:bg-gray-700 hover:bg-white dark:hover:bg-gray-600',
                                 car.isRemoving ? 'opacity-0 scale-95 transform -translate-y-2' : 'opacity-100 scale-100 transform translate-y-0'
                             ]">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-bold text-gray-900 dark:text-white text-right">السيارة رقم {{ car.no }}</h5>
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <span :class="car.results == 0 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'" 
                                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ car.results == 0 ? 'في المخزن' : 'مباعة' }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded-full">{{ car.name }}</span>
                                </div>
                            </div>
                            
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-3 space-y-1">
                                <p class="text-right"><strong>الرقم التسلسلي:</strong> {{ car.pin }}</p>
                                <p class="text-right"><strong>اللون:</strong> {{ car.color }}</p>
                                <p class="text-right"><strong>السنة:</strong> {{ car.model }}</p>
                            </div>
                            
                            <div class="text-sm mb-4 space-y-1">
                                <p class="text-right"><strong>التكلفة الإجمالية:</strong> ${{ Number(car.total_cost || 0).toLocaleString('en-US') }}</p>
                                <p class="text-right"><strong>الاستثمارات الحالية:</strong> ${{ Number(car.total_investments || 0).toLocaleString('en-US') }}</p>
                                <p class="text-right text-green-600 dark:text-green-400"><strong>المتاح للاستثمار:</strong> ${{ Number(car.available_for_investment || 0).toLocaleString('en-US') }}</p>
                                <p class="text-right text-blue-600 dark:text-blue-400 text-xs"><strong>الحد الأقصى للاستثمار:</strong> ${{ Number(car.total_cost || 0).toLocaleString('en-US') }}</p>
                                
                                <!-- عرض معلومات البيع للسيارات المباعة -->
                                <div v-if="car.results != 0" class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <p class="text-right"><strong>سعر البيع:</strong> ${{ Number(car.pay_price || 0).toLocaleString('en-US') }}</p>
                                    <p :class="car.profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                                       class="text-right font-bold">
                                        <strong>الربح:</strong> ${{ Number(car.profit || 0).toLocaleString('en-US') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 text-right">سيتم حساب نصيبك من الربح فوراً</p>
                                </div>
                            </div>

                            <!-- Current Investors -->
                            <div v-if="car.current_investors && car.current_investors.length > 0" class="mb-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 text-right">المستثمرون الحاليون:</p>
                                <div class="flex flex-wrap gap-1 justify-end">
                                    <span v-for="investor in car.current_investors" :key="investor.id"
                                          class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full">
                                        {{ investor.name }} ({{ Number(investor.percentage || 0).toFixed(1) }}%)
                                    </span>
                                </div>
                            </div>

                            <!-- Investment Amount Input -->
                            <div class="space-y-2">
                                <!-- Full Price Button -->
                                <button
                                    @click="setFullPriceInvestment(car)"
                                    :disabled="car.isRemoving || car.total_cost <= 0 || car.available_for_investment <= 0"
                                    class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all duration-200 font-medium">
                                    {{ car.available_for_investment > 0 ? `المبلغ المتاح ($${Number(car.available_for_investment || 0).toLocaleString('en-US')})` : 'مستثمر بالكامل' }}
                                </button>
                                
                                <!-- Input Field -->
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <button
                                        @click="addCarToInvestment(car)"
                                        :disabled="!car.investmentAmount || car.investmentAmount <= 0 || car.investmentAmount > car.available_for_investment || car.isRemoving"
                                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all duration-200 font-medium">
                                        {{ car.isRemoving ? 'جاري الإضافة...' : 'إضافة' }}
                                    </button>
                                    <input
                                        v-model="car.investmentAmount"
                                        @input="validateInvestmentAmount(car)"
                                        type="number"
                                        :max="car.available_for_investment"
                                        :min="0.01"
                                        step="0.01"
                                        placeholder="مبلغ الاستثمار"
                                        :disabled="car.isRemoving"
                                        class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-right transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                </div>
                                
                                <!-- Validation Message -->
                                <div v-if="car.investmentAmount && car.investmentAmount > car.available_for_investment" 
                                     class="text-xs text-red-600 dark:text-red-400 text-right">
                                    المبلغ يتجاوز المبلغ المتاح للاستثمار
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Cars -->
                <div v-if="selectedCars.length > 0" class="mb-8">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-6 text-right">السيارات المختارة للاستثمار</h4>
                    <div class="space-y-3">
                        <div v-for="(selectedCar, index) in selectedCars" :key="selectedCar.id"
                             class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-700">
                            <div class="text-right">
                                <span class="font-bold text-gray-900 dark:text-white block">السيارة رقم {{ selectedCar.no }} - {{ selectedCar.name }}</span>
                                <span class="text-sm text-blue-600 dark:text-blue-400 mt-1 block">
                                    مبلغ الاستثمار: ${{ Number(selectedCar.investmentAmount).toLocaleString('en-US') }}
                                </span>
                            </div>
                            <button @click="removeCarFromInvestment(index)"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Investment Summary -->
                <div v-if="selectedCars.length > 0" class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4 text-right">ملخص الاستثمار</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <div class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 text-right">عدد السيارات:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400 text-xl">{{ selectedCars.length }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 text-right">إجمالي المبلغ:</span>
                            <span class="font-bold text-green-600 dark:text-green-400 text-xl">${{ totalSelectedAmount.toLocaleString('en-US') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 text-right">الرصيد الحالي:</span>
                            <span class="font-bold text-gray-600 dark:text-gray-400 text-xl">${{ Number(userWalletBalance).toLocaleString('en-US') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 text-right">الرصيد بعد الاستثمار:</span>
                            <span class="font-bold text-purple-600 dark:text-purple-400 text-xl">${{ (Number(userWalletBalance) - totalSelectedAmount).toLocaleString('en-US') }}</span>
                        </div>
                        <div v-if="totalSelectedAmount > userWalletBalance" class="col-span-full">
                            <div class="flex justify-between items-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg shadow-sm border border-orange-200 dark:border-orange-700">
                                <span class="font-semibold text-orange-700 dark:text-orange-300 text-right">المبلغ المطلوب من الصندوق:</span>
                                <span class="font-bold text-orange-600 dark:text-orange-400 text-xl">${{ (totalSelectedAmount - userWalletBalance).toLocaleString('en-US') }}</span>
                            </div>
                            <div class="mt-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="mr-3">
                                        <h4 class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">تحذير: رصيد غير كافي</h4>
                                        <p class="text-sm text-red-700 dark:text-red-300">
                                            رصيدك الحالي (${{ Number(userWalletBalance).toLocaleString('en-US') }}) أقل من المبلغ المطلوب للاستثمار. 
                                            أضف المبلغ المطلوب للقاسة أولاً قبل المتابعة.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Note -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 text-right">
                        ملاحظة (اختيارية)
                    </label>
                    <textarea
                        v-model="carInvestmentForm.note"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-right resize-none"
                        placeholder="أضف ملاحظة حول الاستثمار..."></textarea>
                </div>

                </div>
                
                <!-- Footer -->
                <div class="flex justify-end space-x-4 space-x-reverse p-6 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                    <button @click="showCarInvestmentModal = false"
                            class="px-8 py-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-400 dark:hover:bg-gray-500 transition-all duration-200 font-medium">
                        إلغاء
                    </button>
                    <button @click="createCarInvestment"
                            :disabled="selectedCars.length === 0 || carInvestmentLoading || totalSelectedAmount > userWalletBalance"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl flex flex-col items-center space-y-1">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <svg v-if="carInvestmentLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ carInvestmentLoading ? 'جاري إنشاء الاستثمار...' : (totalSelectedAmount > userWalletBalance ? 'أضف رصيد أولاً' : 'إنشاء الاستثمار') }}</span>
                        </div>
                        <span v-if="totalSelectedAmount > userWalletBalance && !carInvestmentLoading" class="text-xs opacity-90">
                            (أضف ${{ (totalSelectedAmount - userWalletBalance).toLocaleString('en-US') }} للقاسة أولاً)
                        </span>
                        <span v-else-if="totalSelectedAmount <= userWalletBalance && !carInvestmentLoading" class="text-xs opacity-90">
                            (سيتم سحب المبلغ من القاسة)
                        </span>
                    </button>
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
const showCarInvestmentModal = ref(false)
const addLoading = ref(false)
const withdrawLoading = ref(false)
const availableCars = ref([])
const selectedCars = ref([])
const carSearchTerm = ref('')
const carInvestmentLoading = ref(false)
const investmentLoading = ref(false)

// Investment completion data
const investmentCompletion = ref(null)

// Cars needing completion data
const carsNeedingCompletion = ref([])
const loadingCarsCompletion = ref(false)
const completingInvestment = ref(false)

const addForm = ref({
    amount: '',
    description: ''
})

const withdrawForm = ref({
    amount: '',
    description: ''
})

const investmentForm = ref({
    amount: '',
    note: ''
})

const carInvestmentForm = ref({
    totalAmount: '',
    note: ''
})

// Computed
const totalIn = computed(() => {
    return props.userTransactions
        .filter(t => t.type === 'user_in' || t.type === 'investor_profit')
        .reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0)
})

const totalOut = computed(() => {
    return props.userTransactions
        .filter(t => t.type === 'user_out' || t.type === 'investment')
        .reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0)
})

// Methods
const formatNumber = (number) => {
    if (number === null || number === undefined || isNaN(number)) {
        return '0'
    }
    return Math.round(Number(number)).toLocaleString()
}

// حساب إجمالي الربح للاستثمار
const calculateInvestmentProfit = (investment) => {
    if (!investment.investment_cars) return 0
    
    return investment.investment_cars.reduce((total, carInvestment) => {
        return total + (carInvestment.profit_share || 0)
    }, 0)
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
        case 'investor_profit':
            return 'bg-blue-100 text-blue-800'
        case 'investment':
            return 'bg-purple-100 text-purple-800'
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
        case 'investor_profit':
            return 'ربح مستثمر'
        case 'investment':
            return 'استثمار'
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
        case 'investor_profit':
            return 'text-blue-600'
        case 'investment':
            return 'text-purple-600'
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
        case 'investor_profit':
            return '+'
        case 'investment':
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

    if (!props.user || !props.user.id) {
        toast.error('معلومات المستخدم غير متوفرة')
        return
    }

    addLoading.value = true

    try {
        // إضافة للرصيد العادي فقط
        const response = await axios.post('/api/user-wallet/add', {
            amount: addForm.value.amount,
            description: addForm.value.description,
            user_id: props.user.id
        });

        if (response.data.success) {
            toast.success('تم إضافة المبلغ بنجاح');
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

// فتح modal إضافة إلى القاسة مع المبلغ المقترح
const openDirectInvestmentModal = () => {
    addForm.value.amount = props.suggestedInvestmentAmount.toString()
    addForm.value.description = `استثمار مقترح لتغطية رأس المال المتبقي`
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

// Car Investment Functions
const openCarInvestmentModal = async () => {
    showCarInvestmentModal.value = true
    await loadAvailableCars()
}

const loadAvailableCars = async () => {
    try {
        const response = await axios.get('/api/cars/available-for-investment')
        availableCars.value = response.data.cars.map(car => ({
            ...car,
            investmentAmount: '',
            isRemoving: false
        }))
    } catch (error) {
        console.error('Error loading cars:', error)
        toast.error('حدث خطأ أثناء تحميل السيارات')
    }
}

const searchCars = async () => {
    try {
        const response = await axios.get(`/api/cars/available-for-investment?search=${carSearchTerm.value}`)
        availableCars.value = response.data.cars.map(car => ({
            ...car,
            investmentAmount: '',
            isRemoving: false
        }))
    } catch (error) {
        console.error('Error searching cars:', error)
        toast.error('حدث خطأ أثناء البحث')
    }
}

const addCarToInvestment = (car) => {
    if (!car.investmentAmount || car.investmentAmount <= 0) {
        toast.error('يرجى إدخال مبلغ صحيح للاستثمار')
        return
    }
    
    if (car.investmentAmount > car.available_for_investment) {
        toast.error('المبلغ يتجاوز المبلغ المتاح للاستثمار')
        return
    }
    
    // التحقق من عدم وجود السيارة في القائمة
    if (selectedCars.value.some(selected => selected.id === car.id)) {
        toast.error('هذه السيارة مختارة بالفعل')
        return
    }
    
    // إضافة السيارة للقائمة المختارة
    selectedCars.value.push({
        ...car,
        investmentAmount: parseFloat(car.investmentAmount)
    })
    
    // إزالة السيارة من القائمة المتاحة مع انيميشن
    const carIndex = availableCars.value.findIndex(c => c.id === car.id)
    if (carIndex !== -1) {
        // إضافة انيميشن الاختفاء
        availableCars.value[carIndex].isRemoving = true
        
        setTimeout(() => {
            availableCars.value.splice(carIndex, 1)
        }, 300)
    }
    
    // مسح حقل الإدخال
    car.investmentAmount = ''
    
    // عرض رسالة نجاح
    toast.success('تم إضافة السيارة للاستثمار - بانتظار تأكيد وإنشاء الاستثمار')
}

// دالة تعيين السعر الكامل للاستثمار
const setFullPriceInvestment = (car) => {
    if (car.total_cost <= 0) {
        toast.error('سعر السيارة غير صحيح')
        return
    }
    
    // إذا كانت السيارة مستثمرة 100%، استخدم المبلغ المتاح للاستثمار
    if (car.available_for_investment <= 0) {
        toast.warning('هذه السيارة مستثمرة بالكامل')
        return
    }
    
    car.investmentAmount = car.available_for_investment
    toast.success(`تم تعيين المبلغ المتاح للاستثمار للسيارة رقم ${car.no}`)
}

// دالة التحقق من صحة مبلغ الاستثمار
const validateInvestmentAmount = (car) => {
    if (car.investmentAmount && car.investmentAmount > car.available_for_investment) {
        // منع الإدخال إذا تجاوز المبلغ المتاح للاستثمار
        setTimeout(() => {
            car.investmentAmount = car.available_for_investment
            toast.warning('تم تعديل المبلغ للمبلغ المتاح للاستثمار')
        }, 100)
    }
}

const removeCarFromInvestment = (index) => {
    const removedCar = selectedCars.value[index]
    selectedCars.value.splice(index, 1)
    
    // إعادة السيارة للقائمة المتاحة
    availableCars.value.push({
        ...removedCar,
        investmentAmount: '',
        isRemoving: false
    })
    
    toast.success('تم إزالة السيارة من الاستثمار')
}

const totalSelectedAmount = computed(() => {
    return selectedCars.value.reduce((total, car) => total + (parseFloat(car.investmentAmount) || 0), 0)
})

const createCarInvestment = async () => {
    if (selectedCars.value.length === 0) {
        toast.error('يرجى اختيار سيارة واحدة على الأقل')
        return
    }
    
    const totalAmount = totalSelectedAmount.value
    
    carInvestmentLoading.value = true
    
    try {
        // إنشاء الاستثمار المباشر في السيارات (معاملة واحدة فقط)
        const response = await axios.post('/api/user-wallet/direct-car-investment', {
            user_id: props.user.id,
            amount: totalAmount,
            cars: selectedCars.value.map(car => ({
                car_id: car.id,
                amount: car.investmentAmount
            })),
            note: carInvestmentForm.value.note || 'استثمار مباشر في سيارات محددة'
        })
        
        // التحقق من وجود سيارات مباعة في الاستثمار
        const soldCars = selectedCars.value.filter(car => car.results != 0)
        if (soldCars.length > 0) {
            toast.success('تم إنشاء الاستثمار في السيارات بنجاح! تم حساب الربح تلقائياً للسيارات المباعة')
        } else {
            toast.success(response.data.message || 'تم إنشاء الاستثمار في السيارات بنجاح')
        }
        
        showCarInvestmentModal.value = false
        selectedCars.value = []
        carInvestmentForm.value = { totalAmount: '', note: '' }
        
        // إعادة تحميل الصفحة لتحديث البيانات
        window.location.reload()
    } catch (error) {
        console.error('Error creating car investment:', error)
        toast.error(error.response?.data?.message || 'حدث خطأ أثناء إنشاء الاستثمار')
    } finally {
        carInvestmentLoading.value = false
    }
}

// Investment completion functions
const startInvestmentCompletion = () => {
    if (investmentCompletion.value) {
        // Pre-fill the add form with the required amount
        addForm.value.amount = investmentCompletion.value.amount.toString()
        addForm.value.description = investmentCompletion.value.description
        showAddModal.value = true
    }
}

// Check for investment completion data on mount
onMounted(() => {
    const completionData = sessionStorage.getItem('investmentCompletion')
    if (completionData) {
        try {
            investmentCompletion.value = JSON.parse(completionData)
            // Clear the session storage after reading
            sessionStorage.removeItem('investmentCompletion')
        } catch (error) {
            console.error('Error parsing investment completion data:', error)
        }
    }
    
    // Load cars needing completion on mount
    loadCarsNeedingCompletion()
})

// Load cars needing investment completion
const loadCarsNeedingCompletion = async () => {
    loadingCarsCompletion.value = true
    console.log('=== بدء تحميل السيارات التي تحتاج إكمال استثمار ===')
    
    try {
        const response = await axios.get(`/api/user-wallet/cars-needing-completion/${props.user.id}`)
        console.log('استجابة API:', response.data)
        
        if (response.data.success) {
            carsNeedingCompletion.value = response.data.cars
            // Force reactivity update
            carsNeedingCompletion.value = [...response.data.cars]
            
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

// Complete car investment
const completeCarInvestment = async (car) => {
    if (completingInvestment.value) return
    
    const confirmed = confirm(`هل أنت متأكد من إكمال استثمار السيارة رقم ${car.no}؟\nسيتم تحويل $${car.remaining_amount.toLocaleString()} من الصندوق إلى قاستك.`)
    
    if (!confirmed) return
    
    completingInvestment.value = true
    console.log('=== بدء إكمال استثمار السيارة ===', car)
    
    try {
        const response = await axios.post('/api/user-wallet/complete-car-investment', {
            car_id: car.id,
            amount: car.remaining_amount,
            user_id: props.user.id // إضافة معرف المستخدم من props
        })
        
        console.log('استجابة إكمال الاستثمار:', response.data)
        
        if (response.data.success) {
            toast.success(response.data.message)
            
            // إعادة تحميل السيارات التي تحتاج إكمال
            await loadCarsNeedingCompletion()
            
            // إعادة تحميل الصفحة لتحديث الرصيد والمعاملات
            setTimeout(() => {
                window.location.reload()
            }, 2000)
        } else {
            console.error('فشل في إكمال الاستثمار:', response.data)
            toast.error(response.data.message || 'فشل في إكمال الاستثمار')
        }
    } catch (error) {
        console.error('خطأ في إكمال الاستثمار:', error)
        console.error('تفاصيل الخطأ:', error.response?.data)
        toast.error('حدث خطأ في إكمال الاستثمار: ' + (error.response?.data?.message || error.message))
    } finally {
        completingInvestment.value = false
        console.log('=== انتهاء إكمال استثمار السيارة ===')
    }
}
</script>
