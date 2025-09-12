# إعدادات قاعدة البيانات للمستأجرين

## 📍 أين يمكن تحديد أو تعديل قاعدة البيانات التي يتصل عليها أي مستأجر

### 1. ملف الإعدادات الرئيسي: `config/tenancy.php`

هذا هو الملف الرئيسي الذي يتحكم في إعدادات قاعدة البيانات للمستأجرين:

```php
'database' => [
    // الاتصال بقاعدة البيانات المركزية
    'central_connection' => env('DB_CONNECTION', 'mysql'),

    // الاتصال المستخدم كقالب لإنشاء اتصالات المستأجرين
    'template_tenant_connection' => 'mysql',

    // تسمية قواعد بيانات المستأجرين
    'prefix' => 'car_tenant_',  // البادئة
    'suffix' => '',             // اللاحقة

    // مديري قواعد البيانات
    'managers' => [
        'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
        'pgsql' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
        'sqlite' => Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager::class,
    ],
],
```

### 2. ملف إعدادات قاعدة البيانات: `config/database.php`

يحتوي على إعدادات الاتصال بقاعدة البيانات:

```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
],
```

### 3. ملف البيئة: `.env`

يحتوي على القيم الفعلية للإعدادات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=car-ai1
DB_USERNAME=root
DB_PASSWORD=
```

## 🔧 كيفية تعديل إعدادات قاعدة البيانات للمستأجرين

### 1. تغيير البادئة (Prefix)

لتغيير اسم قاعدة البيانات من `car_tenant_1` إلى `tenant_1`:

```php
// في config/tenancy.php
'prefix' => 'tenant_',
```

### 2. تغيير نوع قاعدة البيانات

لتغيير من MySQL إلى PostgreSQL:

```php
// في config/tenancy.php
'template_tenant_connection' => 'pgsql',

// في config/database.php - إضافة اتصال PostgreSQL
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
],
```

### 3. تغيير خادم قاعدة البيانات

لتغيير الخادم من `127.0.0.1` إلى `192.168.1.100`:

```env
# في ملف .env
DB_HOST=192.168.1.100
DB_PORT=3306
```

### 4. إضافة لاحقة (Suffix)

لإضافة لاحقة لاسم قاعدة البيانات:

```php
// في config/tenancy.php
'suffix' => '_db',
// النتيجة: car_tenant_1_db
```

## 🎯 أمثلة عملية

### مثال 1: تغيير اسم قاعدة البيانات

```php
// config/tenancy.php
'database' => [
    'prefix' => 'store_',
    'suffix' => '_v2',
],
// النتيجة: store_1_v2, store_2_v2, etc.
```

### مثال 2: استخدام خادم مختلف للمستأجرين

```php
// config/database.php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
    ],
    
    // اتصال منفصل للمستأجرين
    'tenant_mysql' => [
        'driver' => 'mysql',
        'host' => env('TENANT_DB_HOST', '192.168.1.100'),
        'port' => env('TENANT_DB_PORT', '3306'),
        'database' => env('TENANT_DB_DATABASE', 'tenant_db'),
        'username' => env('TENANT_DB_USERNAME', 'tenant_user'),
        'password' => env('TENANT_DB_PASSWORD', ''),
    ],
],

// config/tenancy.php
'template_tenant_connection' => 'tenant_mysql',
```

### مثال 3: استخدام مدير قاعدة بيانات مختلف

```php
// config/tenancy.php
'managers' => [
    'mysql' => Stancl\Tenancy\TenantDatabaseManagers\PermissionControlledMySQLDatabaseManager::class,
],
```

## 🔍 كيفية التحقق من الإعدادات الحالية

### 1. عبر صفحة إدارة المستأجرين

اذهب إلى: `http://127.0.0.1:8000/central-admin/tenants/database-info`

### 2. عبر الكود

```php
// الحصول على معلومات قاعدة البيانات المركزية
$centralConnection = DB::connection();
echo "Central DB: " . $centralConnection->getDatabaseName();

// الحصول على معلومات قاعدة بيانات مستأجر
$tenant = Tenant::find(1);
tenancy()->initialize($tenant);
$tenantConnection = DB::connection();
echo "Tenant DB: " . $tenantConnection->getDatabaseName();
```

## ⚠️ ملاحظات مهمة

1. **تأكد من وجود قاعدة البيانات** قبل تغيير الإعدادات
2. **قم بعمل نسخة احتياطية** قبل التعديل
3. **اختبر الإعدادات** في بيئة التطوير أولاً
4. **امسح الكاش** بعد التعديل: `php artisan tenants:clear-cache`

## 🚀 خطوات التطبيق

1. **عدّل الملفات المطلوبة**
2. **امسح الكاش:** `php artisan tenants:clear-cache`
3. **اختبر الاتصال:** `http://127.0.0.1:8000/central-admin/tenants/database-info`
4. **تحقق من إنشاء المستأجرين الجدد**

---

**ملف الإعدادات الرئيسي:** `config/tenancy.php`  
**ملف إعدادات قاعدة البيانات:** `config/database.php`  
**ملف البيئة:** `.env`
