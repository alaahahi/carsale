@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">تفاصيل المستأجر</h1>
        <div class="flex space-x-2">
            <a href="{{ route('tenants.edit', $tenant->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                تعديل
            </a>
            <a href="{{ route('tenants.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                العودة للقائمة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tenant Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">معلومات المستأجر</h2>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">الاسم</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">البريد الإلكتروني</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->email ?? 'غير محدد' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">الهاتف</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->phone ?? 'غير محدد' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">الحالة</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($tenant->status === 'active') bg-green-100 text-green-800
                            @elseif($tenant->status === 'inactive') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $tenant->status === 'active' ? 'نشط' : ($tenant->status === 'inactive' ? 'غير نشط' : 'معلق') }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">خطة الاشتراك</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->subscription_plan }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">انتهاء الاشتراك</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->subscription_expires_at ? $tenant->subscription_expires_at->format('Y-m-d') : 'غير محدد' }}</dd>
                </div>
            </dl>
            
            @if($tenant->address)
            <div class="mt-6">
                <dt class="text-sm font-medium text-gray-500">العنوان</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $tenant->address }}</dd>
            </div>
            @endif
        </div>

        <!-- Domain Management -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">إدارة الدومينات</h2>
                <button onclick="showAddDomainModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    إضافة دومين
                </button>
            </div>

            <div class="space-y-3">
                @foreach($tenant->domains as $domain)
                <div class="flex items-center justify-between p-3 border rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $domain->domain }}</p>
                            <p class="text-xs text-gray-500">تم الإنشاء: {{ $domain->created_at->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-1">
                        <button onclick="editDomain('{{ $domain->id }}', '{{ $domain->domain }}')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            تعديل
                        </button>
                        <form method="POST" action="{{ route('tenants.domains.remove', [$tenant->id, $domain->id]) }}" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الدومين؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            @if($tenant->domains->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد دومينات</h3>
                <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة دومين جديد للمستأجر.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Cache Management -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">إدارة الكاش</h2>
                <p class="text-sm text-gray-500">مسح كاش المستأجر لضمان تحديث البيانات</p>
            </div>
            <form method="POST" action="{{ route('tenants.clear-cache', $tenant->id) }}" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    مسح الكاش
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Add Domain Modal -->
<div id="addDomainModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">إضافة دومين جديد</h3>
            <form method="POST" action="{{ route('tenants.domains.add', $tenant->id) }}">
                @csrf
                <div class="mb-4">
                    <label for="domain" class="block text-sm font-medium text-gray-700">الدومين</label>
                    <input type="text" name="domain" id="domain" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="example.com">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideAddDomainModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        إلغاء
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        إضافة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Domain Modal -->
<div id="editDomainModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">تعديل الدومين</h3>
            <form id="editDomainForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_domain" class="block text-sm font-medium text-gray-700">الدومين</label>
                    <input type="text" name="domain" id="edit_domain" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideEditDomainModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        إلغاء
                    </button>
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        تحديث
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddDomainModal() {
    document.getElementById('addDomainModal').classList.remove('hidden');
}

function hideAddDomainModal() {
    document.getElementById('addDomainModal').classList.add('hidden');
}

function editDomain(domainId, currentDomain) {
    document.getElementById('edit_domain').value = currentDomain;
    document.getElementById('editDomainForm').action = '{{ route("tenants.domains.update", [$tenant->id, ":domainId"]) }}'.replace(':domainId', domainId);
    document.getElementById('editDomainModal').classList.remove('hidden');
}

function hideEditDomainModal() {
    document.getElementById('editDomainModal').classList.add('hidden');
}
</script>
@endsection
