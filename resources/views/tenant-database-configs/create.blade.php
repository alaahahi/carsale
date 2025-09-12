@extends('layouts.app')

@section('title', 'إضافة إعداد قاعدة بيانات جديد')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-to-r from-green-600 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">إضافة إعداد قاعدة بيانات جديد</h1>
                        <p class="text-green-100">إضافة اتصال ديناميكي جديد بقاعدة البيانات</p>
                    </div>
                    <a href="{{ route('tenant-database-configs.index') }}" 
                       class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-50 transition-colors">
                        <i class="fas fa-arrow-right mr-2"></i>العودة للقائمة
                    </a>
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
                
                <form action="{{ route('tenant-database-configs.store') }}" method="POST" class="p-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Subdomain -->
                        <div class="md:col-span-2">
                            <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                                الـ Subdomain <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="subdomain" 
                                   name="subdomain" 
                                   value="{{ old('subdomain') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subdomain') border-red-500 @enderror"
                                   placeholder="مثال: aindubai_alhadaf"
                                   required>
                            @error('subdomain')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">سيتم استخدام هذا الـ subdomain للاتصال بقاعدة البيانات</p>
                        </div>

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
                                    <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tenant_id')
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
                                <option value="mysql" {{ old('driver') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                                <option value="pgsql" {{ old('driver') == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                                <option value="sqlite" {{ old('driver') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
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
                                   value="{{ old('host', '127.0.0.1') }}"
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
                                   value="{{ old('port', '3306') }}"
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
                                   value="{{ old('database_name') }}"
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
                                   value="{{ old('username') }}"
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
                             </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       value="{{ old('password') }}"
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
                                   value="{{ old('charset', 'utf8mb4') }}"
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
                                   value="{{ old('collation', 'utf8mb4_unicode_ci') }}"
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
                                       {{ old('is_active', true) ? 'checked' : '' }}
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
                                      placeholder="وصف اختياري للإعداد">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('tenant-database-configs.index') }}" 
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            إلغاء
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>حفظ الإعداد
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
</script>
@endsection
