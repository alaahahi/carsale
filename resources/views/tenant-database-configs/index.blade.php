@extends('layouts.app')

@section('title', 'إدارة إعدادات قاعدة البيانات')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">إدارة إعدادات قاعدة البيانات</h1>
                        <p class="text-blue-100">إدارة الاتصالات الديناميكية بقواعد البيانات بناءً على الـ Subdomain</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('tenant-database-configs.create') }}" 
                           class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                            <i class="fas fa-plus mr-2"></i>إضافة إعداد جديد
                        </a>
                        <button onclick="testAllConnections()" 
                                class="bg-green-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors">
                            <i class="fas fa-plug mr-2"></i>اختبار جميع الاتصالات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-6">
        <div class="col-md-3">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-database text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $configs->total() }}</h3>
                        <p class="text-gray-600">إجمالي الإعدادات</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $configs->where('is_active', true)->count() }}</h3>
                        <p class="text-gray-600">الإعدادات النشطة</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-pause-circle text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $configs->where('is_active', false)->count() }}</h3>
                        <p class="text-gray-600">الإعدادات المعطلة</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-server text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $configs->groupBy('driver')->count() }}</h3>
                        <p class="text-gray-600">أنواع قواعد البيانات</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">قائمة إعدادات قاعدة البيانات</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الـ Subdomain</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستأجر</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع قاعدة البيانات</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الخادم</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قاعدة البيانات</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($configs as $config)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-globe text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $config->subdomain }}</div>
                                            <div class="text-sm text-gray-500">{{ $config->description ?? 'لا يوجد وصف' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($config->tenant)
                                        <span class="text-sm text-gray-900">{{ $config->tenant->name }}</span>
                                    @else
                                        <span class="text-sm text-gray-500">غير مرتبط</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $config->driver === 'mysql' ? 'bg-green-100 text-green-800' : 
                                           ($config->driver === 'pgsql' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ strtoupper($config->driver) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $config->host }}:{{ $config->port }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $config->database_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $config->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $config->is_active ? 'نشط' : 'معطل' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="testConnection({{ $config->id }})" 
                                                class="text-green-600 hover:text-green-900" title="اختبار الاتصال">
                                            <i class="fas fa-plug"></i>
                                        </button>
                                        <a href="{{ route('tenant-database-configs.show', $config) }}" 
                                           class="text-blue-600 hover:text-blue-900" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tenant-database-configs.edit', $config) }}" 
                                           class="text-yellow-600 hover:text-yellow-900" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteConfig({{ $config->id }})" 
                                                class="text-red-600 hover:text-red-900" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-database text-4xl mb-4"></i>
                                    <p class="text-lg">لا توجد إعدادات قاعدة بيانات</p>
                                    <p class="text-sm">ابدأ بإضافة إعداد جديد</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($configs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $configs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Test Connection Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">جاري اختبار الاتصال...</h3>
                <p class="text-sm text-gray-500 text-center">يرجى الانتظار</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function testConnection(configId) {
    const modal = document.getElementById('testModal');
    modal.classList.remove('hidden');
    
    fetch(`{{ url('admin/tenant-database-configs') }}/${configId}/test-connection`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        modal.classList.add('hidden');
        
        if (data.success) {
            showToast('تم الاتصال بقاعدة البيانات بنجاح', 'success');
        } else {
            showToast(data.message || 'فشل الاتصال بقاعدة البيانات', 'error');
        }
    })
    .catch(error => {
        modal.classList.add('hidden');
        showToast('حدث خطأ أثناء اختبار الاتصال', 'error');
    });
}

function testAllConnections() {
    const modal = document.getElementById('testModal');
    modal.classList.remove('hidden');
    
    // اختبار جميع الاتصالات
    const configs = @json($configs->pluck('id'));
    let completed = 0;
    let successful = 0;
    
    configs.forEach(configId => {
        fetch(`{{ url('admin/tenant-database-configs') }}/${configId}/test-connection`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            completed++;
            if (data.success) successful++;
            
            if (completed === configs.length) {
                modal.classList.add('hidden');
                showToast(`تم اختبار ${successful} من ${configs.length} اتصال بنجاح`, 'info');
            }
        })
        .catch(error => {
            completed++;
            if (completed === configs.length) {
                modal.classList.add('hidden');
                showToast('حدث خطأ أثناء اختبار الاتصالات', 'error');
            }
        });
    });
}

function deleteConfig(configId) {
    if (confirm('هل أنت متأكد من حذف هذا الإعداد؟')) {
        fetch(`/admin/tenant-database-configs/${configId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => {
            if (response.ok) {
                showToast('تم حذف الإعداد بنجاح', 'success');
                location.reload();
            } else {
                showToast('حدث خطأ أثناء حذف الإعداد', 'error');
            }
        })
        .catch(error => {
            showToast('حدث خطأ أثناء حذف الإعداد', 'error');
        });
    }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'info' ? 'bg-blue-500 text-white' :
        'bg-gray-500 text-white'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection
