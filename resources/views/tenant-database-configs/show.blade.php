@extends('layouts.app')

@section('title', 'عرض إعداد قاعدة البيانات')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">عرض إعداد قاعدة البيانات</h1>
                        <p class="text-blue-100">تفاصيل إعداد قاعدة البيانات: {{ $tenantDatabaseConfig->subdomain }}</p>
                    </div>
                    <div class="flex space-x-4">
                        <button type="button" 
                                onclick="if(typeof testConnectionDirect === 'function') { testConnectionDirect({{ $tenantDatabaseConfig->id }}); } else { alert('الدالة غير معرفة!'); } return false;" 
                                class="bg-green-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors"
                                id="testConnectionBtn">
                            <i class="fas fa-plug mr-2"></i>اختبار الاتصال
                        </button>
                     
                      
                        <a href="{{ route('tenant-database-configs.edit', $tenantDatabaseConfig) }}" 
                           class="bg-yellow-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-yellow-600 transition-colors">
                            <i class="fas fa-edit mr-2"></i>تعديل
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

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">معلومات الإعداد</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Info -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">الـ Subdomain</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->subdomain }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">المستأجر</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    @if($tenantDatabaseConfig->tenant)
                                        {{ $tenantDatabaseConfig->tenant->name }}
                                    @else
                                        <span class="text-gray-500">غير مرتبط</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">نوع قاعدة البيانات</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $tenantDatabaseConfig->driver === 'mysql' ? 'bg-green-100 text-green-800' : 
                                       ($tenantDatabaseConfig->driver === 'pgsql' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ strtoupper($tenantDatabaseConfig->driver) }}
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">الحالة</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $tenantDatabaseConfig->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $tenantDatabaseConfig->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Connection Info -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">الخادم</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->host }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">المنفذ</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->port }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">اسم قاعدة البيانات</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->database_name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">اسم المستخدم</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->username }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">الترميز</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->charset }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">الترتيب</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->collation }}</p>
                            </div>
                        </div>
                        
                        @if($tenantDatabaseConfig->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-500 mb-1">الوصف</label>
                            <p class="text-lg text-gray-900">{{ $tenantDatabaseConfig->description }}</p>
                        </div>
                        @endif
                        
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">تاريخ الإنشاء</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->created_at->format('Y/m/d H:i') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">آخر تحديث</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $tenantDatabaseConfig->updated_at->format('Y/m/d H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Test Modal -->
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
 <script>


// تعريف الدوال في النطاق العام
window.testConnectionDirect = function(configId) {

    const modal = document.getElementById('testModal');
    if (!modal) {
        console.error('Test modal not found');
        showToast('خطأ: لم يتم العثور على نافذة الاختبار', 'error');
        return false;
    }
    
    modal.classList.remove('hidden');
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        modal.classList.add('hidden');
        showToast('خطأ: لم يتم العثور على رمز الأمان', 'error');
        return false;
    }
    
    
    const url = `{{ route('tenant-database-configs.test-connection', $tenantDatabaseConfig) }}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        modal.classList.add('hidden');
        
        if (data.success) {
            showToast('تم الاتصال بقاعدة البيانات بنجاح', 'success');
        } else {
            showToast(data.message || 'فشل الاتصال بقاعدة البيانات', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modal.classList.add('hidden');
        showToast('حدث خطأ أثناء اختبار الاتصال: ' + error.message, 'error');
    });
    
    return false; // منع السلوك الافتراضي
};


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

// الاختبار التلقائي عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    console.log('بدء الاختبار التلقائي...');
    performAutoTest();
});

// دالة الاختبار التلقائي
function performAutoTest() {
    const modal = document.getElementById('autoTestModal');
    const content = document.getElementById('autoTestContent');
    
    // إظهار النافذة
    modal.classList.remove('hidden');
    
    // إضافة رسالة التحميل
    content.innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
            <span class="text-gray-600">جاري اختبار الاتصال بقاعدة البيانات...</span>
        </div>
    `;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const url = `{{ route('tenant-database-configs.test-connection', $tenantDatabaseConfig) }}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log('نتائج الاختبار:', data);
        displayAutoTestResults(data);
    })
    .catch(error => {
        console.error('خطأ في الاختبار:', error);
        displayAutoTestError(error);
    });
}

// عرض نتائج الاختبار التلقائي
function displayAutoTestResults(data) {
    const content = document.getElementById('autoTestContent');
    const runMigrationsBtn = document.getElementById('runMigrationsBtn');
    const createAdminBtn = document.getElementById('createAdminBtn');
    
        if (data.success) {
        // اختبار ناجح - فحص الجداول
        content.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <h4 class="text-green-800 font-semibold">✅ تم الاتصال بقاعدة البيانات بنجاح</h4>
                </div>
                <p class="text-green-700">قاعدة البيانات: <strong>{{ $tenantDatabaseConfig->database_name }}</strong></p>
                <p class="text-green-700">الخادم: <strong>{{ $tenantDatabaseConfig->host }}</strong></p>
                <p class="text-green-700">المنفذ: <strong>{{ $tenantDatabaseConfig->port }}</strong></p>
                <p class="text-green-700">المستخدم: <strong>{{ $tenantDatabaseConfig->username }}</strong></p>
            </div>
        `;
        
        // فحص الجداول
        checkDatabaseTables();
        
    } else {
        // اختبار فاشل
        content.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <i class="fas fa-times-circle text-red-500 mr-2"></i>
                    <h4 class="text-red-800 font-semibold">❌ فشل الاتصال بقاعدة البيانات</h4>
                </div>
                <p class="text-red-700"><strong>السبب:</strong> ${data.message || 'خطأ غير معروف'}</p>
                <div class="mt-3 text-sm text-red-600">
                    <p><strong>الأسباب المحتملة:</strong></p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>قاعدة البيانات غير موجودة</li>
                        <li>بيانات الاتصال غير صحيحة</li>
                        <li>الخادم غير متاح</li>
                        <li>مشكلة في الصلاحيات</li>
                    </ul>
                </div>
            </div>
        `;
    }
}

// فحص الجداول في قاعدة البيانات
function checkDatabaseTables() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const url = `{{ route('tenant-database-configs.check-tables', $tenantDatabaseConfig) }}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log('نتائج فحص الجداول:', data);
        displayTablesCheck(data);
    })
    .catch(error => {
        console.error('خطأ في فحص الجداول:', error);
        displayTablesError(error);
    });
}

// عرض نتائج فحص الجداول
function displayTablesCheck(data) {
    const content = document.getElementById('autoTestContent');
    const runMigrationsBtn = document.getElementById('runMigrationsBtn');
    const createAdminBtn = document.getElementById('createAdminBtn');
    
    let tablesHtml = '';
    let needsMigrations = false;
    let needsAdmin = false;
    
    if (data.success) {
        const tables = data.tables || [];
        const databaseName = data.database_name || 'غير محدد';
        
        // إضافة معلومات قاعدة البيانات
        content.innerHTML += `
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <div class="flex items-center mb-1">
                    <i class="fas fa-database text-gray-500 mr-2"></i>
                    <h5 class="text-gray-700 font-medium">معلومات قاعدة البيانات</h5>
                </div>
                <p class="text-gray-600 text-sm">اسم قاعدة البيانات: <strong>${databaseName}</strong></p>
                <p class="text-gray-600 text-sm">عدد الجداول: <strong>${tables.length}</strong></p>
            </div>
        `;
        
        if (tables.length === 0) {
            tablesHtml = `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <h4 class="text-yellow-800 font-semibold">⚠️ قاعدة البيانات فارغة</h4>
                    </div>
                    <p class="text-yellow-700">لا توجد جداول في قاعدة البيانات. تحتاج إلى تشغيل المايكريشن.</p>
                </div>
            `;
            needsMigrations = true;
        } else {
            // فحص إذا كانت الجداول موجودة بدون جدول migrations
            const hasMigrationsTable = tables.includes('migrations');
            const hasUsersTable = tables.includes('users');
            
            if (!hasMigrationsTable && tables.length > 0) {
                tablesHtml = `
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                            <h4 class="text-orange-800 font-semibold">ℹ️ جداول موجودة بدون جدول migrations</h4>
                        </div>
                        <p class="text-orange-700">يوجد ${tables.length} جدول في قاعدة البيانات ولكن لا يوجد جدول migrations. سيتم إصلاح هذا تلقائياً.</p>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            ${tables.map(table => `<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm">${table}</span>`).join('')}
                        </div>
                    </div>
                `;
                needsMigrations = true;
            } else if (hasMigrationsTable && tables.length > 0) {
                // حالة وجود جدول migrations ولكن قد تكون هناك مشكلة في المايكريشن
                tablesHtml = `
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-table text-blue-500 mr-2"></i>
                            <h4 class="text-blue-800 font-semibold">📊 الجداول الموجودة (${tables.length})</h4>
                        </div>
                        <p class="text-blue-700">قاعدة البيانات تحتوي على جداول موجودة. إذا واجهت مشاكل في المايكريشن، سيتم إصلاحها تلقائياً.</p>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            ${tables.map(table => `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">${table}</span>`).join('')}
                        </div>
                    </div>
                `;
                needsMigrations = true; // إظهار زر المايكريشن في حالة وجود مشاكل
            } else {
                tablesHtml = `
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-table text-blue-500 mr-2"></i>
                            <h4 class="text-blue-800 font-semibold">📊 الجداول الموجودة (${tables.length})</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            ${tables.map(table => `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">${table}</span>`).join('')}
                        </div>
                    </div>
                `;
                
                // فحص وجود جدول المستخدمين
                if (!hasUsersTable) {
                    needsMigrations = true;
                }
                
                // فحص وجود أدمن
                checkAdminUser(tables);
            }
        }
    } else {
        tablesHtml = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <i class="fas fa-times-circle text-red-500 mr-2"></i>
                    <h4 class="text-red-800 font-semibold">❌ فشل فحص الجداول</h4>
                </div>
                <p class="text-red-700">${data.message || 'خطأ غير معروف'}</p>
            </div>
        `;
    }
    
    content.innerHTML += tablesHtml;
    
    // إظهار الأزرار حسب الحاجة
    if (needsMigrations) {
        runMigrationsBtn.classList.remove('hidden');
        // إظهار حقول المستخدم الافتراضي
        document.getElementById('adminUserFields').classList.remove('hidden');
    } else {
        // إخفاء الحقول إذا لم تكن هناك حاجة للمايكريشن
        document.getElementById('adminUserFields').classList.add('hidden');
        runMigrationsBtn.classList.add('hidden');
    }
    if (needsAdmin) {
        createAdminBtn.classList.remove('hidden');
    }
}

// فحص وجود أدمن
function checkAdminUser(tables) {
    if (!tables.includes('users')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const url = `{{ route('tenant-database-configs.check-admin', $tenantDatabaseConfig) }}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log('نتائج فحص الأدمن:', data);
        displayAdminCheck(data);
    })
    .catch(error => {
        console.error('خطأ في فحص الأدمن:', error);
    });
}

// عرض نتائج فحص الأدمن
function displayAdminCheck(data) {
    const content = document.getElementById('autoTestContent');
    const createAdminBtn = document.getElementById('createAdminBtn');
    
    if (data.success && data.hasAdmin) {
        content.innerHTML += `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <i class="fas fa-user-check text-green-500 mr-2"></i>
                    <h4 class="text-green-800 font-semibold">👤 يوجد أدمن في النظام</h4>
                </div>
                <p class="text-green-700">البريد الإلكتروني: <strong>${data.adminEmail}</strong></p>
            </div>
        `;
    } else {
        content.innerHTML += `
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <i class="fas fa-user-plus text-orange-500 mr-2"></i>
                    <h4 class="text-orange-800 font-semibold">👤 لا يوجد أدمن في النظام</h4>
                </div>
                <p class="text-orange-700">تحتاج إلى إنشاء مستخدم أدمن للوصول إلى النظام.</p>
            </div>
        `;
        createAdminBtn.classList.remove('hidden');
    }
}

// عرض خطأ فحص الجداول
function displayTablesError(error) {
    const content = document.getElementById('autoTestContent');
    content.innerHTML += `
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                <h4 class="text-red-800 font-semibold">❌ خطأ في فحص الجداول</h4>
            </div>
            <p class="text-red-700">${error.message}</p>
        </div>
    `;
}

// عرض خطأ الاختبار التلقائي
function displayAutoTestError(error) {
    const content = document.getElementById('autoTestContent');
    content.innerHTML = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                <h4 class="text-red-800 font-semibold">❌ خطأ في الاختبار التلقائي</h4>
            </div>
            <p class="text-red-700">${error.message}</p>
        </div>
    `;
}

// إغلاق نافذة الاختبار التلقائي
function closeAutoTestModal() {
    document.getElementById('autoTestModal').classList.add('hidden');
}

// تشغيل المايكريشن
function runMigrations() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const url = `{{ route('tenant-database-configs.run-migrations', $tenantDatabaseConfig) }}`;
    
    // الحصول على قيم الحقول
    const adminEmail = document.getElementById('adminEmail').value;
    const adminPassword = document.getElementById('adminPassword').value;
    
    showToast('جاري تشغيل المايكريشن...', 'info');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            admin_email: adminEmail,
            admin_password: adminPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('تم تشغيل المايكريشن بنجاح', 'success');
            // إخفاء قسم إعدادات المستخدم الافتراضي
            document.getElementById('adminUserFields').classList.add('hidden');
            // إخفاء زر تشغيل المايكريشن
            document.getElementById('runMigrationsBtn').classList.add('hidden');
            closeAutoTestModal();
            // إعادة تشغيل الاختبار التلقائي
            setTimeout(() => performAutoTest(), 1000);
        } else {
            showToast('فشل تشغيل المايكريشن: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('خطأ في تشغيل المايكريشن: ' + error.message, 'error');
    });
}

// إنشاء مستخدم أدمن
function createAdminUser() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const url = `{{ route('tenant-database-configs.create-admin', $tenantDatabaseConfig) }}`;
    
    showToast('جاري إنشاء مستخدم الأدمن...', 'info');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            email: 'admin@admin.com',
            password: '12345678',
            name: 'مدير النظام'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('تم إنشاء مستخدم الأدمن بنجاح', 'success');
            closeAutoTestModal();
        } else {
            showToast('فشل إنشاء مستخدم الأدمن: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('خطأ في إنشاء مستخدم الأدمن: ' + error.message, 'error');
    });
}

</script>
 

    <!-- Auto Test Results Modal -->
    <div id="autoTestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">نتائج الاختبار التلقائي</h3>
                        <button onclick="closeAutoTestModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="autoTestContent" class="space-y-4">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </div>
                    
                    <!-- حقول المستخدم الافتراضي -->
                    <div id="adminUserFields" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                        <h4 class="text-md font-medium text-gray-900 mb-4">إعدادات المستخدم الافتراضي</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="adminEmail" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                                <input type="email" 
                                       id="adminEmail" 
                                       value="admin@admin.com"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="adminPassword" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور</label>
                                <input type="password" 
                                       id="adminPassword" 
                                       value="12345678"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button onclick="closeAutoTestModal()" 
                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            إغلاق
                        </button>
                        <button onclick="runMigrations()" 
                                id="runMigrationsBtn"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 hidden">
                            <i class="fas fa-database mr-2"></i>تشغيل المايكريشن
                        </button>
                        <button onclick="createAdminUser()" 
                                id="createAdminBtn"
                                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 hidden">
                            <i class="fas fa-user-plus mr-2"></i>إنشاء أدمن
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

