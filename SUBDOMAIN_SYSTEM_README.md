# نظام إدارة المستأجرين مع Sub-domains

## نظرة عامة

تم تطوير نظام إدارة المستأجرين باستخدام Laravel مع مكتبة `stancl/tenancy` لدعم الـ multi-tenancy مع الـ sub-domains. النظام يدعم:

- إدارة المستأجرين والدومينات
- نظام cache للـ sub-domains لتحسين الأداء
- واجهة إدارة شاملة باللغة العربية
- API endpoints لإدارة المستأجرين

## الميزات الجديدة

### 1. نظام Sub-domains مع Cache
- دعم كامل للـ sub-domains
- نظام cache ذكي لتحسين الأداء
- إدارة تلقائية للكاش عند التحديث

### 2. إدارة الدومينات
- إضافة دومينات متعددة لكل مستأجر
- تعديل وحذف الدومينات
- التحقق من صحة الدومينات

### 3. واجهة الإدارة
- صفحات إدارة المستأجرين باللغة العربية
- تصميم responsive باستخدام Tailwind CSS
- نماذج تفاعلية لإدارة الدومينات

## الملفات المضافة/المحدثة

### Controllers
- `app/Http/Controllers/TenantController.php` - محدث مع وظائف إدارة الدومينات
- `app/Http/Middleware/TenantMiddleware.php` - محدث لدعم الـ sub-domains مع cache

### Helpers
- `app/Helpers/SubdomainHelper.php` - جديد - مساعد لإدارة الـ sub-domains والـ cache

### Views
- `resources/views/tenants/index.blade.php` - قائمة المستأجرين
- `resources/views/tenants/show.blade.php` - تفاصيل المستأجر مع إدارة الدومينات
- `resources/views/tenants/create.blade.php` - إضافة مستأجر جديد
- `resources/views/tenants/edit.blade.php` - تعديل المستأجر
- `resources/views/layouts/app.blade.php` - Layout أساسي

### Commands
- `app/Console/Commands/ClearTenantCache.php` - أمر لمسح كاش المستأجرين

### Configuration
- `config/tenancy.php` - محدث لدعم الـ sub-domains

## كيفية الاستخدام

### 1. إعداد البيئة

تأكد من إعداد ملف `.env` بشكل صحيح:

```env
APP_URL=http://car-management.local
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=car_management
DB_USERNAME=root
DB_PASSWORD=
```

### 2. تشغيل المايجريشن

```bash
php artisan migrate
```

### 3. الوصول لواجهة الإدارة

- الوصول الرئيسي: `http://admin.car-management.local/admin/tenants`
- أو: `http://car-management.local/admin/tenants`

### 4. إدارة المستأجرين

#### إضافة مستأجر جديد:
1. اذهب إلى صفحة المستأجرين
2. اضغط على "إضافة مستأجر جديد"
3. املأ البيانات المطلوبة
4. النظام سيقوم بإنشاء قاعدة بيانات منفصلة للمستأجر تلقائياً

#### إدارة الدومينات:
1. اذهب إلى تفاصيل المستأجر
2. في قسم "إدارة الدومينات" يمكنك:
   - إضافة دومين جديد
   - تعديل دومين موجود
   - حذف دومين

### 5. استخدام الـ Sub-domains

بعد إضافة مستأجر مع دومين، يمكن الوصول إليه عبر:
- `http://subdomain.car-management.local`
- أو الدومين المخصص إذا كان دومين كامل

### 6. إدارة الكاش

#### مسح كاش مستأجر محدد:
```bash
php artisan tenants:clear-cache
```

#### مسح جميع الكاش:
```bash
php artisan tenants:clear-cache --all
```

## API Endpoints

### الحصول على مستأجر بالـ subdomain:
```
GET /api/admin/tenants/by-subdomain?subdomain=example
```

## الأمان

- جميع العمليات محمية بـ CSRF
- التحقق من صحة البيانات
- حماية من الوصول غير المصرح به

## الأداء

- نظام cache ذكي يحسن الأداء
- كاش لمدة ساعة واحدة (قابل للتعديل)
- مسح تلقائي للكاش عند التحديث

## استكشاف الأخطاء

### مشاكل الـ Sub-domains:
1. تأكد من إعداد DNS بشكل صحيح
2. تحقق من إعدادات الـ web server
3. تأكد من إضافة الدومين في `central_domains`

### مشاكل الكاش:
1. استخدم الأمر `php artisan tenants:clear-cache --all`
2. تحقق من إعدادات الـ cache في Laravel
3. تأكد من صلاحيات الكتابة في مجلد الـ cache

## التطوير المستقبلي

- إضافة نظام إشعارات
- تحسين واجهة المستخدم
- إضافة تقارير مفصلة
- دعم الـ SSL للـ sub-domains
- نظام backup تلقائي

## الدعم

للمساعدة أو الاستفسارات، يرجى التواصل مع فريق التطوير.
