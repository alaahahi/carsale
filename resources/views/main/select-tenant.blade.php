@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">اختيار المستأجر</h1>
            <p class="text-lg text-gray-600">اختر المستأجر الذي تريد الوصول إليه</p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="البحث عن المستأجر..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex gap-2">
                    <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">جميع الحالات</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="suspended">معلق</option>
                    </select>
                    <select id="planFilter" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">جميع الخطط</option>
                        <option value="basic">أساسي</option>
                        <option value="premium">مميز</option>
                        <option value="enterprise">مؤسسي</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tenants Grid -->
        <div id="tenantsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tenants as $tenant)
            <div class="tenant-card bg-white rounded-lg shadow hover:shadow-lg transition-shadow" 
                 data-name="{{ strtolower($tenant->name) }}" 
                 data-status="{{ $tenant->status }}" 
                 data-plan="{{ $tenant->subscription_plan }}">
                
                <!-- Tenant Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-lg font-medium text-indigo-600">{{ substr($tenant->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $tenant->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $tenant->email ?? 'غير محدد' }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($tenant->status === 'active') bg-green-100 text-green-800
                            @elseif($tenant->status === 'inactive') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $tenant->status === 'active' ? 'نشط' : ($tenant->status === 'inactive' ? 'غير نشط' : 'معلق') }}
                        </span>
                    </div>
                </div>

                <!-- Tenant Details -->
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">خطة الاشتراك:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $tenant->subscription_plan }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">تاريخ الإنشاء:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $tenant->created_at->format('Y-m-d') }}</span>
                        </div>

                        @if($tenant->subscription_expires_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">انتهاء الاشتراك:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $tenant->subscription_expires_at->format('Y-m-d') }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Domains -->
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">الدومينات:</p>
                        <div class="space-y-1">
                            @foreach($tenant->domains as $domain)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-600">{{ $domain->domain }}</span>
                                    @php
                                        $subdomain = \App\Helpers\SubdomainHelper::extractSubdomain($domain->domain);
                                    @endphp
                                    @if($subdomain)
                                        <a href="{{ \App\Helpers\SubdomainHelper::generateSubdomainUrl($subdomain) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                            الوصول
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex space-x-2">
                        @if($tenant->domains->count() > 0)
                            @php
                                $firstDomain = $tenant->domains->first();
                                $subdomain = \App\Helpers\SubdomainHelper::extractSubdomain($firstDomain->domain);
                            @endphp
                            @if($subdomain)
                                <a href="{{ \App\Helpers\SubdomainHelper::generateSubdomainUrl($subdomain) }}" 
                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm font-medium">
                                    الوصول للمستأجر
                                </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('tenants.show', $tenant->id) }}" 
                           class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center py-2 px-3 rounded text-sm font-medium">
                            التفاصيل
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- No Results -->
        <div id="noResults" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد نتائج</h3>
            <p class="mt-1 text-sm text-gray-500">جرب البحث بكلمات مختلفة أو تغيير الفلاتر.</p>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('main.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                العودة للصفحة الرئيسية
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const planFilter = document.getElementById('planFilter');
    const tenantsGrid = document.getElementById('tenantsGrid');
    const noResults = document.getElementById('noResults');
    const tenantCards = document.querySelectorAll('.tenant-card');

    function filterTenants() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const planValue = planFilter.value;
        
        let visibleCount = 0;

        tenantCards.forEach(card => {
            const name = card.dataset.name;
            const status = card.dataset.status;
            const plan = card.dataset.plan;

            const matchesSearch = name.includes(searchTerm);
            const matchesStatus = !statusValue || status === statusValue;
            const matchesPlan = !planValue || plan === planValue;

            if (matchesSearch && matchesStatus && matchesPlan) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            tenantsGrid.style.display = 'none';
            noResults.classList.remove('hidden');
        } else {
            tenantsGrid.style.display = 'grid';
            noResults.classList.add('hidden');
        }
    }

    searchInput.addEventListener('input', filterTenants);
    statusFilter.addEventListener('change', filterTenants);
    planFilter.addEventListener('change', filterTenants);
});
</script>
@endsection
