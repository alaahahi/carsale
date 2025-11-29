<template>
    <AuthenticatedLayout>
        <Head title="العملاء" />
        
        <div class="py-2">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm">
                    <div class="p-6 dark:bg-gray-900">
                        <!-- Header -->
                        <div class="mb-6">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">العملاء</h1>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">إدارة بيانات العملاء والمعاملات المالية</p>
                            
                            <!-- Action Buttons -->
                            <div class="mt-4 flex gap-4">
                                <button @click="showAddClientModal" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>إضافة عميل جديد</span>
                                </button>
                                
                                <button @click="printClientsReport" 
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    <span>طباعة التقرير</span>
                                </button>
                                
                                <button @click="goToExternalMerchant" 
                                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                    <span>معلومات التاجر (المشروع الثاني)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <!-- إجمالي العملاء -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي العملاء</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ stats.total_clients || 0 }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- إجمالي المطلوب -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي المطلوب</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    ${{ formatNumber(stats.total_required || 0) }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- إجمالي المدفوع -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي المدفوع</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    ${{ formatNumber(stats.total_paid || 0) }}
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
                                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">إجمالي الدين</dt>
                                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                    ${{ formatNumber(stats.total_debt || 0) }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <!-- البحث بالاسم -->
                            <div>
                                <input
                                    v-model="searchName"
                                    @input="applyFilters"
                                    type="text"
                                    placeholder="البحث بالاسم..."
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                            </div>
                            
                            <!-- البحث برقم الهاتف -->
                            <div>
                                <input
                                    v-model="searchPhone"
                                    @input="applyFilters"
                                    type="text"
                                    placeholder="البحث برقم الهاتف..."
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                            </div>
                            
                            <!-- فلتر حالة العميل -->
                            <div>
                                <select v-model="statusFilter" @change="applyFilters" 
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">جميع الحالات</option>
                                    <option value="debtor">مدين</option>
                                    <option value="paid">مدفوع بالكامل</option>
                                    <option value="credit">لديه رصيد</option>
                                </select>
                            </div>
                            
                            <!-- زر إعادة تعيين الفلاتر -->
                            <div>
                                <button @click="resetFilters" 
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <span>إعادة تعيين</span>
                                </button>
                            </div>
                        </div>

                        <!-- Clients Table -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">قائمة العملاء</h3>
                            </div>
                            
                            <div v-if="loading" class="flex justify-center items-center py-12">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                            </div>

                            <div v-else-if="clientsData.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">لا يوجد عملاء</h3>
                                <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة عميل جديد.</p>
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الاسم</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الهاتف</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المطلوب</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المدفوع</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">المتبقي</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الحالة</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="client in clientsData" :key="client.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ client.id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ client.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ client.phone || 'غير محدد' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                ${{ formatNumber(client.total_required) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                ${{ formatNumber(client.total_paid) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="getDebtColor(client.remaining_debt)">
                                                ${{ formatNumber(client.remaining_debt) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getClientStatus(client.remaining_debt).class">
                                                    {{ getClientStatus(client.remaining_debt).text }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        v-if="client.email !== 'admin@admin.com' && client.show_wallet"
                                                        @click="goToUserWallet(client.id)"
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 px-3 py-1 rounded-md bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 transition-colors duration-200">
                                                        قاسة
                                                    </button>
                                                    <button
                                                        v-if="client.email !== 'admin@admin.com' && !client.show_wallet"
                                                        @click="createWalletForClient(client)"
                                                        :disabled="creatingWallet"
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-1 rounded-md bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 transition-colors duration-200 disabled:opacity-50">
                                                        {{ creatingWallet ? 'جاري الإنشاء...' : 'إنشاء قاسة' }}
                                                    </button>
                                                    <button
                                                        v-if="client.email !== 'admin@admin.com' && client.show_wallet && client.total_profit_share > 0"
                                                        @click="showProfitEditModal(client)"
                                                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 px-3 py-1 rounded-md bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:hover:bg-purple-900/30 transition-colors duration-200">
                                                        تعديل الربح
                                                    </button>
                                                    <button
                                                        v-if="client.email !== 'admin@admin.com'"
                                                        @click="showClientEditModal(client)"
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-1 rounded-md bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 transition-colors duration-200">
                                                        تعديل
                                                    </button>
                                                    <button 
                                                        v-if="client.email !== 'admin@admin.com'"
                                                        @click="destroy(client.id)"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 px-3 py-1 rounded-md bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 transition-colors duration-200">
                                                        حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div v-if="clientsData.length > 0" class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <button 
                                        @click="previousPage" 
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        السابق
                                    </button>
                                    <button 
                                        @click="nextPage" 
                                        :disabled="currentPage === totalPages"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        التالي
                                    </button>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            عرض
                                            <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                                            إلى
                                            <span class="font-medium">{{ Math.min(currentPage * perPage, totalClients) }}</span>
                                            من
                                            <span class="font-medium">{{ totalClients }}</span>
                                            نتيجة
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <button 
                                                @click="previousPage" 
                                                :disabled="currentPage === 1"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="sr-only">السابق</span>
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            
                                            <template v-for="page in visiblePages" :key="page">
                                                <button 
                                                    v-if="page !== '...'"
                                                    @click="goToPage(page)"
                                                    :class="[
                                                        page === currentPage 
                                                            ? 'z-10 bg-blue-50 dark:bg-blue-900/20 border-blue-500 text-blue-600 dark:text-blue-400' 
                                                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                                    ]">
                                                    {{ page }}
                                                </button>
                                                <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    ...
                                                </span>
                                            </template>
                                            
                                            <button 
                                                @click="nextPage" 
                                                :disabled="currentPage === totalPages"
                                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="sr-only">التالي</span>
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نافذة تعديل العميل -->
        <Modal :show="isEditModalVisible" @close="hideClientEditModal">
            <div class="bg-white dark:bg-gray-800 px-6 py-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">تعديل بيانات العميل</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">قم بتحديث المعلومات المطلوبة</p>
                        </div>
                    </div>
                    <button @click="hideClientEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateClient" class="space-y-6">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                            <span class="text-red-500">*</span> اسم العميل
                        </label>
                        <input
                            type="text"
                            id="edit_name"
                            v-model="editForm.name"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-lg"
                            placeholder="أدخل اسم العميل">
                    </div>

                    <div>
                        <label for="edit_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                            رقم الهاتف
                        </label>
                        <input
                            type="text"
                            id="edit_phone"
                            v-model="editForm.phone"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-lg"
                            placeholder="أدخل رقم الهاتف">
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="hideClientEditModal"
                            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 font-medium">
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="editLoading"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 font-medium">
                            <svg v-if="editLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ editLoading ? 'جاري التحديث...' : 'تحديث البيانات' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- نافذة إضافة العميل -->
        <Modal :show="isAddModalVisible" @close="hideAddClientModal">
            <div class="bg-white dark:bg-gray-800 px-6 py-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">إضافة عميل جديد</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">أدخل معلومات العميل الجديد</p>
                        </div>
                    </div>
                    <button @click="hideAddClientModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="addClient" class="space-y-6">
                    <div>
                        <label for="add_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                            <span class="text-red-500">*</span> اسم العميل
                        </label>
                        <input
                            type="text"
                            id="add_name"
                            v-model="addForm.name"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 text-lg"
                            placeholder="أدخل اسم العميل">
                    </div>

                    <div>
                        <label for="add_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right">
                            رقم الهاتف
                        </label>
                        <input
                            type="text"
                            id="add_phone"
                            v-model="addForm.phone"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 text-lg"
                            placeholder="أدخل رقم الهاتف">
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="hideAddClientModal"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ addLoading ? 'جاري الإضافة...' : 'إضافة العميل' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Profit Edit Modal -->
        <Modal :show="showProfitEdit" @close="hideProfitEditModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">تعديل نسبة الربح</h3>
                    <button @click="hideProfitEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div v-if="selectedClient" class="space-y-6">
                    <!-- Client Info -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">معلومات العميل</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">الاسم:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ selectedClient.name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">إجمالي الربح الحالي:</span>
                                <span class="font-bold text-green-600 dark:text-green-400">${{ formatNumber(selectedClient.total_profit_share) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Investment Details -->
                    <div v-if="clientInvestments.length > 0">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">تفاصيل الاستثمارات</h4>
                        <div class="space-y-3">
                            <div v-for="investment in clientInvestments" :key="investment.id" 
                                 class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">استثمار #{{ investment.id }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ investment.note || 'بدون ملاحظات' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-lg text-gray-900 dark:text-white">${{ formatNumber(investment.amount) }}</p>
                                    </div>
                                </div>

                                <!-- Car Investments -->
                                <div v-if="investment.investment_cars && investment.investment_cars.length > 0" class="space-y-2">
                                    <div v-for="carInvestment in investment.investment_cars" :key="carInvestment.id" 
                                         class="bg-gray-50 dark:bg-gray-600 rounded-lg p-3">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ carInvestment.car?.name || 'سيارة غير معروفة' }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">رقم: {{ carInvestment.car?.no || 'غير محدد' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-gray-900 dark:text-white">المستثمر: ${{ formatNumber(carInvestment.invested_amount) }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ carInvestment.percentage }}%</p>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">نصيب الربح:</span>
                                                    <input 
                                                        v-model="carInvestment.profit_share"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-right">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">$</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        لا توجد استثمارات لهذا العميل
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="hideProfitEditModal"
                        class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 font-medium">
                        إلغاء
                    </button>
                    <button
                        type="button"
                        @click="saveProfitChanges"
                        :disabled="profitEditLoading"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 font-medium">
                        <svg v-if="profitEditLoading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        {{ profitEditLoading ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
                    </button>
                </div>
            </div>
        </Modal>
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
    clients: Array,
    totalClients: Number,
    currentPage: Number,
    perPage: Number,
    totalPages: Number,
    stats: Object,
    systemConfig: Object
})

// Reactive data
const loading = ref(false)
const clientsData = ref(props.clients || [])
const isEditModalVisible = ref(false)
const isAddModalVisible = ref(false)
const editLoading = ref(false)
const addLoading = ref(false)
const creatingWallet = ref(false)

// Profit edit modal
const showProfitEdit = ref(false)
const selectedClient = ref(null)
const clientInvestments = ref([])
const profitEditLoading = ref(false)

// Search filters
const searchName = ref('')
const searchPhone = ref('')
const statusFilter = ref('')

// Pagination
const currentPage = ref(props.currentPage || 1)
const perPage = ref(props.perPage || 10)
const totalClients = ref(props.totalClients || 0)
const totalPages = ref(props.totalPages || 1)
const stats = ref(props.stats || {})

// Forms
const editForm = ref({
    id: null,
    name: '',
    phone: ''
})

const addForm = ref({
    name: '',
    phone: ''
})

// Computed
const visiblePages = computed(() => {
    const pages = []
    const start = Math.max(1, currentPage.value - 2)
    const end = Math.min(totalPages.value, currentPage.value + 2)
    
    if (start > 1) {
        pages.push(1)
        if (start > 2) {
            pages.push('...')
        }
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i)
    }
    
    if (end < totalPages.value) {
        if (end < totalPages.value - 1) {
            pages.push('...')
        }
        pages.push(totalPages.value)
    }
    
    return pages
})

// Methods
const formatNumber = (number) => {
    return Math.round(number).toLocaleString('en-US')
}

const formatNumberEnglish = (number) => {
    return Math.round(number).toLocaleString('en-US')
}

const getDebtColor = (debt) => {
    if (debt === 0) return 'text-green-600'
    if (debt > 0) return 'text-red-600'
    return 'text-gray-600'
}

const getClientStatus = (debt) => {
    if (debt === 0) {
        return { text: 'مدفوع بالكامل', class: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' }
    } else if (debt > 0) {
        return { text: 'مدين', class: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }
    } else {
        return { text: 'لديه رصيد', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' }
    }
}

const goToUserWallet = (userId) => {
    window.location.href = `/user-wallet/${userId}`
}

const createWalletForClient = async (client) => {
    creatingWallet.value = true
    
    try {
        const response = await axios.post('/api/customer-wallet/create', {
            customer_id: client.id
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

const goToExternalMerchant = () => {
    window.location.href = '/external-merchant'
}

const showClientEditModal = (client) => {
    editForm.value = {
        id: client.id,
        name: client.name,
        phone: client.phone || ''
    }
    isEditModalVisible.value = true
}

const hideClientEditModal = () => {
    isEditModalVisible.value = false
    editForm.value = { id: null, name: '', phone: '' }
}

const showAddClientModal = () => {
    isAddModalVisible.value = true
}

const hideAddClientModal = () => {
    isAddModalVisible.value = false
    addForm.value = { name: '', phone: '' }
}

const updateClient = async () => {
    if (!editForm.value.name.trim()) {
        toast.error('يرجى إدخال اسم العميل')
        return
    }
    
    editLoading.value = true
    try {
        const response = await axios.put(`/api/clients/${editForm.value.id}`, {
            name: editForm.value.name,
            phone: editForm.value.phone
        })
        
        toast.success('تم تحديث بيانات العميل بنجاح')
        hideClientEditModal()
        loadClients()
    } catch (error) {
        toast.error('حدث خطأ أثناء تحديث البيانات')
        console.error(error)
    } finally {
        editLoading.value = false
    }
}

const addClient = async () => {
    if (!addForm.value.name.trim()) {
        toast.error('يرجى إدخال اسم العميل')
        return
    }
    
    addLoading.value = true
    try {
        const response = await axios.post('/api/clients/store', {
            name: addForm.value.name,
            phone: addForm.value.phone
        })
        
        toast.success('تم إضافة العميل بنجاح')
        hideAddClientModal()
        loadClients()
    } catch (error) {
        toast.error('حدث خطأ أثناء إضافة العميل')
        console.error(error)
    } finally {
        addLoading.value = false
    }
}

const destroy = async (id) => {
    if (!confirm('هل أنت متأكد من حذف هذا العميل؟')) {
        return
    }
    
    try {
        await axios.delete(`/api/clients/${id}`)
        toast.success('تم حذف العميل بنجاح')
        loadClients()
    } catch (error) {
        toast.error('حدث خطأ أثناء حذف العميل')
        console.error(error)
    }
}

const loadClients = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams({
            page: currentPage.value,
            per_page: perPage.value,
            name: searchName.value,
            phone: searchPhone.value,
            status: statusFilter.value
        })
        
        const response = await axios.get(`/api/clients?${params}`)
        clientsData.value = response.data.data
        totalClients.value = response.data.total
        totalPages.value = response.data.last_page
        stats.value = response.data.stats
    } catch (error) {
        toast.error('حدث خطأ أثناء تحميل البيانات')
        console.error(error)
    } finally {
        loading.value = false
    }
}

// تطبيق الفلاتر
const applyFilters = () => {
    currentPage.value = 1 // العودة للصفحة الأولى عند الفلترة
    loadClients()
}

// إعادة تعيين الفلاتر
const resetFilters = () => {
    searchName.value = ''
    searchPhone.value = ''
    statusFilter.value = ''
    currentPage.value = 1
    loadClients()
}

// Pagination methods
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
        loadClients()
    }
}

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
        loadClients()
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
        loadClients()
    }
}

// طباعة تقرير العملاء
const printClientsReport = async () => {
    try {
        // جلب جميع البيانات مع الفلاتر الحالية
        const params = new URLSearchParams({
            per_page: 10000, // عدد كبير لجلب جميع البيانات
            name: searchName.value,
            phone: searchPhone.value,
            status: statusFilter.value
        })
        
        const response = await axios.get(`/api/clients?${params}`)
        const allClients = response.data.data
        
        // حساب الإحصائيات من جميع البيانات
        const totalClients = allClients.length
        const totalRequired = allClients.reduce((sum, client) => sum + (client.total_required || 0), 0)
        const totalPaid = allClients.reduce((sum, client) => sum + (client.total_paid || 0), 0)
        const totalDebt = allClients.reduce((sum, client) => sum + (client.remaining_debt || 0), 0)
        
        // حساب عدد العملاء حسب الحالة
        const clientsWithDebt = allClients.filter(client => client.remaining_debt > 0).length
        const clientsPaid = allClients.filter(client => client.remaining_debt === 0).length
        const clientsWithCredit = allClients.filter(client => client.remaining_debt < 0).length
    
    // إنشاء محتوى الطباعة
    const printContent = `
        <div style="font-family: Arial, sans-serif; padding: 20px;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="margin-bottom: 10px; color: #2563eb;">${props.systemConfig?.company_name || 'Salam Jalal Ayoub Company'}</h1>
                <h2 style="margin-bottom: 15px;">تقرير العملاء</h2>
                <p style="font-size: 14px; color: #666;">${new Date().toLocaleDateString('en-US')}</p>
            </div>
            
            <!-- Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Clients</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #1f2937;">${totalClients}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Required</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #1f2937;">$${Math.round(totalRequired).toLocaleString('en-US')}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Paid</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #059669;">$${Math.round(totalPaid).toLocaleString('en-US')}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Total Debt</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #dc2626;">$${Math.round(totalDebt).toLocaleString('en-US')}</p>
                </div>
            </div>
            
            <!-- Client Status Summary -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Clients with Debt</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #dc2626;">${clientsWithDebt}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">Fully Paid</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #059669;">${clientsPaid}</p>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
                    <h3 style="margin-bottom: 8px; color: #374151; font-size: 14px;">With Credit</h3>
                    <p style="font-size: 16px; font-weight: bold; color: #2563eb;">${clientsWithCredit}</p>
                </div>
            </div>
            
            <!-- Detailed Table -->
            <h3 style="margin-bottom: 15px; text-align: center; color: #374151;">Clients Details</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">ID</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Name</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Phone</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Required</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Paid</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Remaining</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${allClients.map(client => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${client.id}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${client.name}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">${client.phone || 'N/A'}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">$${Math.round(client.total_required || 0).toLocaleString('en-US')}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">$${Math.round(client.total_paid || 0).toLocaleString('en-US')}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center; color: ${client.remaining_debt === 0 ? '#059669' : client.remaining_debt > 0 ? '#dc2626' : '#2563eb'};">$${Math.round(client.remaining_debt || 0).toLocaleString('en-US')}</td>
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">
                                ${client.remaining_debt === 0 ? 'Paid' : 
                                  client.remaining_debt > 0 ? 'Debtor' : 
                                  'With Credit'}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    // فتح نافذة الطباعة
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Clients Report</title>
                <style>
                    @media print {
                        body { margin: 0; }
                        @page { margin: 1cm; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    } catch (error) {
        toast.error('حدث خطأ أثناء تحميل البيانات للطباعة')
        console.error(error)
    }
}

// Profit edit functions
const showProfitEditModal = async (client) => {
    selectedClient.value = client
    showProfitEdit.value = true
    
    try {
        // جلب استثمارات العميل
        const response = await axios.get(`/api/clients/${client.id}/investments`)
        clientInvestments.value = response.data.investments || []
    } catch (error) {
        console.error('Error loading client investments:', error)
        toast.error('حدث خطأ أثناء جلب استثمارات العميل')
        clientInvestments.value = []
    }
}

const hideProfitEditModal = () => {
    showProfitEdit.value = false
    selectedClient.value = null
    clientInvestments.value = []
}

const saveProfitChanges = async () => {
    if (!selectedClient.value) return
    
    profitEditLoading.value = true
    
    try {
        // تحضير البيانات للتحديث
        const updates = []
        clientInvestments.value.forEach(investment => {
            investment.investment_cars.forEach(carInvestment => {
                updates.push({
                    id: carInvestment.id,
                    profit_share: parseFloat(carInvestment.profit_share) || 0
                })
            })
        })
        
        // إرسال التحديثات
        const response = await axios.post(`/api/clients/${selectedClient.value.id}/update-profit-shares`, {
            updates: updates
        })
        
        if (response.data.success) {
            toast.success('تم تحديث نسب الربح بنجاح')
            hideProfitEditModal()
            // إعادة تحميل بيانات العملاء
            loadClients()
        } else {
            toast.error(response.data.message || 'حدث خطأ أثناء تحديث نسب الربح')
        }
    } catch (error) {
        console.error('Error updating profit shares:', error)
        toast.error('حدث خطأ أثناء تحديث نسب الربح')
    } finally {
        profitEditLoading.value = false
    }
}

// Lifecycle
onMounted(() => {
    // Initialize data if not provided via props
    if (!props.clients) {
        loadClients()
    }
})
</script>
