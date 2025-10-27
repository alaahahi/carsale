<?php
namespace App\Imports;

use App\Models\Car;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\Company;
use App\Models\Name;
use App\Models\CarModel;
use App\Models\Color;
use App\Helpers\TenantDataHelper;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarDataImport implements ToCollection, WithValidation
{
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;

    public function __construct(){
        $this->url = env('FRONTEND_URL');
        // الحصول على نوع المستخدم من قاعدة بيانات الـ tenant
        $this->userClient = TenantDataHelper::getUserTypeId('client');
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                // تخطي الصف الأول (العناوين)
                if ($index == 0) {
                    continue;
                }

                // التحقق من وجود البيانات الأساسية
                if (!$this->validateRow($row)) {
                    $this->skipCount++;
                    continue;
                }

                // استخراج القيم من الصف
                $no = $this->cleanValue($row[0]);
                $pin = $this->cleanValue($row[1]);
                $name = $this->cleanValue($row[10]) . ' ' . $this->cleanValue($row[2]);
                $color = $this->cleanValue($row[3]);
                $model = $this->cleanValue($row[4]);
                $purchasePrice = $this->cleanDecimal($row[5]);
                $dubaiShipping = $this->cleanDecimal($row[6]);
                $dubaiExp = $this->cleanDecimal($row[7]);
                $erbilShipping = $this->cleanDecimal($row[8]);
                $erbilExp = $this->cleanDecimal($row[9]);
                $source = $this->cleanValue($row[11]) ?: 'Nejoum';

                // التحقق من صحة البيانات
                if (!$pin) {
                    $this->errors[] = [
                        'row' => $index + 1,
                        'message' => 'رقم PIN مطلوب'
                    ];
                    continue;
                }

                // محاولة إنشاء أو تحديث السيارة
                try {
                    Car::updateOrCreate(
                        ['pin' => $pin],
                        [
                            'no' => $no ?: null,
                            'name' => $name ?: null,
                            'model' => $model ?: null,
                            'color' => $color ?: null,
                            'source' => $source,
                            'purchase_price' => $purchasePrice,
                            'dubai_shipping' => $dubaiShipping,
                            'dubai_exp' => $dubaiExp,
                            'erbil_shipping' => $erbilShipping,
                            'erbil_exp' => $erbilExp,
                            'user_id' => auth()->id() ?? 60,
                            'results' => 0, // تحديد أن السيارة متاحة
                        ]
                    );
                    
                    $this->successCount++;
                    
                    // تسجيل العملية
                    Log::info('Car imported successfully', [
                        'pin' => $pin,
                        'name' => $name,
                        'row' => $index + 1
                    ]);
                    
                } catch (\Exception $e) {
                    $this->errors[] = [
                        'row' => $index + 1,
                        'message' => 'خطأ في إدخال البيانات: ' . $e->getMessage(),
                        'pin' => $pin
                    ];
                    
                    Log::error('Error importing car', [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'pin' => $pin
                    ]);
                }
            }

            // إذا كانت هناك أخطاء كثيرة، عمل Rollback
            if (count($this->errors) > $this->successCount) {
                DB::rollBack();
                throw new \Exception('فشل الاستيراد: عدد الأخطاء كبير جداً');
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import transaction failed', [
                'error' => $e->getMessage(),
                'total_rows' => $rows->count(),
                'success_count' => $this->successCount,
                'error_count' => count($this->errors)
            ]);
            throw $e;
        }
    }

    private function validateRow($row)
    {
        // التحقق من وجود رقم PIN (العمود الثاني)
        return isset($row[1]) && !empty($row[1]);
    }

    private function cleanValue($value)
    {
        if ($value === null) {
            return null;
        }
        
        // تنظيف القيمة من المسافات الزائدة
        $cleaned = trim($value);
        
        // إرجاع null للقيم الفارغة
        return $cleaned === '' ? null : $cleaned;
    }

    private function cleanDecimal($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        
        // إزالة أي رموز غير رقمية
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        return (float) $cleaned;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkipCount()
    {
        return $this->skipCount;
    }

    public function rules(): array
    {
        return [
            '0' => 'nullable|string',
            '1' => 'required|string', // PIN مطلوب
            '2' => 'nullable|string',
            '3' => 'nullable|string',
            '4' => 'nullable|string',
            '5' => 'nullable|numeric',
            '6' => 'nullable|numeric',
            '7' => 'nullable|numeric',
            '8' => 'nullable|numeric',
            '9' => 'nullable|numeric',
            '10' => 'nullable|string',
            '11' => 'nullable|string',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '1.required' => 'رقم PIN مطلوب',
            '1.string' => 'رقم PIN يجب أن يكون نصاً',
            '5.numeric' => 'سعر الشراء يجب أن يكون رقماً',
            '6.numeric' => 'تكلفة شحن دبي يجب أن تكون رقماً',
            '7.numeric' => 'مصروفات دبي يجب أن تكون رقماً',
            '8.numeric' => 'تكلفة شحن أربيل يجب أن تكون رقماً',
            '9.numeric' => 'مصروفات أربيل يجب أن تكون رقماً',
        ];
    }
}
