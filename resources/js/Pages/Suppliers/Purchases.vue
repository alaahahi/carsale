<template>
    <AuthenticatedLayout>
        <Head :title="$t('suppliers_purchases')" />

        <div class="py-4">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('suppliers_purchases') }}</h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $t('suppliers_purchases_desc') }}
                                </p>
                            </div>
                            <button
                                @click="goBack"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg"
                            >
                                {{ $t('back') }}
                            </button>
                        </div>

                        <div class="flex justify-end mb-6">
                            <button
                                @click="loadSummary"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                            >
                                {{ $t('refresh') }}
                            </button>
                        </div>

                        <!-- Summary totals -->
                        <div v-if="summaryTotals" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $t('cars_count_label') }}</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ formatNumber(summaryTotals.cars_count) }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $t('total_purchase') }}</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summaryTotals.total_purchase) }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $t('total_supplier_payments') }}</div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summaryTotals.total_paid) }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $t('current_debt') }}</div>
                                <div class="text-xl font-bold" :class="summaryTotals.debt > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                    ${{ formatNumber(summaryTotals.debt) }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Suppliers list -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                    <h2 class="font-semibold text-gray-900 dark:text-white">{{ $t('suppliers_list') }}</h2>
                                </div>
                                <div v-if="loadingSummary" class="p-6 text-center text-gray-500 dark:text-gray-400">{{ $t('loading') }}</div>
                                <div v-else class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('supplier') }}</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('count_col') }}</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('purchase_col') }}</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('paid_col') }}</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('debt') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                            <tr
                                                v-for="row in summaryRows"
                                                :key="row.supplier_id"
                                                class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                                                :class="selectedSupplierId === row.supplier_id ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
                                                @click="selectSupplier(row.supplier_id)"
                                            >
                                                <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">
                                                    {{ row.supplier_name || ('#' + row.supplier_id) }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ formatNumber(row.cars_count) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-gray-600 dark:text-gray-300">${{ formatNumber(row.total_purchase) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-gray-600 dark:text-gray-300">${{ formatNumber(row.total_paid) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap font-semibold"
                                                    :class="Number(row.debt) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                                    ${{ formatNumber(row.debt) }}
                                                </td>
                                            </tr>
                                            <tr v-if="summaryRows.length === 0">
                                                <td class="px-3 py-6 text-center text-gray-500 dark:text-gray-400" colspan="5">{{ $t('no_data') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Supplier details -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                                    <h2 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $t('supplier_details') }}
                                        <span v-if="supplierDetails?.supplier" class="text-gray-500 dark:text-gray-400 font-normal">
                                            — {{ supplierDetails.supplier.name }}
                                        </span>
                                    </h2>
                                    <div class="flex gap-2 items-center">
                                        <div v-if="supplierDetails?.supplier" class="flex gap-2">
                                            <a
                                                :href="`/dashboard?add_car=1&supplier_id=${supplierDetails.supplier.id}&tab=single`"
                                                class="px-3 py-1 rounded-lg text-sm bg-green-600 hover:bg-green-700 text-white"
                                            >
                                                {{ $t('add_car_btn') }}
                                            </a>
                                            <a
                                                :href="`/dashboard?add_car=1&supplier_id=${supplierDetails.supplier.id}&tab=group`"
                                                class="px-3 py-1 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white"
                                            >
                                                {{ $t('add_group_btn') }}
                                            </a>
                                            <button
                                                type="button"
                                                class="px-3 py-1 rounded-lg text-sm bg-yellow-600 hover:bg-yellow-700 text-white"
                                                @click="openAddPayment"
                                            >
                                                {{ $t('add_payment') }}
                                            </button>
                                        </div>
                                        <button
                                            class="px-3 py-1 rounded-lg text-sm"
                                            :class="activeTab === 'cars' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                                            @click="activeTab = 'cars'"
                                        >
                                            {{ $t('cars_tab') }}
                                        </button>
                                        <button
                                            class="px-3 py-1 rounded-lg text-sm"
                                            :class="activeTab === 'payments' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                                            @click="activeTab = 'payments'; loadPayments()"
                                        >
                                            {{ $t('payments_tab') }}
                                        </button>
                                        <button
                                            class="px-3 py-1 rounded-lg text-sm"
                                            :class="activeTab === 'aggregate' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                                            @click="activeTab = 'aggregate'"
                                        >
                                            {{ $t('aggregate_tab') }}
                                        </button>
                                        <button
                                            class="px-3 py-1 rounded-lg text-sm"
                                            :class="activeTab === 'profits' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                                            @click="activeTab = 'profits'"
                                        >
                                            {{ $t('profits_tab') }}
                                        </button>
                                    </div>
                                </div>

                                <div v-if="loadingDetails" class="p-6 text-center text-gray-500 dark:text-gray-400">{{ $t('loading') }}</div>
                                <div v-else-if="!supplierDetails" class="p-6 text-center text-gray-500 dark:text-gray-400">{{ $t('choose_supplier') }}</div>
                                <div v-else class="p-4">
                                    <!-- Totals -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('total_purchase') }}</div>
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">${{ formatNumber(supplierDetails.totals?.total_purchase) }}</div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('total_supplier_payments') }}</div>
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">${{ formatNumber(supplierDetails.totals?.total_paid) }}</div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('current_debt') }}</div>
                                            <div class="text-lg font-bold"
                                                 :class="Number(supplierDetails.totals?.debt || 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                                ${{ formatNumber(supplierDetails.totals?.debt) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cars tab -->
                                    <div v-show="activeTab === 'cars'" class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">PIN</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('car') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('model_col') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('color') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('purchase_price') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('paid_col') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('remaining_col') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                <tr v-for="c in (supplierDetails.cars?.data || [])" :key="c.id">
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ c.pin }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">{{ c.name }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ c.model }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ c.color }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(c.purchase_price) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(c.paid_amount) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap font-semibold"
                                                        :class="(Number(c.purchase_price || 0) - Number(c.paid_amount || 0)) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                                        ${{ formatNumber((Number(c.purchase_price || 0) - Number(c.paid_amount || 0))) }}
                                                    </td>
                                                </tr>
                                                <tr v-if="(supplierDetails.cars?.data || []).length === 0">
                                                    <td class="px-3 py-6 text-center text-gray-500 dark:text-gray-400" colspan="7">{{ $t('no_cars') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Payments tab -->
                                    <div v-show="activeTab === 'payments'" class="overflow-x-auto">
                                        <div class="mb-3 flex items-center justify-between">
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                <span v-if="supplierPaymentsSummary">
                                                    {{ $t('payments_total') }}: ${{ formatNumber(supplierPaymentsSummary.total || 0) }}
                                                    — {{ $t('count_col') }}: {{ supplierPaymentsSummary.count || 0 }}
                                                </span>
                                            </div>
                                        </div>

                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('date') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('amount') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('car') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('user') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('note') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                <tr v-if="loadingPayments">
                                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ $t('loading') }}</td>
                                                </tr>
                                                <tr v-for="p in supplierPayments" :key="p.id">
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ p.paid_at || (p.created_at || '').slice(0,10) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">${{ formatNumber(p.amount) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                                        <span v-if="p.car">#{{ p.car.no }} {{ p.car.pin ? '(' + p.car.pin + ')' : '' }}</span>
                                                        <span v-else class="text-gray-500 dark:text-gray-400">{{ $t('on_account') }}</span>
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ p.creator?.name || '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="truncate">{{ p.note || '' }}</span>
                                                            <button type="button"
                                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline whitespace-nowrap"
                                                                    @click="deleteSupplierPayment(p)">
                                                                {{ $t('delete') }}
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr v-if="!loadingPayments && supplierPayments.length === 0">
                                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ $t('no_payments') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Aggregate tab -->
                                    <div v-show="activeTab === 'aggregate'" class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('model_col') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('color') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('count_col') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('purchase_col') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('paid_col') }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('debt') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                <tr v-for="r in (supplierDetails.aggregate || [])" :key="`${r.model}-${r.color}`">
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">{{ r.model || '—' }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ r.color || '—' }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ formatNumber(r.cars_count) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(r.total_purchase) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(r.total_paid) }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap font-semibold"
                                                        :class="Number(r.debt) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                                        ${{ formatNumber(r.debt) }}
                                                    </td>
                                                </tr>
                                                <tr v-if="(supplierDetails.aggregate || []).length === 0">
                                                    <td class="px-3 py-6 text-center text-gray-500 dark:text-gray-400" colspan="6">{{ $t('no_data') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Profits tab -->
                                    <div v-show="activeTab === 'profits'">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('sold_profit_count') }}</div>
                                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ formatNumber(supplierDetails.profits?.totals?.sold_count || 0) }}</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('total_sales') }}</div>
                                                <div class="text-lg font-bold text-gray-900 dark:text-white">${{ formatNumber(supplierDetails.profits?.totals?.total_sales || 0) }}</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('total_cost_label') }}</div>
                                                <div class="text-lg font-bold text-gray-900 dark:text-white">${{ formatNumber(supplierDetails.profits?.totals?.total_cost || 0) }}</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t('net_profit') }}</div>
                                                <div class="text-lg font-bold"
                                                     :class="Number(supplierDetails.profits?.totals?.total_profit || 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                                    ${{ formatNumber(supplierDetails.profits?.totals?.total_profit || 0) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 font-semibold text-gray-900 dark:text-white">
                                                    {{ $t('top_profit') }}
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                                            <tr>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('car') }}</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('sale_price_col') }}</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('cost_col') }}</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('profit') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                            <tr v-for="c in (supplierDetails.profits?.top_profit_cars || [])" :key="c.id">
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">
                                                                    #{{ c.no }} <span class="text-gray-500 dark:text-gray-400">{{ c.pin ? '(' + c.pin + ')' : '' }}</span>
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(c.sale_price) }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(c.total_cost) }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap font-semibold text-green-600 dark:text-green-400">
                                                                    ${{ formatNumber(c.profit) }}
                                                                </td>
                                                            </tr>
                                                            <tr v-if="(supplierDetails.profits?.top_profit_cars || []).length === 0">
                                                                <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ $t('no_data') }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 font-semibold text-gray-900 dark:text-white">
                                                    {{ $t('top_loss') }}
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                                            <tr>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('car') }}</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('sale_price_col') }}</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('cost_col') }}</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('profit') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                            <tr v-for="c in (supplierDetails.profits?.top_loss_cars || [])" :key="c.id">
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">
                                                                    #{{ c.no }} <span class="text-gray-500 dark:text-gray-400">{{ c.pin ? '(' + c.pin + ')' : '' }}</span>
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(c.sale_price) }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(c.total_cost) }}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap font-semibold text-red-600 dark:text-red-400">
                                                                    ${{ formatNumber(c.profit) }}
                                                                </td>
                                                            </tr>
                                                            <tr v-if="(supplierDetails.profits?.top_loss_cars || []).length === 0">
                                                                <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">{{ $t('no_data') }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Payment Modal -->
                        <div v-if="showAddPayment" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-900 dark:border-gray-800">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $t('add_supplier_payment') }}</h3>
                                    <button @click="showAddPayment = false" class="text-gray-400 hover:text-gray-600">✕</button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('amount') }}</label>
                                        <input v-model="paymentForm.amount" type="number" step="0.01" min="0.01"
                                               :max="maxPayableAmount || null"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm" />
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $t('max_amount') }}: ${{ formatNumber(maxPayableAmount || 0) }}
                                            <span v-if="paymentForm.car_id"> ({{ $t('by_selected_car') }})</span>
                                            <span v-else> ({{ $t('by_supplier_debt') }})</span>
                                        </div>
                                        <div v-if="amountExceedsMax" class="mt-1 text-xs text-red-600 dark:text-red-400 font-semibold">
                                            {{ $t('amount_exceeds_warning') }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('date') }}</label>
                                        <input v-model="paymentForm.paid_at" type="date"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('assign_car_optional') }}</label>
                                        <select v-model="paymentForm.car_id"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm">
                                            <option :value="null">{{ $t('auto_distribute') }}</option>
                                            <option v-for="c in (supplierDetails?.cars?.data || [])" :key="c.id" :value="c.id">
                                                #{{ c.no }} {{ c.pin ? '(' + c.pin + ')' : '' }} — {{ $t('remaining_label') }}: ${{ formatNumber((Number(c.purchase_price||0)-Number(c.paid_amount||0))) }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                            <input type="checkbox" v-model="paymentForm.on_account"
                                                   class="rounded border-gray-300 dark:border-gray-700" />
                                            <span>{{ $t('on_account_payment') }}</span>
                                        </label>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $t('on_account_note') }}
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $t('note') }}</label>
                                        <input v-model="paymentForm.note" type="text"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg text-sm" />
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2 mt-4">
                                    <button @click="showAddPayment = false"
                                            class="px-4 py-2 bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg text-sm">
                                        {{ $t('cancel') }}
                                    </button>
                                    <button @click="submitPayment"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
                                        {{ $t('save') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Global aggregate -->
                        <div class="mt-8 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                <h2 class="font-semibold text-gray-900 dark:text-white">{{ $t('global_aggregate_title') }}</h2>
                            </div>
                            <div v-if="loadingAggregate" class="p-6 text-center text-gray-500 dark:text-gray-400">{{ $t('loading') }}</div>
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('supplier') }}</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('model_col') }}</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('color') }}</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('count_col') }}</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('payments_tab') }}</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ $t('debt') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                        <tr v-for="r in globalAggregate" :key="`${r.supplier_id}-${r.model}-${r.color}`">
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-white">{{ r.supplier_name || ('#' + r.supplier_id) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ r.model || '—' }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ r.color || '—' }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">{{ formatNumber(r.cars_count) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">${{ formatNumber(r.total_paid) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap font-semibold"
                                                :class="Number(r.debt) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                                ${{ formatNumber(r.debt) }}
                                            </td>
                                        </tr>
                                        <tr v-if="globalAggregate.length === 0">
                                            <td class="px-3 py-6 text-center text-gray-500 dark:text-gray-400" colspan="6">{{ $t('no_data') }}</td>
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
import { Head } from '@inertiajs/inertia-vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'

const toast = useToast()
const { t } = useI18n()

const loadingSummary = ref(false)
const loadingDetails = ref(false)
const loadingAggregate = ref(false)
const loadingPayments = ref(false)

const summaryRows = ref([])
const summaryTotals = ref(null)

const selectedSupplierId = ref(null)
const supplierDetails = ref(null)
const activeTab = ref('cars')

const globalAggregate = ref([])
const supplierPayments = ref([])
const supplierPaymentsSummary = ref(null)

const showAddPayment = ref(false)
const paymentForm = ref({
    amount: null,
    paid_at: new Date().toISOString().slice(0, 10),
    note: '',
    car_id: null, // optional
    on_account: false, // allow recording credit (no car allocation)
})

const selectedCarForPayment = computed(() => {
    const carId = paymentForm.value?.car_id
    if (!carId) return null
    return (supplierDetails.value?.cars?.data || []).find((c) => Number(c.id) === Number(carId)) || null
})

const hasAnyDue = computed(() => {
    const d = Number(supplierDetails.value?.totals?.debt || 0)
    return d > 0
})

const maxPayableAmount = computed(() => {
    // إذا تم اختيار سيارة: الحد الأقصى = المتبقي لهذه السيارة
    if (selectedCarForPayment.value) {
        const c = selectedCarForPayment.value
        const due = Number(c.purchase_price || 0) - Number(c.paid_amount || 0)
        return Math.max(0, Number(due.toFixed(2)))
    }
    // توزيع تلقائي: الحد الأقصى = دين المورد الإجمالي (حسب الفلترة الحالية)
    const debt = Number(supplierDetails.value?.totals?.debt || 0)
    return Math.max(0, Number(debt.toFixed(2)))
})

const amountExceedsMax = computed(() => {
    const a = Number(paymentForm.value?.amount || 0)
    return a > 0 && maxPayableAmount.value >= 0 && a > maxPayableAmount.value
})

watch([() => paymentForm.value.amount, () => paymentForm.value.car_id, () => supplierDetails.value?.totals?.debt], () => {
    // لا نُجبر المستخدم على القص؛ فقط تنبيه.
})

const formatNumber = (n) => {
    const num = Number(n || 0)
    return Math.round(num).toLocaleString('en-US')
}

const goBack = () => window.history.back()

const loadSummary = async () => {
    loadingSummary.value = true
    try {
        const res = await axios.get(`/api/suppliers/purchases/summary`)
        summaryRows.value = res.data.data || []
        summaryTotals.value = res.data.totals || null
    } finally {
        loadingSummary.value = false
    }
}

const loadGlobalAggregate = async () => {
    loadingAggregate.value = true
    try {
        const res = await axios.get(`/api/cars/purchases/aggregate`)
        globalAggregate.value = res.data.data || []
    } finally {
        loadingAggregate.value = false
    }
}

const selectSupplier = async (supplierId) => {
    selectedSupplierId.value = supplierId
    loadingDetails.value = true
    supplierDetails.value = null
    try {
        const res = await axios.get(`/api/suppliers/${supplierId}/purchases`)
        supplierDetails.value = res.data
        activeTab.value = 'cars'
        await loadPayments()
    } finally {
        loadingDetails.value = false
    }
}

const loadPayments = async () => {
    if (!selectedSupplierId.value) return
    loadingPayments.value = true
    try {
        const res = await axios.get(`/suppliers/${selectedSupplierId.value}/payments`)
        supplierPayments.value = res.data.payments?.data || []
        supplierPaymentsSummary.value = res.data.summary || null
    } finally {
        loadingPayments.value = false
    }
}

const openAddPayment = () => {
    paymentForm.value = {
        amount: null,
        paid_at: new Date().toISOString().slice(0, 10),
        note: '',
        car_id: null,
        on_account: false,
    }
    showAddPayment.value = true
}

const submitPayment = async () => {
    if (!selectedSupplierId.value) return
    const amount = Number(paymentForm.value?.amount || 0)
    if (amount <= 0) {
        toast.error(t('enter_valid_amount'))
        return
    }
    const allowOnAccount = !!paymentForm.value?.on_account
    if (!allowOnAccount) {
        if (maxPayableAmount.value <= 0) {
            toast.error(t('no_unpaid_cars_enable_on_account'))
            return
        }
        if (amount > maxPayableAmount.value) {
            toast.error(`${t('amount_exceeds_enable_on_account')} $${formatNumber(maxPayableAmount.value)} (${t('or_enable_on_account')})`)
            return
        }
    }
    try {
        await axios.post(`/suppliers/${selectedSupplierId.value}/payments`, paymentForm.value)
        toast.success(t('payment_added_success'))
        showAddPayment.value = false
        await loadSummary()
        await selectSupplier(selectedSupplierId.value)
    } catch (e) {
        toast.error(e.response?.data?.message || t('payment_add_failed'))
    }
}

const deleteSupplierPayment = async (p) => {
    if (!selectedSupplierId.value || !p?.id) return
    const ok = confirm(`${t('confirm_delete_payment_supplier')}\n${t('amount')}: $${formatNumber(p.amount)}\n${t('date')}: ${(p.paid_at || (p.created_at || '')).slice(0, 10)}`)
    if (!ok) return
    try {
        await axios.delete(`/suppliers/${selectedSupplierId.value}/payments/${p.id}`)
        toast.success(t('payment_deleted_success'))
        await loadSummary()
        await selectSupplier(selectedSupplierId.value)
    } catch (e) {
        toast.error(e.response?.data?.message || t('payment_delete_failed'))
    }
}

onMounted(async () => {
    await loadSummary()
    await loadGlobalAggregate()
})
</script>

