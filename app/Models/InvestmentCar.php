<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'car_id',
        'invested_amount',
        'percentage',
        'profit_share',
    ];

    protected $casts = [
        'invested_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'profit_share' => 'decimal:2',
    ];

    // العلاقة مع الاستثمار
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    // العلاقة مع السيارة
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // حساب النسبة المئوية للاستثمار في هذه السيارة
    public function calculatePercentage($carTotalCost)
    {
        if ($carTotalCost > 0) {
            // حساب النسبة الفعلية للاستثمار
            $actualPercentage = ($this->invested_amount / $carTotalCost) * 100;
            
            // تطبيق النسبة الافتراضية 50% لجميع العملاء
            $this->percentage = 50.0; // نسبة ثابتة 50% لجميع العملاء
            $this->save();
            
            \Log::info('Percentage calculated with default 50%', [
                'investment_car_id' => $this->id,
                'invested_amount' => $this->invested_amount,
                'car_total_cost' => $carTotalCost,
                'actual_percentage' => $actualPercentage,
                'default_percentage' => 50.0
            ]);
        } else {
            $this->percentage = 0;
            $this->save();
        }
    }

    // حساب نصيب الربح من هذه السيارة
    public function calculateProfitShare($carProfit)
    {
        $percentage = $this->percentage ?? 0;
        $this->profit_share = ($percentage / 100) * $carProfit;
        $this->save();
        return $this->profit_share;
    }

    // الحصول على إجمالي الاستثمارات في سيارة محددة
    public static function getTotalInvestmentForCar($carId)
    {
        return self::where('car_id', $carId)->sum('invested_amount');
    }

    // الحصول على جميع المستثمرين في سيارة محددة
    public static function getInvestorsForCar($carId)
    {
        return self::where('car_id', $carId)
            ->with(['investment.user', 'car'])
            ->get();
    }

    // التحقق من إمكانية الاستثمار في السيارة
    public static function canInvestInCar($carId, $amount)
    {
        $car = Car::find($carId);
        if (!$car) return false;

        $carTotalCost = $car->purchase_price + 
                       ($car->erbil_exp ?? 0) + 
                       ($car->erbil_shipping ?? 0) + 
                       ($car->dubai_exp ?? 0) + 
                       ($car->dubai_shipping ?? 0);

        $existingInvestments = self::getTotalInvestmentForCar($carId);
        $availableForInvestment = $carTotalCost - $existingInvestments;

        return $amount <= $availableForInvestment;
    }

    // الحصول على السيارات المتاحة للاستثمار
    public static function getAvailableCarsForInvestment()
    {
        return Car::where('results', 0) // السيارات في المخزن فقط
            ->whereDoesntHave('investmentCars', function($query) {
                $query->whereHas('investment', function($q) {
                    $q->where('status', 'active');
                });
            })
            ->orWhereHas('investmentCars', function($query) {
                $query->whereHas('investment', function($q) {
                    $q->where('status', 'active');
                });
            })
            ->with(['investmentCars.investment'])
            ->get()
            ->filter(function($car) {
                $carTotalCost = $car->purchase_price + 
                               ($car->erbil_exp ?? 0) + 
                               ($car->erbil_shipping ?? 0) + 
                               ($car->dubai_exp ?? 0) + 
                               ($car->dubai_shipping ?? 0);

                $existingInvestments = self::getTotalInvestmentForCar($car->id);
                return $existingInvestments < $carTotalCost;
            });
    }
}