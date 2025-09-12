# دليل تعديل قاعدة البيانات للمستأجرين

## ✅ نعم، يمكنك تعديل قاعدة البيانات التي يتصل عليها المستأجر

### 🔧 الطرق المختلفة لتعديل قاعدة البيانات:

## 1. **تعديل البادئة (Prefix) - للتأثير على المستأجرين الجدد**

### في `config/tenancy.php`:
```php
'database' => [
    'prefix' => 'store_',  // بدلاً من aindubai_
    'suffix' => '',
],
```

**النتيجة:** المستأجرين الجدد سيحصلون على قواعد بيانات باسم `store_1`, `store_2`, etc.

## 2. **تعديل اللاحقة (Suffix)**

### في `config/tenancy.php`:
```php
'database' => [
    'prefix' => 'aindubai_',
    'suffix' => '_v2',  // إضافة لاحقة
],
```

**النتيجة:** المستأجرين الجدد سيحصلون على قواعد بيانات باسم `aindubai_1_v2`, `aindubai_2_v2`, etc.

## 3. **تعديل نوع قاعدة البيانات**

### في `config/tenancy.php`:
```php
'database' => [
    'template_tenant_connection' => 'pgsql',  // بدلاً من mysql
],
```

### في `config/database.php` - إضافة اتصال PostgreSQL:
```php
'connections' => [
    'pgsql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
    ],
],
```

## 4. **تعديل خادم قاعدة البيانات**

### في `.env`:
```env
DB_HOST=192.168.1.100      # بدلاً من 127.0.0.1
DB_PORT=3306
DB_DATABASE=car-ai1
DB_USERNAME=root
DB_PASSWORD=
```

## 5. **إنشاء اتصال منفصل للمستأجرين**

### في `config/database.php`:
```php
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
```

### في `config/tenancy.php`:
```php
'database' => [
    'template_tenant_connection' => 'tenant_mysql',
],
```

### في `.env`:
```env
# إعدادات قاعدة البيانات المركزية
DB_HOST=127.0.0.1
DB_DATABASE=car-ai1

# إعدادات قاعدة البيانات للمستأجرين
TENANT_DB_HOST=192.168.1.100
TENANT_DB_DATABASE=tenant_db
TENANT_DB_USERNAME=tenant_user
TENANT_DB_PASSWORD=tenant_password
```

## 6. **تعديل قاعدة بيانات مستأجر محدد**

### عبر الكود:
```php
// تغيير قاعدة بيانات مستأجر محدد
$tenant = Tenant::find(1);

// إنشاء قاعدة بيانات جديدة
$newDatabaseName = 'custom_store_1';
$tenant->database()->create($newDatabaseName);

// أو استخدام Artisan command
Artisan::call('tenants:migrate', [
    '--tenants' => $tenant->id,
    '--database' => $newDatabaseName,
]);
```

## 7. **استخدام مدير قاعدة بيانات مختلف**

### في `config/tenancy.php`:
```php
'managers' => [
    'mysql' => Stancl\Tenancy\TenantDatabaseManagers\PermissionControlledMySQLDatabaseManager::class,
],
```

هذا المدير ينشئ مستخدم قاعدة بيانات منفصل لكل مستأجر.

## 🔍 كيفية التحقق من التغييرات

### 1. **عبر صفحة إدارة المستأجرين:**
- اذهب إلى: `http://127.0.0.1:8000/central-admin/tenants/database-info`
- ستجد جميع قواعد البيانات مع أسمائها الجديدة

### 2. **عبر الكود:**
```php
// الحصول على معلومات قاعدة البيانات المركزية
$centralConnection = DB::connection();
echo "Central DB: " . $centralConnection->getDatabaseName();

// الحصول على معلومات قاعدة بيانات مستأجر
$tenant = Tenant::find(1);
tenancy()->initialize($tenant);
$tenantConnection = DB::connection();
echo "Tenant DB: " . $tenantConnection->getDatabaseName();
echo "Tenant Host: " . $tenantConnection->getConfig('host');
echo "Tenant Port: " . $tenantConnection->getConfig('port');
```

## ⚠️ ملاحظات مهمة

### 1. **المستأجرين الموجودين:**
- سيحتفظون بقواعد بياناتهم الحالية
- التغييرات تؤثر على المستأجرين الجدد فقط

### 2. **بعد التعديل:**
```bash
# مسح الكاش
php artisan tenants:clear-cache --all

# إعادة تشغيل الخادم
php artisan serve
```

### 3. **التحقق من الاتصال:**
- تأكد من وجود قاعدة البيانات
- تأكد من صحة بيانات الاتصال
- اختبر الاتصال قبل التطبيق

## 🚀 مثال عملي

### تغيير من `aindubai_` إلى `store_`:

1. **عدّل `config/tenancy.php`:**
   ```php
   'prefix' => 'store_',
   ```

2. **امسح الكاش:**
   ```bash
   php artisan tenants:clear-cache --all
   ```

3. **النتيجة:**
   - المستأجرين الموجودين: `aindubai_1`, `aindubai_2`
   - المستأجرين الجدد: `store_1`, `store_2`

## 📋 ملخص الطرق

| الطريقة | الملف | التأثير |
|---------|-------|---------|
| تغيير البادئة | `config/tenancy.php` | المستأجرين الجدد |
| تغيير اللاحقة | `config/tenancy.php` | المستأجرين الجدد |
| تغيير نوع قاعدة البيانات | `config/tenancy.php` | المستأجرين الجدد |
| تغيير الخادم | `.env` | جميع المستأجرين |
| اتصال منفصل | `config/database.php` | جميع المستأجرين |
| مستأجر محدد | الكود | مستأجر واحد |

---

**ملف الإعدادات الرئيسي:** `config/tenancy.php`  
**ملف إعدادات قاعدة البيانات:** `config/database.php`  
**ملف البيئة:** `.env`  
**صفحة التحقق:** `http://127.0.0.1:8000/central-admin/tenants/database-info`
