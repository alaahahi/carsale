# أوامر Tenancy الشاملة - Complete Tenancy Commands

## تثبيت مكتبة Tenancy - Install Tenancy Package

```bash
# تثبيت مكتبة Stancl Tenancy
composer require stancl/tenancy

# نشر ملفات التكوين
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag="config"

# نشر ملفات Migration
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag="migrations"

# تشغيل Migration
php artisan migrate
```

## أوامر إدارة Tenants - Tenant Management Commands

### إنشاء Tenant جديد - Create New Tenant
```bash
# إنشاء tenant جديد
php artisan tenants:create tenant1

# إنشاء tenant مع معرف مخصص
php artisan tenants:create tenant1 --id=custom-id

# إنشاء tenant مع بيانات إضافية
php artisan tenants:create tenant1 --data='{"name":"Company Name","email":"admin@company.com"}'
```

### إدارة Tenants - Manage Tenants
```bash
# عرض قائمة جميع Tenants
php artisan tenants:list

# عرض تفاصيل tenant محدد
php artisan tenants:show tenant1

# حذف tenant
php artisan tenants:delete tenant1

# إعادة تعيين tenant
php artisan tenants:reset tenant1
```

## أوامر Migration للـ Tenants - Migration Commands for Tenants

### تشغيل Migration على جميع Tenants
```bash
# تشغيل migration على جميع tenants
php artisan tenants:migrate

# تشغيل migration مع rollback
php artisan tenants:migrate --rollback

# تشغيل migration مع refresh
php artisan tenants:migrate --refresh

# تشغيل migration مع seed
php artisan tenants:migrate --seed

# تشغيل migration محدد
php artisan tenants:migrate --path=database/migrations/2023_01_01_create_users_table.php
```

### إدارة Migration للـ Tenants
```bash
# عرض حالة migrations لجميع tenants
php artisan tenants:migrate-status

# تشغيل migration على tenant محدد
php artisan tenants:migrate --tenants=tenant1

# تشغيل migration على عدة tenants
php artisan tenants:migrate --tenants=tenant1,tenant2,tenant3
```

## أوامر Seed للـ Tenants - Seed Commands for Tenants

### تشغيل Seed على جميع Tenants
```bash
# تشغيل seed على جميع tenants
php artisan tenants:seed

# تشغيل seed محدد
php artisan tenants:seed --class=DatabaseSeeder

# تشغيل seed على tenant محدد
php artisan tenants:seed --tenants=tenant1

# تشغيل seed مع إعادة تعيين البيانات
php artisan tenants:seed --fresh
```

## أوامر Artisan للـ Tenants - Artisan Commands for Tenants

### تشغيل أوامر Artisan على Tenants
```bash
# تشغيل أمر artisan على جميع tenants
php artisan tenants:artisan "command:name"

# تشغيل أمر artisan على tenant محدد
php artisan tenants:artisan "command:name" --tenants=tenant1

# تشغيل أمر artisan مع معاملات
php artisan tenants:artisan "make:model User" --tenants=tenant1

# تشغيل أمر artisan مع تفاعل
php artisan tenants:artisan "tinker" --tenants=tenant1 --interactive
```

## أوامر Cache للـ Tenants - Cache Commands for Tenants

### إدارة Cache للـ Tenants
```bash
# مسح cache لجميع tenants
php artisan tenants:cache-clear

# مسح cache لـ tenant محدد
php artisan tenants:cache-clear --tenants=tenant1

# إعادة بناء cache لجميع tenants
php artisan tenants:cache-rebuild

# إعادة بناء cache لـ tenant محدد
php artisan tenants:cache-rebuild --tenants=tenant1
```

## أوامر Config للـ Tenants - Config Commands for Tenants

### إدارة Config للـ Tenants
```bash
# عرض config لجميع tenants
php artisan tenants:config-cache

# مسح config cache لجميع tenants
php artisan tenants:config-clear

# إعادة بناء config cache لجميع tenants
php artisan tenants:config-rebuild
```

## أوامر Route للـ Tenants - Route Commands for Tenants

### إدارة Routes للـ Tenants
```bash
# عرض routes لجميع tenants
php artisan tenants:route-list

# عرض routes لـ tenant محدد
php artisan tenants:route-list --tenants=tenant1

# إعادة بناء route cache لجميع tenants
php artisan tenants:route-cache

# مسح route cache لجميع tenants
php artisan tenants:route-clear
```

## أوامر View للـ Tenants - View Commands for Tenants

### إدارة Views للـ Tenants
```bash
# إعادة بناء view cache لجميع tenants
php artisan tenants:view-cache

# مسح view cache لجميع tenants
php artisan tenants:view-clear

# إعادة بناء view cache لـ tenant محدد
php artisan tenants:view-cache --tenants=tenant1
```

## أوامر Database للـ Tenants - Database Commands for Tenants

### إدارة Database للـ Tenants
```bash
# إنشاء database لجميع tenants
php artisan tenants:db-create

# حذف database لجميع tenants
php artisan tenants:db-drop

# إعادة تعيين database لجميع tenants
php artisan tenants:db-reset

# نسخ database لجميع tenants
php artisan tenants:db-backup
```

## أوامر Storage للـ Tenants - Storage Commands for Tenants

### إدارة Storage للـ Tenants
```bash
# إنشاء storage links لجميع tenants
php artisan tenants:storage-link

# مسح storage لجميع tenants
php artisan tenants:storage-clear

# نسخ storage لجميع tenants
php artisan tenants:storage-backup
```

## أوامر Custom للـ Tenants - Custom Commands for Tenants

### إنشاء أوامر مخصصة للـ Tenants
```bash
# إنشاء أمر مخصص للـ tenants
php artisan make:command TenantCustomCommand

# تشغيل أمر مخصص على جميع tenants
php artisan tenants:custom "custom:command"

# تشغيل أمر مخصص على tenant محدد
php artisan tenants:custom "custom:command" --tenants=tenant1
```

## أوامر Monitoring للـ Tenants - Monitoring Commands for Tenants

### مراقبة Tenants
```bash
# عرض إحصائيات tenants
php artisan tenants:stats

# عرض معلومات tenant محدد
php artisan tenants:info tenant1

# فحص صحة tenants
php artisan tenants:health-check

# عرض logs لجميع tenants
php artisan tenants:logs
```

## أوامر Backup للـ Tenants - Backup Commands for Tenants

### نسخ احتياطي للـ Tenants
```bash
# نسخ احتياطي لجميع tenants
php artisan tenants:backup

# نسخ احتياطي لـ tenant محدد
php artisan tenants:backup --tenants=tenant1

# استعادة من نسخة احتياطية
php artisan tenants:restore backup-file.zip

# عرض النسخ الاحتياطية المتاحة
php artisan tenants:backup-list
```

## أوامر Maintenance للـ Tenants - Maintenance Commands for Tenants

### صيانة Tenants
```bash
# وضع الصيانة لجميع tenants
php artisan tenants:maintenance-on

# إيقاف وضع الصيانة لجميع tenants
php artisan tenants:maintenance-off

# وضع الصيانة لـ tenant محدد
php artisan tenants:maintenance-on --tenants=tenant1

# إيقاف وضع الصيانة لـ tenant محدد
php artisan tenants:maintenance-off --tenants=tenant1
```

## أوامر Queue للـ Tenants - Queue Commands for Tenants

### إدارة Queue للـ Tenants
```bash
# تشغيل queue worker لجميع tenants
php artisan tenants:queue-work

# تشغيل queue worker لـ tenant محدد
php artisan tenants:queue-work --tenants=tenant1

# مسح queue لجميع tenants
php artisan tenants:queue-clear

# إعادة تشغيل queue لجميع tenants
php artisan tenants:queue-restart
```

## أوامر Schedule للـ Tenants - Schedule Commands for Tenants

### إدارة Schedule للـ Tenants
```bash
# تشغيل scheduled tasks لجميع tenants
php artisan tenants:schedule-run

# تشغيل scheduled tasks لـ tenant محدد
php artisan tenants:schedule-run --tenants=tenant1

# عرض scheduled tasks لجميع tenants
php artisan tenants:schedule-list
```

## أوامر Testing للـ Tenants - Testing Commands for Tenants

### اختبار Tenants
```bash
# تشغيل tests لجميع tenants
php artisan tenants:test

# تشغيل tests لـ tenant محدد
php artisan tenants:test --tenants=tenant1

# تشغيل tests مع coverage
php artisan tenants:test --coverage
```

## أوامر Debugging للـ Tenants - Debugging Commands for Tenants

### تصحيح أخطاء Tenants
```bash
# عرض معلومات debug لجميع tenants
php artisan tenants:debug

# عرض معلومات debug لـ tenant محدد
php artisan tenants:debug --tenants=tenant1

# فحص أخطاء tenants
php artisan tenants:check-errors
```

## ملاحظات مهمة - Important Notes

1. **تأكد من إعداد Tenancy بشكل صحيح** قبل تشغيل الأوامر
2. **استخدم `--tenants`** لتشغيل الأوامر على tenant محدد
3. **استخدم `--interactive`** للأوامر التي تحتاج تفاعل
4. **احرص على عمل نسخة احتياطية** قبل تشغيل الأوامر الخطيرة
5. **اختبر الأوامر** في بيئة التطوير قبل الإنتاج

## أمثلة عملية - Practical Examples

```bash
# إنشاء tenant جديد وإعداد قاعدة البيانات
php artisan tenants:create company1 --data='{"name":"Company 1","email":"admin@company1.com"}'
php artisan tenants:migrate --tenants=company1
php artisan tenants:seed --tenants=company1

# تشغيل migration على جميع tenants
php artisan tenants:migrate

# مسح cache لجميع tenants
php artisan tenants:cache-clear

# تشغيل أمر مخصص على tenant محدد
php artisan tenants:artisan "make:model Product" --tenants=company1
```