# المكتبات الضرورية للمشروع - Required Libraries for Project

## المكتبات المثبتة حالياً - Currently Installed Libraries

### Laravel Core Libraries
```bash
# Laravel Framework الأساسي
composer require laravel/framework:^9.19
composer require laravel/sanctum:^3.0
composer require laravel/tinker:^2.7
composer require laravel/breeze:^1.14
composer require laravel/sail:^1.0.1
composer require laravel/pint:^1.0
```

### مكتبات إدارة الملفات والصور - File Management & Image Libraries
```bash
# معالجة الصور
composer require intervention/image:^2.7

# إدارة الملفات والتخزين
composer require guzzlehttp/guzzle:^7.5
composer require http-interop/http-factory-guzzle:^1.2
```

### مكتبات PDF والتقارير - PDF & Reports Libraries
```bash
# إنشاء ملفات PDF
composer require mpdf/mpdf:^8.1
composer require carlos-meneses/laravel-mpdf:^2.1
```

### مكتبات Excel والبيانات - Excel & Data Libraries
```bash
# استيراد وتصدير ملفات Excel
composer require maatwebsite/excel:^3.1
```

### مكتبات البحث والاستعلام - Search & Query Libraries
```bash
# محرك البحث Meilisearch
composer require meilisearch/meilisearch-php:^0.25.0
```

### مكتبات المحاسبة والمالية - Accounting & Financial Libraries
```bash
# نظام المحاسبة
composer require scottlaurent/accounting:^0.3.3
```

### مكتبات QR Code - QR Code Libraries
```bash
# إنشاء رموز QR
composer require simplesoftwareio/simple-qrcode:^4.2
```

### مكتبات Frontend - Frontend Libraries
```bash
# Inertia.js للتفاعل مع Vue.js
composer require inertiajs/inertia-laravel:^0.6.4
composer require tightenco/ziggy:^1.5
```

## المكتبات الإضافية المقترحة - Additional Recommended Libraries

### مكتبات الأمان والحماية - Security Libraries
```bash
# تشفير البيانات
composer require defuse/php-encryption:^2.3

# حماية من CSRF و XSS
composer require spatie/laravel-permission:^5.5

# التحقق من صحة البيانات المتقدمة
composer require spatie/laravel-validation-rules:^2.0
```

### مكتبات إدارة المستخدمين - User Management Libraries
```bash
# إدارة الأدوار والصلاحيات
composer require spatie/laravel-permission:^5.5

# إدارة الملفات الشخصية
composer require spatie/laravel-medialibrary:^10.0
```

### مكتبات الإشعارات - Notification Libraries
```bash
# إرسال الإشعارات
composer require pusher/pusher-php-server:^7.0

# إرسال الرسائل النصية
composer require twilio/sdk:^6.40
```

### مكتبات النسخ الاحتياطي - Backup Libraries
```bash
# النسخ الاحتياطي لقاعدة البيانات
composer require spatie/laravel-backup:^8.0
```

### مكتبات المراقبة والسجلات - Monitoring & Logging Libraries
```bash
# مراقبة الأداء
composer require spatie/laravel-activitylog:^4.7

# إدارة السجلات
composer require spatie/laravel-log-viewer:^1.0
```

### مكتبات API - API Libraries
```bash
# توثيق API
composer require darkaonline/l5-swagger:^8.0

# معدل الاستجابة API
composer require spatie/laravel-rate-limited-job-middleware:^1.0
```

### مكتبات التطوير والاختبار - Development & Testing Libraries
```bash
# اختبار الوحدة
composer require phpunit/phpunit:^9.5.10

# محاكاة البيانات
composer require fakerphp/faker:^1.9.1

# اختبار المتصفح
composer require laravel/dusk:^7.0
```

## مكتبات JavaScript/Frontend - JavaScript/Frontend Libraries

### مكتبات Vue.js - Vue.js Libraries
```bash
npm install vue@^3.2.36
npm install @inertiajs/inertia@^0.11.0
npm install @inertiajs/inertia-vue3@^0.6.0
npm install @inertiajs/progress@^0.2.7
```

### مكتبات UI و التصميم - UI & Design Libraries
```bash
# Tailwind CSS
npm install tailwindcss@^3.1.0
npm install @tailwindcss/forms@^0.5.2

# Alpine.js للتفاعل
npm install alpinejs@^3.4.2
```

### مكتبات الخرائط والموقع - Maps & Location Libraries
```bash
# خرائط Google
npm install @fawmi/vue-google-maps@^0.9.79
```

### مكتبات المكونات - Component Libraries
```bash
# النوافذ المنبثقة
npm install vue-final-modal@^3.4.11

# البحث والاختيار
npm install vue-search-select@^3.2.0

# التقويم
npm install vue-tailwind-datepicker@^1.2.6

# التصوير
npm install vue-camera-lib@^1.0.4
```

### مكتبات التطوير - Development Libraries
```bash
# Vite للبناء
npm install vite@^3.0.0
npm install @vitejs/plugin-vue@^3.0.0
npm install laravel-vite-plugin@^0.6.0

# PostCSS
npm install postcss@^8.4.6
npm install autoprefixer@^10.4.2
```

## مكتبات إضافية مفيدة - Additional Useful Libraries

### مكتبات إدارة المهام - Task Management Libraries
```bash
# إدارة المهام المجدولة
composer require spatie/laravel-cronless-schedule:^1.0

# معالجة المهام في الخلفية
composer require spatie/laravel-queue-batch:^1.0
```

### مكتبات التحليل والإحصائيات - Analytics & Statistics Libraries
```bash
# تحليل البيانات
composer require spatie/laravel-analytics:^4.0

# إحصائيات الموقع
composer require spatie/laravel-google-analytics:^3.0
```

## ملاحظات مهمة - Important Notes

1. **تأكد من تحديث PHP**: يجب أن يكون إصدار PHP 8.0.2 أو أحدث
2. **تحديث Composer**: تأكد من تحديث Composer إلى أحدث إصدار
3. **إعداد البيئة**: تأكد من إعداد ملف `.env` بشكل صحيح
4. **النسخ الاحتياطي**: قم بعمل نسخة احتياطية قبل تثبيت المكتبات الجديدة
5. **الاختبار**: اختبر المشروع بعد تثبيت كل مكتبة جديدة

## أوامر التثبيت - Installation Commands

```bash
# تثبيت المكتبات الأساسية
composer install

# تثبيت مكتبات التطوير
composer install --dev

# تثبيت مكتبات JavaScript
npm install

# بناء الأصول
npm run build

# تشغيل المشروع
php artisan serve
```

## المكتبات المطلوبة للميزات المحددة - Libraries Required for Specific Features

### نظام إدارة السيارات - Car Management System
- `intervention/image` - معالجة صور السيارات
- `maatwebsite/excel` - استيراد بيانات السيارات
- `mpdf/mpdf` - تقارير السيارات

### نظام المحاسبة - Accounting System
- `scottlaurent/accounting` - إدارة الحسابات المالية
- `spatie/laravel-permission` - إدارة الصلاحيات المالية

### نظام إدارة المستخدمين - User Management System
- `laravel/sanctum` - توثيق المستخدمين
- `spatie/laravel-permission` - إدارة الأدوار والصلاحيات

### نظام الملفات والرفع - File Upload System
- `intervention/image` - معالجة الصور
- `guzzlehttp/guzzle` - رفع الملفات الخارجية