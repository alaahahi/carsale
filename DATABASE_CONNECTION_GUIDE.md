# دليل إعدادات قاعدة البيانات للمستأجرين

## نظرة عامة

نظام Multi-Tenancy يستخدم قاعدة بيانات منفصلة لكل مستأجر. هذا يعني أن كل مستأجر له قاعدة بيانات خاصة به مع نفس الجداول ولكن بيانات مختلفة.

## إعدادات قاعدة البيانات

### 1. قاعدة البيانات المركزية (Central Database)

**الملف:** `config/database.php`
```php
'default' => env('DB_CONNECTION', 'mysql'),

'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        // ... باقي الإعدادات
    ],
],
```

**الغرض:** تخزين معلومات المستأجرين والدومينات

### 2. إعدادات التينانسي (Tenancy Configuration)

**الملف:** `config/tenancy.php`
```php
'database' => [
    'central_connection' => env('DB_CONNECTION', 'mysql'),
    'template_tenant_connection' => 'mysql',
    'prefix' => 'car_tenant_',
    'suffix' => '',
    'managers' => [
        'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
    ],
],
```

**المعاملات:**
- `central_connection`: الاتصال بقاعدة البيانات المركزية
- `template_tenant_connection`: قالب الاتصال للمستأجرين
- `prefix`: البادئة لأسماء قواعد بيانات المستأجرين
- `suffix`: اللاحقة لأسماء قواعد بيانات المستأجرين

### 3. تسمية قواعد البيانات

**النمط:** `{prefix}{tenant_id}{suffix}`

**مثال:** `car_tenant_12345`

## كيفية عمل النظام

### 1. إنشاء مستأجر جديد

عند إنشاء مستأجر جديد:

1. **إنشاء سجل في قاعدة البيانات المركزية:**
   ```php
   $tenant = Tenant::create([
       'id' => Str::uuid(),
       'name' => $request->name,
       // ... باقي البيانات
   ]);
   ```

2. **إنشاء قاعدة بيانات جديدة:**
   ```php
   \Artisan::call('tenants:migrate', [
       '--tenants' => $tenant->id,
   ]);
   ```

3. **إنشاء الدومين:**
   ```php
   Domain::create([
       'domain' => $request->domain,
       'tenant_id' => $tenant->id,
   ]);
   ```

### 2. تبديل قاعدة البيانات

عند الوصول لدومين مستأجر:

1. **استخراج الدومين من الطلب**
2. **البحث عن المستأجر في الكاش**
3. **تهيئة التينانسي:**
   ```php
   tenancy()->initialize($tenant);
   ```

4. **تبديل الاتصال تلقائياً لقاعدة بيانات المستأجر**

## أدوات التحقق من قاعدة البيانات

### 1. فحص قاعدة بيانات مستأجر محدد

**الراوت:** `GET /admin/tenants/{id}/check-database`

**الاستخدام:**
```javascript
fetch(`/admin/tenants/${tenantId}/check-database`)
    .then(response => response.json())
    .then(data => {
        console.log('Database Info:', data);
    });
```

**المعلومات المُرجعة:**
- اسم قاعدة البيانات
- الخادم والمنفذ
- عدد الجداول
- حالة الاتصال

### 2. معلومات جميع قواعد البيانات

**الراوت:** `GET /admin/tenants/database-info`

**المعلومات المُرجعة:**
- قاعدة البيانات المركزية
- جميع قواعد بيانات المستأجرين
- حالة كل اتصال

## الملفات المهمة

### 1. Middleware
- `app/Http/Middleware/TenantMiddleware.php` - تحديد المستأجر من الدومين
- `app/Http/Middleware/CentralMiddleware.php` - التحقق من الدومين المركزي

### 2. Models
- `app/Models/Tenant.php` - نموذج المستأجر
- `Stancl\Tenancy\Database\Models\Domain` - نموذج الدومين

### 3. Helpers
- `app/Helpers/SubdomainHelper.php` - مساعدات الدومين والكاش

### 4. Controllers
- `app/Http/Controllers/TenantController.php` - إدارة المستأجرين

## التحقق من الاتصال

### 1. من خلال الواجهة

**زر "معلومات قاعدة البيانات":**
- يعرض معلومات جميع قواعد البيانات
- يوضح حالة كل اتصال
- يظهر الأخطاء إن وجدت

**زر "فحص قاعدة البيانات" (لكل مستأجر):**
- يفحص قاعدة بيانات مستأجر محدد
- يعرض تفاصيل الاتصال
- يعد الجداول الموجودة

### 2. من خلال Terminal

```bash
# قائمة المستأجرين
php artisan tenants:list

# تشغيل المايجريشن لمستأجر محدد
php artisan tenants:migrate --tenants=tenant_id

# تشغيل أمر لمستأجر محدد
php artisan tenants:run --tenants=tenant_id -- migrate:status
```

## استكشاف الأخطاء

### 1. مشاكل الاتصال

**الخطأ:** `Connection refused`
**الحل:** تأكد من تشغيل MySQL

**الخطأ:** `Database does not exist`
**الحل:** تشغيل المايجريشن للمستأجر

### 2. مشاكل التينانسي

**الخطأ:** `Tenant not found`
**الحل:** تأكد من وجود الدومين في قاعدة البيانات المركزية

**الخطأ:** `Cache miss`
**الحل:** مسح الكاش وإعادة المحاولة

## أفضل الممارسات

### 1. إدارة الكاش
- استخدم الكاش لتسريع البحث عن المستأجرين
- امسح الكاش عند تحديث معلومات المستأجر

### 2. النسخ الاحتياطي
- احتفظ بنسخة احتياطية من قاعدة البيانات المركزية
- احتفظ بنسخ احتياطية من قواعد بيانات المستأجرين المهمة

### 3. المراقبة
- راقب استخدام قاعدة البيانات لكل مستأجر
- راقب أداء الاستعلامات

## الخلاصة

النظام يعمل بشكل صحيح عندما:
✅ كل مستأجر له قاعدة بيانات منفصلة
✅ التبديل بين قواعد البيانات يتم تلقائياً
✅ الكاش يعمل بشكل صحيح
✅ المايجريشن تعمل لكل مستأجر

استخدم أدوات التحقق المدمجة للتأكد من أن كل شيء يعمل بشكل صحيح!
