# دليل إدارة الدومينات والمستأجرين

## ✅ تم تطبيق التغييرات المطلوبة

### 1. **تغيير البادئة:**
- **من:** `car_tenant_` 
- **إلى:** `aindubai_`
- **النتيجة:** قواعد البيانات ستكون `aindubai_1`, `aindubai_2`, etc.

### 2. **إضافة استثناء للعمل المحلي:**
- تم إضافة `aindubai.local` و `www.aindubai.local` للدومينات المركزية
- هذه الدومينات ستستخدم قاعدة البيانات المركزية وليس قاعدة بيانات مستأجر

## 🔧 كيفية ربط عدة دومينات بنفس المستأجر

### الطريقة 1: عبر صفحة إدارة المستأجرين

1. **اذهب إلى:** `http://127.0.0.1:8000/central-admin/tenants`
2. **اضغط على "عرض" للمستأجر المطلوب**
3. **اضغط على "إضافة دومين"**
4. **أدخل الدومين الجديد:** `aindubai_copart.local`
5. **اضغط "حفظ"**

### الطريقة 2: عبر الكود

```php
// إضافة دومين جديد لمستأجر موجود
$tenant = Tenant::find(1); // المستأجر الأول
Domain::create([
    'domain' => 'aindubai_copart.local',
    'tenant_id' => $tenant->id,
]);
```

### الطريقة 3: عبر قاعدة البيانات مباشرة

```sql
-- إضافة دومين جديد لمستأجر موجود
INSERT INTO domains (domain, tenant_id) 
VALUES ('aindubai_copart.local', 1);
```

## 📋 مثال عملي

### المستأجر الأول (ID: 1)
- **الدومينات:** 
  - `aindubai_alhadaf.local`
  - `aindubai_copart.local`
- **قاعدة البيانات:** `aindubai_1`

### المستأجر الثاني (ID: 2)
- **الدومينات:**
  - `aindubai_dowalyplus.local`
  - `aindubai_dph.local`
- **قاعدة البيانات:** `aindubai_2`

## 🌐 كيفية عمل النظام

### 1. **الدومينات المركزية (لا تحتاج مستأجر):**
```
aindubai.local          → قاعدة البيانات المركزية
www.aindubai.local      → قاعدة البيانات المركزية
127.0.0.1:8000         → قاعدة البيانات المركزية
```

### 2. **دومينات المستأجرين:**
```
aindubai_alhadaf.local    → قاعدة البيانات: aindubai_1
aindubai_copart.local     → قاعدة البيانات: aindubai_1 (نفس المستأجر)
aindubai_dowalyplus.local → قاعدة البيانات: aindubai_2
aindubai_dph.local        → قاعدة البيانات: aindubai_3
aindubai_tesla.local      → قاعدة البيانات: aindubai_4
aindubai_wedoo.local      → قاعدة البيانات: aindubai_5
```

## 🔍 كيفية التحقق من الإعدادات

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
```

## 🚀 خطوات إنشاء مستأجر جديد

### 1. **إنشاء المستأجر:**
- اذهب إلى: `http://127.0.0.1:8000/central-admin/tenants/create`
- أدخل البيانات المطلوبة
- اضغط "حفظ"

### 2. **إضافة دومينات إضافية:**
- اذهب إلى صفحة المستأجر
- اضغط "إضافة دومين"
- أدخل الدومين الجديد
- اضغط "حفظ"

### 3. **التحقق من النتيجة:**
- اذهب إلى: `http://127.0.0.1:8000/central-admin/tenants/database-info`
- ستجد قاعدة البيانات الجديدة باسم `aindubai_X`

## ⚠️ ملاحظات مهمة

1. **المستأجرين الموجودين:** سيحتفظون بقواعد بياناتهم الحالية
2. **المستأجرين الجدد:** سيحصلون على قواعد بيانات بأسماء `aindubai_X`
3. **الكاش:** تم مسحه تلقائياً بعد التعديل
4. **الاستثناء المحلي:** `aindubai.local` يستخدم قاعدة البيانات المركزية

## 🎯 النتيجة النهائية

- ✅ **البادئة:** `aindubai_` بدلاً من `car_tenant_`
- ✅ **عدة دومينات:** يمكن ربطها بنفس المستأجر
- ✅ **الـ Subdomain:** يحدد قاعدة البيانات تلقائياً
- ✅ **الاستثناء المحلي:** `aindubai.local` يعمل مع قاعدة البيانات المركزية

---

**ملف الإعدادات:** `config/tenancy.php`  
**صفحة الإدارة:** `http://127.0.0.1:8000/central-admin/tenants`  
**معلومات قاعدة البيانات:** `http://127.0.0.1:8000/central-admin/tenants/database-info`
