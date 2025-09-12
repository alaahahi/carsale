# تعليمات سريعة - نظام Sub-domains

## الخطوات الأساسية

### 1. إعداد البيئة
```bash
# تشغيل المايجريشن
php artisan migrate

# تشغيل الخادم
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. إعداد الـ Hosts (Windows)
أضف هذه الأسطر إلى ملف `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 car-management.local
127.0.0.1 admin.car-management.local
127.0.0.1 www.car-management.local
127.0.0.1 app.car-management.local
```

### 3. الوصول للنظام
- **لوحة الإدارة**: `http://admin.car-management.local:8000/admin/tenants`
- **الموقع الرئيسي**: `http://car-management.local:8000`

### 4. إضافة مستأجر جديد
1. اذهب إلى لوحة الإدارة
2. اضغط "إضافة مستأجر جديد"
3. املأ البيانات:
   - الاسم: اسم الشركة
   - الدومين: subdomain.car-management.local
   - البريد الإلكتروني
   - خطة الاشتراك
4. اضغط "إنشاء المستأجر"

### 5. الوصول للمستأجر
بعد الإنشاء، يمكن الوصول للمستأجر عبر:
`http://subdomain.car-management.local:8000`

### 6. إدارة الدومينات
- في صفحة تفاصيل المستأجر
- قسم "إدارة الدومينات"
- يمكن إضافة/تعديل/حذف دومينات

### 7. مسح الكاش
```bash
# مسح كاش مستأجر محدد
php artisan tenants:clear-cache

# مسح جميع الكاش
php artisan tenants:clear-cache --all
```

## ملاحظات مهمة

1. **الكاش**: النظام يستخدم كاش لمدة ساعة، يتم مسحه تلقائياً عند التحديث
2. **قواعد البيانات**: كل مستأجر له قاعدة بيانات منفصلة
3. **الأمان**: جميع العمليات محمية
4. **الأداء**: الكاش يحسن الأداء بشكل كبير

## استكشاف الأخطاء

### مشكلة الوصول للـ subdomain:
- تحقق من إعداد الـ hosts
- تأكد من تشغيل الخادم على المنفذ الصحيح

### مشكلة الكاش:
- استخدم الأمر `php artisan tenants:clear-cache --all`
- تحقق من صلاحيات مجلد الـ cache

### مشكلة قاعدة البيانات:
- تأكد من إعدادات قاعدة البيانات في `.env`
- تحقق من تشغيل MySQL

## الملفات المهمة

- `config/tenancy.php` - إعدادات النظام
- `app/Helpers/SubdomainHelper.php` - مساعد الـ subdomains
- `app/Http/Middleware/TenantMiddleware.php` - middleware الرئيسي
- `resources/views/tenants/` - صفحات الإدارة
