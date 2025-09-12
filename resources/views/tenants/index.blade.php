@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-building text-blue-600 mr-3"></i>
                    إدارة المستأجرين
                </h1>
                <p class="text-gray-600 text-lg">إدارة وتنظيم جميع المستأجرين في النظام</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('tenants.create') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    إضافة مستأجر جديد
                </a>
                <button onclick="clearAllCache()" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                    <i class="fas fa-broom mr-2"></i>
                    مسح الكاش
                </button>
                <button onclick="showDatabaseInfo()" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                    <i class="fas fa-database mr-2"></i>
                    معلومات قاعدة البيانات
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">المستأجرين النشطين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->where('status', 'active')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-pause-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">المستأجرين المعلقين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->where('status', 'suspended')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المستأجرين</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenants->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filter -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="البحث في المستأجرين..." class="block w-full pr-10 pl-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="flex gap-3">
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                    <option value="suspended">معلق</option>
                </select>
                <button onclick="resetFilters()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-undo mr-1"></i>
                    إعادة تعيين
                </button>
            </div>
        </div>
    </div>

    <!-- Tenants List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        @if($tenants->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستأجر</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدومينات</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">خطة الاشتراك</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإنشاء</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tenants as $tenant)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-white font-semibold text-lg">{{ substr($tenant->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="mr-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $tenant->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $tenant->email ?: 'لا يوجد بريد إلكتروني' }}</div>
                                        @if($tenant->phone)
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-phone mr-1"></i>{{ $tenant->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tenant->domains as $domain)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors cursor-pointer" onclick="copyToClipboard('{{ $domain->domain }}')">
                                            <i class="fas fa-globe mr-1"></i>
                                            {{ $domain->domain }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($tenant->subscription_plan === 'enterprise') bg-purple-100 text-purple-800
                                    @elseif($tenant->subscription_plan === 'premium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    <i class="fas fa-crown mr-1"></i>
                                    {{ $tenant->subscription_plan === 'enterprise' ? 'مؤسسي' : ($tenant->subscription_plan === 'premium' ? 'مميز' : 'أساسي') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white
                                    @if($tenant->status === 'active') status-active
                                    @elseif($tenant->status === 'inactive') status-inactive
                                    @else status-suspended
                                    @endif">
                                    <i class="fas fa-circle mr-1 text-xs"></i>
                                    {{ $tenant->status === 'active' ? 'نشط' : ($tenant->status === 'inactive' ? 'غير نشط' : 'معلق') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $tenant->created_at->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <a href="{{ route('tenants.show', $tenant->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tenants.edit', $tenant->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="checkTenantDatabase('{{ $tenant->id }}')" class="text-green-600 hover:text-green-900 transition-colors" title="فحص قاعدة البيانات">
                                        <i class="fas fa-database"></i>
                                    </button>
                                    
                                    @if($tenant->status === 'active')
                                        <form method="POST" action="{{ route('tenants.suspend', $tenant->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900 transition-colors" title="تعليق" onclick="return confirm('هل أنت متأكد من تعليق هذا المستأجر؟')">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('tenants.activate', $tenant->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 transition-colors" title="تفعيل" onclick="return confirm('هل أنت متأكد من تفعيل هذا المستأجر؟')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('tenants.destroy', $tenant->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا المستأجر؟ هذا الإجراء لا يمكن التراجع عنه!')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">لا يوجد مستأجرين</h3>
                <p class="text-gray-500 mb-6">ابدأ بإنشاء أول مستأجر في النظام</p>
                <a href="{{ route('tenants.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    إضافة مستأجر جديد
                </a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($tenants->hasPages())
        <div class="mt-6">
            {{ $tenants->links() }}
        </div>
    @endif
</div>

<!-- JavaScript -->
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const selectedStatus = this.value;
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (!selectedStatus) {
                row.style.display = '';
                return;
            }
            
            const statusElement = row.querySelector('.status-active, .status-inactive, .status-suspended');
            if (statusElement) {
                const status = statusElement.textContent.toLowerCase();
                const statusMap = {
                    'active': 'نشط',
                    'inactive': 'غير نشط', 
                    'suspended': 'معلق'
                };
                
                if (status.includes(statusMap[selectedStatus])) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
    
    // Reset filters
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
    
    // Copy to clipboard
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            toast.innerHTML = '<i class="fas fa-check mr-2"></i>تم نسخ الدومين: ' + text;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        });
    }
    
    // Clear all cache
    function clearAllCache() {
        if (confirm('هل أنت متأكد من مسح جميع الكاش؟')) {
            fetch('{{ route("tenants.clear-all-cache") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    toast.innerHTML = '<i class="fas fa-check mr-2"></i>' + data.message;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                } else {
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    toast.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>' + data.message;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                toast.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>حدث خطأ أثناء مسح الكاش';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            });
        }
    }
    
    // Show database info for all tenants
    function showDatabaseInfo() {
        fetch('{{ route("tenants.database-info") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showDatabaseModal(data.data);
                } else {
                    showErrorToast(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('حدث خطأ أثناء الحصول على معلومات قاعدة البيانات');
            });
    }
    
    // Check specific tenant database
    function checkTenantDatabase(tenantId) {
        fetch(`{{ url('admin/tenants') }}/${tenantId}/check-database`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTenantDatabaseModal(data);
                } else {
                    showErrorToast(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('حدث خطأ أثناء فحص قاعدة البيانات');
            });
    }
    
    // Show database info modal
    function showDatabaseModal(data) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-96 overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات قاعدة البيانات</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">قاعدة البيانات المركزية</h4>
                        <div class="text-sm text-blue-800">
                            <p><strong>اسم قاعدة البيانات:</strong> ${data.central.name}</p>
                            <p><strong>الخادم:</strong> ${data.central.host}:${data.central.port}</p>
                            <p><strong>نوع قاعدة البيانات:</strong> ${data.central.driver}</p>
                            <p><strong>حالة الاتصال:</strong> <span class="text-green-600">✓ نشط</span></p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <h4 class="font-semibold text-gray-900">قواعد بيانات المستأجرين</h4>
                        ${data.tenants.map(tenant => `
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">${tenant.name}</p>
                                        <p class="text-sm text-gray-600">الدومينات: ${tenant.domains.join(', ')}</p>
                                        <p class="text-sm text-gray-600">قاعدة البيانات: ${tenant.database.name}</p>
                                        <p class="text-sm text-gray-600">الخادم: ${tenant.database.host}:${tenant.database.port}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${tenant.database.connection_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                            ${tenant.database.connection_active ? '✓ نشط' : '✗ غير نشط'}
                                        </span>
                                        ${tenant.database.error ? `<p class="text-xs text-red-600 mt-1">${tenant.database.error}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Show tenant database modal
    function showTenantDatabaseModal(data) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">فحص قاعدة البيانات</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-900 mb-2">${data.tenant.name}</h4>
                        <div class="text-sm text-green-800">
                            <p><strong>معرف المستأجر:</strong> ${data.tenant.id}</p>
                            <p><strong>الدومينات:</strong> ${data.tenant.domains.join(', ')}</p>
                            <p><strong>اسم قاعدة البيانات:</strong> ${data.database.name}</p>
                            <p><strong>الخادم:</strong> ${data.database.host}:${data.database.port}</p>
                            <p><strong>عدد الجداول:</strong> ${data.database.tables_count}</p>
                            <p><strong>حالة الاتصال:</strong> <span class="text-green-600">✓ نشط</span></p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Show error toast
    function showErrorToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>' + message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
</script>
@endsection
