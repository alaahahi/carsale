# توثيق: صفحة معلومات التاجر من المشروع الثاني

## نظرة عامة

تم إضافة صفحة لعرض معلومات التاجر من المشروع الثاني، حيث يتم جلب البيانات من API خارجي وعرضها بشكل جذاب. الصفحة تدعم عرض أكثر من تاجر واحد من خلال معرفات مخزنة في `system_config`.

---

## الملفات المعدلة

### 1. Models

#### `app/Models/SystemConfig.php`
- **التعديل**: إضافة حقل `external_merchant_ids` إلى `$fillable`
- **الوصف**: يسمح بتخزين معرفات التجار الخارجيين في قاعدة البيانات

```php
protected $fillable = [
    // ... الحقول الأخرى
    'external_merchant_ids',
];
```

---

### 2. Helpers

#### `app/Helpers/TenantDataHelper.php`
- **التعديل**: تحديث دالة `getSystemConfig()` لدعم قراءة `external_merchant_ids`
- **الميزات**:
  - دعم JSON format: `[1, 2, 3]`
  - دعم نص مفصول بفواصل: `"1,2,3"`
  - تنظيف وتحويل القيم إلى مصفوفة من الأعداد الصحيحة

```php
// معالجة معرفات التجار الخارجيين
$merchantIds = [];
if ($config->external_merchant_ids) {
    // محاولة تحليل كـ JSON أولاً
    $decoded = json_decode($config->external_merchant_ids, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $merchantIds = $decoded;
    } else {
        // إذا لم يكن JSON، معالجة كـ نص مفصول بفواصل
        $merchantIds = array_filter(
            array_map('trim', explode(',', $config->external_merchant_ids)),
            function($id) {
                return !empty($id) && is_numeric($id);
            }
        );
        $merchantIds = array_map('intval', $merchantIds);
    }
}
```

---

### 3. Controllers

#### `app/Http/Controllers/UserController.php`

##### أ. دالة `externalMerchant()`
- **الوصف**: عرض صفحة معلومات التاجر
- **الميزات**:
  - قراءة معرفات التجار من `system_config`
  - دعم اختيار تاجر محدد عبر query parameter
  - استخدام أول تاجر كافتراضي إذا لم يتم التحديد

```php
public function externalMerchant(Request $request)
{
    $systemConfig = TenantDataHelper::getSystemConfig();
    $merchantIds = $systemConfig['external_merchant_ids'] ?? [];
    $selectedMerchantId = $request->get('merchant_id');
    
    // إذا كان هناك تاجر محدد في الطلب، استخدمه
    if ($selectedMerchantId && in_array((int)$selectedMerchantId, $merchantIds)) {
        $currentMerchantId = (int)$selectedMerchantId;
    } elseif (!empty($merchantIds)) {
        $currentMerchantId = $merchantIds[0];
    } else {
        $currentMerchantId = null;
    }
    
    return Inertia::render('Clients/ExternalMerchant', [
        'url' => $this->url,
        'systemConfig' => $systemConfig,
        'merchantIds' => $merchantIds,
        'currentMerchantId' => $currentMerchantId
    ]);
}
```

##### ب. دالة `getExternalSales()`
- **الوصف**: جلب بيانات المبيعات والدفعات من API المشروع الثاني
- **الميزات**:
  - قراءة معرف التاجر من `system_config` أو query parameter
  - جلب بيانات المبيعات من `/api/external/getSales`
  - جلب بيانات الدفعات من `/api/external/getPayments`
  - معالجة الأخطاء بشكل آمن

```php
public function getExternalSales(Request $request)
{
    try {
        $systemConfig = TenantDataHelper::getSystemConfig();
        $merchantIds = $systemConfig['external_merchant_ids'] ?? [];
        
        // الحصول على معرف التاجر من الطلب أو من system_config
        $clientId = $request->get('id');
        if (!$clientId && !empty($merchantIds)) {
            $clientId = $merchantIds[0]; // استخدام الأول كافتراضي
        }
        
        $secondProjectUrl = env('SECOND_PROJECT_URL');
        $apiKey = env('EXTERNAL_API_KEY');
        
        // جلب بيانات المبيعات
        $salesResponse = Http::timeout(5)
            ->withHeaders(['X-API-Key' => $apiKey])
            ->get("{$secondProjectUrl}/api/external/getSales", ['id' => $clientId]);
        
        // جلب بيانات الدفعات
        $paymentsResponse = Http::timeout(5)
            ->withHeaders(['X-API-Key' => $apiKey])
            ->get("{$secondProjectUrl}/api/external/getPayments", [
                'client_id' => $clientId,
                'from' => $from,
                'to' => $to
            ]);
        
        return Response::json([
            'success' => true,
            'sales' => $salesData,
            'payments' => $paymentsData
        ], 200);
    } catch (\Exception $e) {
        // معالجة الأخطاء
    }
}
```

#### `app/Http/Controllers/DashboardController.php`
- **التعديل**: إضافة `externalMerchantIds` إلى البيانات المرسلة للـ Dashboard
- **الوصف**: يسمح بعرض أزرار التجار في Dashboard

```php
$systemConfig = TenantDataHelper::getSystemConfig();
$externalMerchantIds = $systemConfig['external_merchant_ids'] ?? [];

return Inertia::render('Dashboard', [
    // ... البيانات الأخرى
    'externalMerchantIds' => $externalMerchantIds
]);
```

---

### 4. Routes

#### `routes/web.php`
- **إضافة**: Route جديد لعرض صفحة معلومات التاجر

```php
Route::get('external-merchant',[UserController::class, 'externalMerchant'])->name('external.merchant');
```

#### `routes/api.php`
- **إضافة**: Route API لجلب بيانات المبيعات

```php
Route::get('external-merchant/sales', [UserController::class, 'getExternalSales'])->name('external.merchant.sales');
```

---

### 5. Vue Components

#### `resources/js/Pages/Clients/ExternalMerchant.vue`
- **الوصف**: صفحة Vue لعرض معلومات التاجر
- **الميزات**:
  - عرض معلومات التاجر (الاسم، الرصيد)
  - بطاقات إحصائية (إجمالي السيارات، المبيعات، المدفوع، المطلوب)
  - تبويبات لعرض السيارات والدفعات
  - قائمة اختيار للتجار عند وجود أكثر من تاجر
  - فلترة الدفعات بالتاريخ

**المكونات الرئيسية**:
- Header مع زر الرجوع
- Merchant Selector (عند وجود أكثر من تاجر)
- Client Info Card (بطاقة معلومات التاجر)
- Summary Cards (بطاقات الإحصائيات)
- Tabs (السيارات والدفعات)
- Tables (جداول البيانات)

**Props**:
```javascript
const props = defineProps({
    systemConfig: Object,
    merchantIds: Array,
    currentMerchantId: Number
})
```

**الدوال الرئيسية**:
- `loadData()`: جلب البيانات من API
- `onMerchantChange()`: تغيير التاجر المحدد
- `getClientName()`: الحصول على اسم التاجر
- `getWalletBalance()`: الحصول على رصيد المحفظة
- `formatNumber()`, `formatCurrency()`, `formatDate()`: دوال التنسيق

#### `resources/js/Pages/Clients/Index.vue`
- **التعديل**: إضافة زر للانتقال إلى صفحة معلومات التاجر

```vue
<button @click="goToExternalMerchant" 
        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
    </svg>
    <span>معلومات التاجر (المشروع الثاني)</span>
</button>
```

#### `resources/js/Pages/Dashboard.vue`
- **التعديل**: إضافة أزرار لكل معرف تاجر في Dashboard
- **الميزات**:
  - عرض زر لكل معرف تاجر من `externalMerchantIds`
  - كل زر يوجه إلى صفحة معلومات التاجر المحدد

```vue
<!-- External Merchant Buttons -->
<div v-if="externalMerchantIds && externalMerchantIds.length > 0" class="flex flex-wrap gap-2 mb-4">
  <a
    v-for="merchantId in externalMerchantIds"
    :key="merchantId"
    :href="`/external-merchant?merchant_id=${merchantId}`"
    style="min-width:150px;"
    className="px-6 mb-12 mx-2 text-center py-2 font-bold text-white bg-purple-600 hover:bg-purple-700 rounded transition-colors duration-200">
    <div class="flex items-center justify-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
      </svg>
      <span>التاجر #{{ merchantId }}</span>
    </div>
  </a>
</div>
```

---

## متغيرات البيئة المطلوبة

يجب إضافة هذه المتغيرات في ملف `.env`:

```env
SECOND_PROJECT_URL=https://project2.example.com
EXTERNAL_API_KEY=your-secret-api-key-here
```

**ملاحظة**: `EXTERNAL_CLIENT_ID` لم يعد مطلوباً في `.env` لأنه يتم قراءته من `system_config`.

---

## إعداد قاعدة البيانات

### إضافة معرفات التجار في `system_config`

يمكن إضافة معرفات التجار بطريقتين:

#### الطريقة 1: JSON Format
```sql
UPDATE system_config 
SET external_merchant_ids = '[1,2,3]' 
WHERE id = 1;
```

#### الطريقة 2: نص مفصول بفواصل
```sql
UPDATE system_config 
SET external_merchant_ids = '1,2,3' 
WHERE id = 1;
```

### إضافة عمود جديد (إذا لم يكن موجوداً)

```sql
ALTER TABLE system_config 
ADD COLUMN external_merchant_ids TEXT NULL;
```

---

## API Endpoints المطلوبة في المشروع الثاني

### 1. API للمبيعات
```
GET /api/external/getSales?id=1
Headers:
  X-API-Key: your-secret-api-key-here
```

**الاستجابة**:
```json
{
  "success": true,
  "cars": [
    {
      "id": 1,
      "vin": "123456789",
      "car_type": "تويوتا",
      "year": 2024,
      "car_color": "أبيض",
      "date": "2024-01-15",
      "total_s": 5000,
      "total": 4000,
      "paid": 3000,
      "discount": 100,
      "profit": 1000,
      "results": 1,
      "note": "ملاحظة",
      "client": {
        "id": 1,
        "name": "اسم التاجر"
      },
      "has_contract": true,
      "is_exit": 0
    }
  ],
  "summary": {
    "car_total": 10,
    "car_total_unpaid": 2,
    "car_total_uncomplete": 5,
    "car_total_complete": 3,
    "cars_sum": 50000,
    "cars_paid": 30000,
    "cars_discount": 1000,
    "cars_need_paid": 19000
  }
}
```

### 2. API للدفعات
```
GET /api/external/getPayments?client_id=1&from=2024-01-01&to=2024-12-31
Headers:
  X-API-Key: your-secret-api-key-here
```

**الاستجابة**:
```json
{
  "success": true,
  "client": {
    "id": 1,
    "name": "اسم التاجر",
    "wallet_balance": -5000
  },
  "payments": [
    {
      "id": 123,
      "amount": 1000,
      "currency": "$",
      "description": "دفعة للسيارة رقم 1",
      "date": "2024-01-20",
      "type": "out"
    }
  ],
  "summary": {
    "total_payments_dollar": 10000,
    "total_payments_dinar": 0,
    "count": 5
  }
}
```

---

## كيفية الاستخدام

### 1. الوصول من صفحة العملاء
- انتقل إلى صفحة "العملاء"
- اضغط على زر "معلومات التاجر (المشروع الثاني)"
- سيتم فتح صفحة معلومات التاجر

### 2. الوصول من Dashboard
- في صفحة Dashboard، ستظهر أزرار لكل معرف تاجر
- اضغط على زر "التاجر #X" للانتقال إلى صفحة معلومات التاجر المحدد

### 3. الوصول المباشر
- يمكن الوصول مباشرة عبر: `/external-merchant?merchant_id=1`
- إذا لم يتم تحديد `merchant_id`، سيتم استخدام أول معرف من القائمة

---

## الميزات

### 1. عرض معلومات التاجر
- اسم التاجر
- رقم التاجر
- رصيد المحفظة (مع تمييز الألوان)

### 2. بطاقات الإحصائيات
- إجمالي السيارات
- إجمالي المبيعات
- المدفوع
- المطلوب

### 3. تبويب السيارات
- جدول بجميع السيارات
- عرض: VIN، النوع، السنة، اللون، التاريخ، الإجمالي، المدفوع، المتبقي، الربح

### 4. تبويب الدفعات
- جدول بجميع الدفعات
- فلترة بالتاريخ (من - إلى)
- عرض: المبلغ، العملة، الوصف، التاريخ، النوع (وارد/صادر)
- ملخص الدفعات (إجمالي بالدولار، إجمالي بالدينار، العدد)

### 5. دعم عدة تجار
- قائمة اختيار عند وجود أكثر من تاجر
- حفظ أسماء التجار تلقائياً بعد جلب البيانات
- تحديث URL عند تغيير التاجر

---

## معالجة الأخطاء

### 1. عدم وجود معرفات تجار
- إذا لم تكن هناك معرفات في `system_config`، ستظهر رسالة خطأ واضحة

### 2. فشل الاتصال بـ API
- معالجة آمنة للأخطاء
- عرض رسائل خطأ واضحة للمستخدم
- تسجيل الأخطاء في Laravel Log

### 3. بيانات غير مكتملة
- استخدام قيم افتراضية عند عدم توفر البيانات
- عرض "غير معروف" أو "غير محدد" للقيم المفقودة

---

## الأمان

- جميع الـ APIs الخارجية محمية بـ API Key
- يتم إرسال `X-API-Key` في الـ Headers
- الـ API Key يجب أن يكون نفس القيمة في المشروعين
- الـ APIs للعرض فقط، لا تسمح بالتعديل

---

## ملاحظات مهمة

1. **API Key**: يجب تغيير `EXTERNAL_API_KEY` إلى قيمة آمنة في الإنتاج
2. **URL**: تأكد من أن `SECOND_PROJECT_URL` هو الرابط الكامل (مع https://)
3. **Timeout**: يتم تعيين timeout 5 ثوانٍ للاتصال بالمشروع الثاني
4. **الأخطاء**: إذا فشل الاتصال بالمشروع الثاني، سيتم عرض رسالة خطأ واضحة
5. **الكاش**: يتم استخدام كاش Laravel لتخزين إعدادات النظام، قد تحتاج لمسح الكاش بعد التعديل

---

## الاختبار

### اختبار API للمبيعات
```bash
curl -H "X-API-Key: your-secret-api-key-here" \
  "https://project2.example.com/api/external/getSales?id=1"
```

### اختبار API للدفعات
```bash
curl -H "X-API-Key: your-secret-api-key-here" \
  "https://project2.example.com/api/external/getPayments?client_id=1&from=2024-01-01&to=2024-12-31"
```

---

## التطويرات المستقبلية المحتملة

1. إضافة تصدير البيانات إلى Excel/PDF
2. إضافة رسوم بيانية للإحصائيات
3. إضافة بحث وفلترة متقدمة
4. إضافة تحديث تلقائي للبيانات
5. إضافة إشعارات عند تغيير البيانات

---

## الدعم

في حالة وجود أي مشاكل أو استفسارات، يرجى مراجعة:
- ملفات Log في `storage/logs/laravel.log`
- إعدادات `system_config` في قاعدة البيانات
- متغيرات البيئة في ملف `.env`

---

**تاريخ التحديث**: 2024
**الإصدار**: 1.0

