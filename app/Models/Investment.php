<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'note',
        'status',
        'investment_type', // 'specific_cars' only
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع سيارات الاستثمار
    public function investmentCars()
    {
        return $this->hasMany(InvestmentCar::class);
    }

    // العلاقة مع السيارات من خلال investmentCars
    public function cars()
    {
        return $this->hasManyThrough(Car::class, InvestmentCar::class, 'investment_id', 'id', 'id', 'car_id');
    }

    // حساب النسبة المئوية للاستثمار (من investment_cars)
    public function calculatePercentage($totalCapital)
    {
        // هذه الدالة لم تعد مستخدمة لأن النسب محسوبة في investment_cars
        return 0;
    }

    // حساب نصيب المستثمر من الربح (من investment_cars)
    public function calculateProfitShare($totalProfit)
    {
        // هذه الدالة لم تعد مستخدمة لأن الأرباح محسوبة في investment_cars
        return 0;
    }

    // الحصول على إجمالي الاستثمارات النشطة
    public static function getTotalActiveInvestments()
    {
        return self::where('status', 'active')->sum('amount');
    }

    // الحصول على جميع المستثمرين النشطين
    public static function getActiveInvestors()
    {
        return self::where('status', 'active')
            ->with('user')
            ->orderBy('amount', 'desc')
            ->get();
    }

    // حساب الربح عند بيع السيارة
    public function calculateProfitOnCarSale($carId)
    {
        $investmentCar = $this->investmentCars()->where('car_id', $carId)->first();
        if (!$investmentCar) {
            return 0;
        }

        $car = Car::find($carId);
        if (!$car || $car->results == 0) {
            return 0; // السيارة لم تباع بعد
        }

        $carProfit = $car->profit; // ربح السيارة الإجمالي
        $investmentPercentage = $investmentCar->percentage;
        
        $profitShare = ($investmentPercentage / 100) * $carProfit;
        
        // تحديث نصيب الربح في investment_cars
        $investmentCar->update(['profit_share' => $profitShare]);
        
        return $profitShare;
    }

    // الحصول على إجمالي الربح من جميع السيارات المستثمرة
    public function getTotalProfitFromCars()
    {
        return $this->investmentCars()
            ->whereHas('car', function($query) {
                $query->where('results', '!=', 0); // السيارات المباعة فقط
            })
            ->sum('profit_share');
    }

    // الحصول على تفاصيل الربح لكل سيارة
    public function getCarProfitDetails()
    {
        return $this->investmentCars()
            ->with('car')
            ->get()
            ->map(function($investmentCar) {
                $car = $investmentCar->car;
                return [
                    'car_id' => $car->id,
                    'car_no' => $car->no,
                    'car_name' => $car->name,
                    'car_pin' => $car->pin,
                    'invested_amount' => $investmentCar->invested_amount,
                    'investment_percentage' => $investmentCar->percentage,
                    'car_profit' => $car->profit,
                    'profit_share' => $investmentCar->profit_share,
                    'is_sold' => $car->results != 0,
                    'sale_price' => $car->pay_price,
                    'total_cost' => $car->total_cost
                ];
            });
    }
}
