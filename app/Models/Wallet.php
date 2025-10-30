<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'balance',
        // removed legacy 'card' column usage
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    /**
     * حساب الرصيد الحقيقي من المعاملات
     */
    public function getCalculatedBalance()
    {
        $totalIn = $this->transactions()
            ->whereIn('type', ['user_in', 'fund_in', 'investor_profit', 'in'])
            ->sum('amount');

        $totalOut = $this->transactions()
            ->whereIn('type', ['user_out', 'fund_out', 'investment', 'out'])
            ->sum('amount');

        return $totalIn - $totalOut;
    }

    /**
     * حساب الرصيد الحقيقي (accessor)
     */
    public function getRealBalanceAttribute()
    {
        return $this->getCalculatedBalance();
    }
}
