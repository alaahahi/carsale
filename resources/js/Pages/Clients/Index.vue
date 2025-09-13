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
                            
                            <!-- Action Button -->
                            <div class="mt-4">
                                <button @click="showAddClientModal" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>إضافة عميل جديد</span>
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
    stats: Object
})

// Reactive data
const loading = ref(false)
const clientsData = ref(props.clients || [])
const isEditModalVisible = ref(false)
const isAddModalVisible = ref(false)
const editLoading = ref(false)
const addLoading = ref(false)

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
    return Math.round(number).toLocaleString()
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
        const response = await axios.get(`/api/clients?page=${currentPage.value}&per_page=${perPage.value}`)
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

// Lifecycle
onMounted(() => {
    // Initialize data if not provided via props
    if (!props.clients) {
        loadClients()
    }
})
</script>
