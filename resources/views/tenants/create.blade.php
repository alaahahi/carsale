@extends('layouts.app')

@section('title', 'إضافة مستأجر جديد')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">إضافة مستأجر جديد</h1>
                        <p class="text-blue-100">إنشاء مستأجر جديد مع إعدادات قاعدة البيانات</p>
                    </div>
                    <a href="{{ route('tenants.index') }}" 
                       class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                        <i class="fas fa-arrow-right mr-2"></i>العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('tenants.store') }}">
                @csrf
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">اسم المستأجر *</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700">الدومين *</label>
                        <input type="text" name="domain" id="domain" required value="{{ old('domain') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="example.com">
                        @error('domain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">الهاتف</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">العنوان</label>
                        <textarea name="address" id="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subscription_plan" class="block text-sm font-medium text-gray-700">خطة الاشتراك *</label>
                        <select name="subscription_plan" id="subscription_plan" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">اختر خطة الاشتراك</option>
                            <option value="basic" {{ old('subscription_plan') === 'basic' ? 'selected' : '' }}>أساسي</option>
                            <option value="premium" {{ old('subscription_plan') === 'premium' ? 'selected' : '' }}>مميز</option>
                            <option value="enterprise" {{ old('subscription_plan') === 'enterprise' ? 'selected' : '' }}>مؤسسي</option>
                        </select>
                        @error('subscription_plan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subscription_expires_at" class="block text-sm font-medium text-gray-700">انتهاء الاشتراك</label>
                        <input type="date" name="subscription_expires_at" id="subscription_expires_at" value="{{ old('subscription_expires_at') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('subscription_expires_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                    <!-- Database Configuration -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">إعدادات قاعدة البيانات</h3>
                        
                        <!-- Database Creation Method -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">طريقة إنشاء قاعدة البيانات</label>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="radio" 
                                           id="db_method_auto" 
                                           name="database_creation_method" 
                                           value="auto" 
                                           {{ old('database_creation_method', 'auto') === 'auto' ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <label for="db_method_auto" class="ml-3 text-sm text-gray-700">
                                        <span class="font-medium">تلقائي</span> - استخدام إعدادات النظام الافتراضية
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" 
                                           id="db_method_manual" 
                                           name="database_creation_method" 
                                           value="manual" 
                                           {{ old('database_creation_method') === 'manual' ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <label for="db_method_manual" class="ml-3 text-sm text-gray-700">
                                        <span class="font-medium">يدوي</span> - إعدادات مخصصة لقاعدة البيانات
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Database Configuration -->
                        <div id="manual_db_config" class="hidden">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-md font-semibold text-gray-800 mb-4">إعدادات قاعدة البيانات المخصصة</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="db_subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                                            الـ Subdomain <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               id="db_subdomain" 
                                               name="db_subdomain" 
                                               value="{{ old('db_subdomain') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_subdomain') border-red-500 @enderror"
                                               placeholder="aindubai_alhadaf">
                                        @error('db_subdomain')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-sm text-gray-500">سيتم استخدام هذا الـ subdomain للاتصال بقاعدة البيانات</p>
                                    </div>

                                    <div>
                                        <label for="db_driver" class="block text-sm font-medium text-gray-700 mb-2">
                                            نوع قاعدة البيانات <span class="text-red-500">*</span>
                                        </label>
                                        <select id="db_driver" 
                                                name="db_driver" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_driver') border-red-500 @enderror">
                                            <option value="">اختر نوع قاعدة البيانات</option>
                                            <option value="mysql" {{ old('db_driver') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                                            <option value="pgsql" {{ old('db_driver') == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                                            <option value="sqlite" {{ old('db_driver') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                                        </select>
                                        @error('db_driver')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_host" class="block text-sm font-medium text-gray-700 mb-2">
                                            الخادم <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               id="db_host" 
                                               name="db_host" 
                                               value="{{ old('db_host', '127.0.0.1') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_host') border-red-500 @enderror"
                                               placeholder="127.0.0.1">
                                        @error('db_host')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_port" class="block text-sm font-medium text-gray-700 mb-2">
                                            المنفذ <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" 
                                               id="db_port" 
                                               name="db_port" 
                                               value="{{ old('db_port', '3306') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_port') border-red-500 @enderror"
                                               placeholder="3306"
                                               min="1" 
                                               max="65535">
                                        @error('db_port')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            اسم قاعدة البيانات <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               id="db_name" 
                                               name="db_name" 
                                               value="{{ old('db_name') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_name') border-red-500 @enderror"
                                               placeholder="hospital_alhadaf">
                                        @error('db_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_username" class="block text-sm font-medium text-gray-700 mb-2">
                                            اسم المستخدم <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               id="db_username" 
                                               name="db_username" 
                                               value="{{ old('db_username') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_username') border-red-500 @enderror"
                                               placeholder="root">
                                        @error('db_username')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_password" class="block text-sm font-medium text-gray-700 mb-2">
                                            كلمة المرور <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" 
                                                   id="db_password" 
                                                   name="db_password" 
                                                   value="{{ old('db_password') }}"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_password') border-red-500 @enderror"
                                                   placeholder="كلمة المرور">
                                            <button type="button" 
                                                    onclick="toggleDbPassword()"
                                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                                <i class="fas fa-eye" id="dbPasswordToggleIcon"></i>
                                            </button>
                                        </div>
                                        @error('db_password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_charset" class="block text-sm font-medium text-gray-700 mb-2">
                                            الترميز
                                        </label>
                                        <input type="text" 
                                               id="db_charset" 
                                               name="db_charset" 
                                               value="{{ old('db_charset', 'utf8mb4') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_charset') border-red-500 @enderror"
                                               placeholder="utf8mb4">
                                        @error('db_charset')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="db_collation" class="block text-sm font-medium text-gray-700 mb-2">
                                            الترتيب
                                        </label>
                                        <input type="text" 
                                               id="db_collation" 
                                               name="db_collation" 
                                               value="{{ old('db_collation', 'utf8mb4_unicode_ci') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('db_collation') border-red-500 @enderror"
                                               placeholder="utf8mb4_unicode_ci">
                                        @error('db_collation')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Migration Options -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">خيارات المايكريشن</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="run_migrations" 
                                   name="run_migrations" 
                                   value="1"
                                   {{ old('run_migrations', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="run_migrations" class="ml-3 text-sm text-gray-700">
                                <span class="font-medium">تشغيل المايكريشن</span> - تشغيل المايكريشن تلقائياً بعد إنشاء قاعدة البيانات
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="force_migrations" 
                                   name="force_migrations" 
                                   value="1"
                                   {{ old('force_migrations') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="force_migrations" class="ml-3 text-sm text-gray-700">
                                <span class="font-medium">فرض المايكريشن</span> - فرض تشغيل المايكريشن حتى لو كانت قاعدة البيانات موجودة
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('tenants.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        إلغاء
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        إنشاء المستأجر
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Toggle database configuration visibility
document.querySelectorAll('input[name="database_creation_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const manualConfig = document.getElementById('manual_db_config');
        if (this.value === 'manual') {
            manualConfig.classList.remove('hidden');
        } else {
            manualConfig.classList.add('hidden');
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const selectedMethod = document.querySelector('input[name="database_creation_method"]:checked');
    if (selectedMethod && selectedMethod.value === 'manual') {
        document.getElementById('manual_db_config').classList.remove('hidden');
    }
});

// Toggle password visibility
function toggleDbPassword() {
    const passwordInput = document.getElementById('db_password');
    const toggleIcon = document.getElementById('dbPasswordToggleIcon');
    
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
document.getElementById('db_driver').addEventListener('change', function() {
    const portInput = document.getElementById('db_port');
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

// Auto-generate database name from subdomain
document.getElementById('db_subdomain').addEventListener('input', function() {
    const dbNameInput = document.getElementById('db_name');
    if (!dbNameInput.value) {
        dbNameInput.value = 'hospital_' + this.value;
    }
});
</script>
@endsection
