# إصلاح مشكلة إنشاء المستأجر

## المشكلة
عند الضغط على "إنشاء متجر" في الرابط `http://127.0.0.1:8000/central-admin/tenants/create` كان يظهر الخطأ:
```
Not enough arguments (missing: "commandname").
```

## سبب المشكلة
كان الكود يستخدم أمر `tenants:run` بشكل خاطئ:

```php
// ❌ الكود الخاطئ
\Artisan::call('tenants:run', [
    '--tenants' => $tenant->id,
    '--' => 'migrate',
]);
```

المشكلة أن أمر `tenants:run` يحتاج إلى `commandname` كمعامل مطلوب، لكن الكود كان يحاول تمرير `migrate` كمعامل اختياري.

## الحل
تم تغيير الكود لاستخدام الأمر الصحيح `tenants:migrate`:

```php
// ✅ الكود الصحيح
\Artisan::call('tenants:migrate', [
    '--tenants' => $tenant->id,
]);
```

## الملفات المعدلة
- `app/Http/Controllers/TenantController.php` - السطر 74-76

## التحقق من الحل
1. تأكد من وجود المايجريشن في `database/migrations/tenant/`
2. تأكد من أن الأمر `php artisan tenants:migrate --help` يعمل
3. جرب إنشاء مستأجر جديد من الواجهة

## الأوامر المتاحة للمستأجرين
```bash
php artisan tenants:list           # قائمة المستأجرين
php artisan tenants:migrate        # تشغيل المايجريشن للمستأجرين
php artisan tenants:migrate-fresh  # إعادة تشغيل المايجريشن من البداية
php artisan tenants:rollback       # تراجع المايجريشن
php artisan tenants:run            # تشغيل أمر معين للمستأجرين
php artisan tenants:seed           # تشغيل الـ seeders للمستأجرين
php artisan tenants:clear-cache    # مسح الكاش
```

## النتيجة
✅ تم حل المشكلة بنجاح
✅ يمكن الآن إنشاء مستأجرين جدد بدون أخطاء
✅ المايجريشن تعمل بشكل صحيح للمستأجرين الجدد
