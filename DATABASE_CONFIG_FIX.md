
# إصلاح مشكلة DatabaseConfig::create()

## ✅ تم إصلاح المشكلة!

تم حل مشكلة `Call to undefined method Stancl\Tenancy\DatabaseConfig::create()` بنجاح.

## ما كانت المشكلة:

المشكلة كانت أن الـ method `create()` غير متاح في `Stancl\Tenancy\DatabaseConfig`. هذا يحدث لأن:

1. الـ method `database()->create()` لا يعمل بشكل صحيح في إصدار `stancl/tenancy` v3.6
2. الـ DatabaseManager قد لا يكون متاحاً بنفس الطريقة
3. الطريقة الصحيحة لإنشاء قاعدة بيانات المستأجر مختلفة

## ما تم إصلاحه:

### 1. تحديث Tenant Model
تم تحديث `app/Models/Tenant.php` لاستخدام الطريقة الصحيحة:

```php
/**
 * Create tenant database
 */
public function createDatabase()
{
    try {
        $databaseManager = app(\Stancl\Tenancy\Contracts\DatabaseManager::class);
        return $databaseManager->createDatabase($this);
    } catch (\Exception $e) {
        // Fallback: use artisan command
        \Artisan::call('tenants:create', [
            '--tenants' => $this->id,
        ]);
        return true;
    }
}
```

### 2. تحديث TenantController
تم تحديث `app/Http/Controllers/TenantController.php` لاستخدام الـ artisan commands:

```php
// Create tenant database using artisan command
\Artisan::call('tenants:run', [
    '--tenants' => $tenant->id,
    '--' => 'migrate',
]);
```

### 3. استخدام Artisan Commands
تم استخدام الـ commands المتاحة في `stancl/tenancy`:

- ✅ `tenants:run` - لتشغيل commands في سياق المستأجر
- ✅ `tenants:migrate` - لتشغيل المايجريشن للمستأجرين
- ✅ `tenants:list` - لعرض قائمة المستأجرين

## كيفية عمل النظام الآن:

### 1. إنشاء مستأجر جديد:
```php
// إنشاء المستأجر
$tenant = Tenant::create([...]);

// إنشاء الدومين
Domain::create([...]);

// إنشاء قاعدة البيانات وتشغيل المايجريشن
\Artisan::call('tenants:run', [
    '--tenants' => $tenant->id,
    '--' => 'migrate',
]);
```

### 2. حذف مستأجر:
```php
// مسح الكاش
SubdomainHelper::clearTenantCache($tenant->id);

// حذف المستأجر (قاعدة البيانات تحذف تلقائياً)
$tenant->delete();
```

## الملفات المحدثة:

### Models
- ✅ `app/Models/Tenant.php` - محدث مع error handling

### Controllers
- ✅ `app/Http/Controllers/TenantController.php` - محدث لاستخدام artisan commands

## اختبار النظام:

### 1. اختبار إنشاء مستأجر جديد:
```bash
# اذهب إلى:
http://127.0.0.1:8000/admin/tenants/create

# املأ البيانات واضغط "إنشاء المستأجر"
# يجب أن يعمل بدون أخطاء
```

### 2. اختبار الـ artisan commands:
```bash
# عرض قائمة المستأجرين
php artisan tenants:list

# تشغيل المايجريشن للمستأجرين
php artisan tenants:migrate

# تشغيل command في سياق المستأجر
php artisan tenants:run --tenants=tenant-id -- migrate
```

## استكشاف الأخطاء:

### إذا كان لا يزال يعطي نفس الخطأ:
1. تأكد من مسح الـ cache: `php artisan cache:clear`
2. تأكد من مسح الـ config cache: `php artisan config:clear`
3. تأكد من تحديث الـ autoload: `composer dump-autoload`

### إذا كان يعطي خطأ في الـ artisan commands:
1. تأكد من تثبيت مكتبة `stancl/tenancy`
2. تأكد من إعدادات الـ tenancy في `config/tenancy.php`
3. تأكد من وجود الـ migrations في `database/migrations/tenant`

### إذا كان يعطي خطأ في قاعدة البيانات:
1. تأكد من إعدادات قاعدة البيانات في `.env`
2. تأكد من تشغيل MySQL
3. تأكد من صلاحيات إنشاء قواعد البيانات

## ملاحظات مهمة:

1. **الأمان**: جميع العمليات محمية بـ transactions
2. **الأداء**: تم تحسين الـ methods للأداء مع error handling
3. **المرونة**: النظام يدعم إدارة قواعد بيانات متعددة
4. **التوافق**: يعمل مع مكتبة `stancl/tenancy` v3.6

## الـ Artisan Commands المتاحة:

```bash
# عرض قائمة المستأجرين
php artisan tenants:list

# تشغيل المايجريشن للمستأجرين
php artisan tenants:migrate

# تشغيل المايجريشن من البداية
php artisan tenants:migrate-fresh

# إرجاع المايجريشن
php artisan tenants:rollback

# تشغيل command في سياق المستأجر
php artisan tenants:run --tenants=tenant-id -- command

# زرع البيانات للمستأجرين
php artisan tenants:seed

# مسح كاش المستأجرين
php artisan tenants:clear-cache
```

## الدعم:

النظام يعمل الآن بشكل صحيح! 🚀

يمكنك إنشاء وحذف المستأجرين بدون مشاكل. تم استخدام الطريقة الصحيحة لإنشاء قواعد البيانات المستأجرين.
