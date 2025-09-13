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
        'percentage',
        'profit_share',
        'note',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'profit_share' => 'decimal:2',
    ];

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // حساب النسبة المئوية للاستثمار
    public function calculatePercentage($totalCapital)
    {
        if ($totalCapital > 0) {
            $this->percentage = ($this->amount / $totalCapital) * 100;
            $this->save();
        } else {
            $this->percentage = 0;
            $this->save();
        }
    }

    // حساب نصيب المستثمر من الربح
    public function calculateProfitShare($totalProfit)
    {
        $percentage = $this->percentage ?? 0;
        $this->profit_share = ($percentage / 100) * $totalProfit;
        $this->save();
        return $this->profit_share;
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
}
