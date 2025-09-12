@extends('layouts.app')

@section('title', 'تعديل إعداد قاعدة البيانات')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-to-r from-yellow-600 to-orange-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">تعديل إعداد قاعدة البيانات</h1>
                        <p class="text-yellow-100">تعديل إعداد قاعدة البيانات: {{ $tenantDatabaseConfig->subdomain }}</p>
                    </div>
                    <div class="flex space-x-4">
                        <button onclick="testConnection({{ $tenantDatabaseConfig->id }})" 
                                class="bg-green-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors">
                            <i class="fas fa-plug mr-2"></i>اختبار الاتصال
                        </button>
                        <a href="{{ route('tenant-database-configs.show', $tenantDatabaseConfig) }}" 
                           class="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors">
                            <i class="fas fa-eye mr-2"></i>عرض
                        </a>
                        <a href="{{ route('tenant-database-configs.index') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors">
                            <i class="fas fa-arrow-right mr-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">معلومات الاتصال</h2>
                </div>
                
                <form action="{{ route('tenant-database-configs.update', $tenantDatabaseConfig) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tenant -->
                        <div>
                            <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-2">
                                المستأجر
                            </label>
                            <select id="tenant_id" 
                                    name="tenant_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tenant_id') border-red-500 @enderror">
                                <option value="">اختر المستأجر (اختياري)</option>
                                @foreach($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $tenantDatabaseConfig->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tenant_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subdomain -->
                        <div>
                            <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                                الـ Subdomain <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="subdomain" 
                                   name="subdomain" 
                                   value="{{ old('subdomain', $tenantDatabaseConfig->subdomain) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subdomain') border-red-500 @enderror"
                                   placeholder="مثال: aindubai_alhadaf"
                                   required>
                            @error('subdomain')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Driver -->
                        <div>
                            <label for="driver" class="block text-sm font-medium text-gray-700 mb-2">
                                نوع قاعدة البيانات <span class="text-red-500">*</span>
                            </label>
                            <select id="driver" 
                                    name="driver" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('driver') border-red-500 @enderror"
                                    required>
                                <option value="">اختر نوع قاعدة البيانات</option>
                                <option value="mysql" {{ old('driver', $tenantDatabaseConfig->driver) == 'mysql' ? 'selected' : '' }}>MySQL</option>
                                <option value="pgsql" {{ old('driver', $tenantDatabaseConfig->driver) == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                                <option value="sqlite" {{ old('driver', $tenantDatabaseConfig->driver) == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                            </select>
                            @error('driver')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Host -->
                        <div>
                            <label for="host" class="block text-sm font-medium text-gray-700 mb-2">
                                الخادم <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="host" 
                                   name="host" 
                                   value="{{ old('host', $tenantDatabaseConfig->host) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('host') border-red-500 @enderror"
                                   placeholder="127.0.0.1"
                                   required>
                            @error('host')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Port -->
                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700 mb-2">
                                المنفذ <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="port" 
                                   name="port" 
                                   value="{{ old('port', $tenantDatabaseConfig->port) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('port') border-red-500 @enderror"
                                   placeholder="3306"
                                   min="1" 
                                   max="65535"
                                   required>
                            @error('port')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Database Name -->
                        <div>
                            <label for="database_name" class="block text-sm font-medium text-gray-700 mb-2">
                                اسم قاعدة البيانات <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="database_name" 
                                   name="database_name" 
                                   value="{{ old('database_name', $tenantDatabaseConfig->database_name) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('database_name') border-red-500 @enderror"
                                   placeholder="اسم قاعدة البيانات"
                                   required>
                            @error('database_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                اسم المستخدم <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $tenantDatabaseConfig->username) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('username') border-red-500 @enderror"
                                   placeholder="اسم المستخدم"
                                   required>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                كلمة المرور  
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       value="{{ old('password', $tenantDatabaseConfig->password) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                                       placeholder="كلمة المرور"
                                        >
                                <button type="button" 
                                        onclick="togglePassword()"
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Charset -->
                        <div>
                            <label for="charset" class="block text-sm font-medium text-gray-700 mb-2">
                                الترميز
                            </label>
                            <input type="text" 
                                   id="charset" 
                                   name="charset" 
                                   value="{{ old('charset', $tenantDatabaseConfig->charset) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('charset') border-red-500 @enderror"
                                   placeholder="utf8mb4">
                            @error('charset')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Collation -->
                        <div>
                            <label for="collation" class="block text-sm font-medium text-gray-700 mb-2">
                                الترتيب
                            </label>
                            <input type="text" 
                                   id="collation" 
                                   name="collation" 
                                   value="{{ old('collation', $tenantDatabaseConfig->collation) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('collation') border-red-500 @enderror"
                                   placeholder="utf8mb4_unicode_ci">
                            @error('collation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="md:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $tenantDatabaseConfig->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    تفعيل هذا الإعداد
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                الوصف
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                      placeholder="وصف اختياري للإعداد">{{ old('description', $tenantDatabaseConfig->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('tenant-database-configs.show', $tenantDatabaseConfig) }}" 
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            إلغاء
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>حفظ التغييرات
                        </button>
                    </div>
                </form>
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
</div>
@endsection

@section('scripts')
<script>
function testConnection(configId) {
    const modal = document.getElementById('testModal');
    modal.classList.remove('hidden');
    
    fetch(`/admin/tenant-database-configs/${configId}/test-connection`, {
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

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Auto-fill port based on driver
document.getElementById('driver').addEventListener('change', function() {
    const portInput = document.getElementById('port');
    const driver = this.value;
    
    switch(driver) {
        case 'mysql':
            portInput.value = '3306';
            break;
        case 'pgsql':
            portInput.value = '5432';
            break;
        case 'sqlite':
            portInput.value = '';
            portInput.disabled = true;
            break;
        default:
            portInput.value = '3306';
            portInput.disabled = false;
    }
});

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
