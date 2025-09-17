<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'car';
    protected $fillable = [
        'id',
        'no',
        'name',
        'company',
        'color',
        'model',
        'image',
        'pin',
        'price',
        'phone_number',
        'invoice_number',
        'paid_amount',
        'paid_amount_pay',
        'user_id',
        'user_purchase_id',
        'created_at',
        'updated_at',
        'purchase_price',
        'purchase_data',
        'pay_data',
        'pay_price',
        'note',
        'note_pay',
        'client_id',
        'results',
        'erbil_exp',
        'erbil_shipping',
        'dubai_exp',
        'dubai_shipping',
        'source',
        'tenant_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع استثمارات السيارات
    public function investmentCars()
    {
        return $this->hasMany(InvestmentCar::class);
    }

    // العلاقة مع الاستثمارات من خلال investmentCars
    public function investments()
    {
        return $this->hasManyThrough(Investment::class, InvestmentCar::class, 'car_id', 'id', 'id', 'investment_id');
    }

    // حساب إجمالي التكلفة للسيارة
    public function getTotalCostAttribute()
    {
        return $this->purchase_price + 
               ($this->erbil_exp ?? 0) + 
               ($this->erbil_shipping ?? 0) + 
               ($this->dubai_exp ?? 0) + 
               ($this->dubai_shipping ?? 0);
    }

    // حساب إجمالي الاستثمارات في هذه السيارة
    public function getTotalInvestmentsAttribute()
    {
        return $this->investmentCars()
            ->whereHas('investment', function($query) {
                $query->where('status', 'active');
            })
            ->sum('invested_amount');
    }

    // حساب المبلغ المتاح للاستثمار
    public function getAvailableForInvestmentAttribute()
    {
        return $this->total_cost - $this->total_investments;
    }

    // التحقق من إمكانية الاستثمار في السيارة
    public function canBeInvestedIn()
    {
        return $this->results == 0 && $this->available_for_investment >= 0;
    }

    // حساب ربح السيارة
    public function getProfitAttribute()
    {
        if ($this->results == 0) return 0; // السيارة لم تباع بعد
        
        return ($this->pay_price ?? 0) - $this->total_cost;
    }

    // حساب وتوزيع الربح على المستثمرين عند بيع السيارة
    public function distributeProfitToInvestors()
    {
        if ($this->results == 0) {
            return false; // السيارة لم تباع بعد
        }

        // فحص إضافي للتأكد من الدفع الكامل
        if ($this->pay_price != $this->paid_amount_pay) {
            \Log::warning('Profit distribution skipped - payment not complete', [
                'car_id' => $this->id,
                'car_no' => $this->no,
                'pay_price' => $this->pay_price,
                'paid_amount' => $this->paid_amount_pay,
                'results' => $this->results
            ]);
            return false; // الدفع غير مكتمل
        }

        $carProfit = $this->profit;
        if ($carProfit <= 0) {
            \Log::info('No profit to distribute', [
                'car_id' => $this->id,
                'car_no' => $this->no,
                'pay_price' => $this->pay_price,
                'total_cost' => $this->total_cost,
                'profit' => $carProfit
            ]);
            return false; // لا يوجد ربح
        }

        // الحصول على جميع الاستثمارات في هذه السيارة
        $investmentCars = $this->investmentCars()
            ->whereHas('investment', function($query) {
                $query->where('status', 'active');
            })
            ->get();

        foreach ($investmentCars as $investmentCar) {
            // التأكد من أن النسبة محسوبة بشكل صحيح
            if ($investmentCar->percentage == 0 || $investmentCar->percentage == null) {
                $investmentCar->calculatePercentage($this->total_cost);
                $investmentCar->refresh();
            }
            
            $investmentPercentage = $investmentCar->percentage;
            $profitShare = ($investmentPercentage / 100) * $carProfit;
            
            // تحديث نصيب الربح
            $investmentCar->update(['profit_share' => $profitShare]);
            
            // إنشاء المعاملات المالية للمستثمر
            $this->createProfitTransactions($investmentCar, $profitShare);
        }

        \Log::info('Profit distribution completed successfully', [
            'car_id' => $this->id,
            'car_no' => $this->no,
            'total_profit' => $carProfit,
            'distributed_to' => $investmentCars->count() . ' investors'
        ]);

        return true;
    }

    // إنشاء المعاملات المالية عند توزيع الربح
    private function createProfitTransactions($investmentCar, $profitShare)
    {
        $investor = $investmentCar->investment->user;
        $investorWallet = $investor->getWalletOrCreate();
        
        // الحصول على الصندوق الأساسي
        $mainWallet = \App\Models\Wallet::whereHas('user', function($query) {
            $query->where('email', 'main@account.com');
        })->first();
        
        if (!$mainWallet) {
            \Log::error('Main wallet not found for profit distribution');
            return;
        }
        
        // حساب المبلغ الإجمالي (رأس المال + الربح)
        $totalAmount = $investmentCar->invested_amount + $profitShare;
        
        // معاملة 1: سحب من الصندوق الأساسي (رأس المال + الربح)
        $withdrawTransaction = \App\Models\Transactions::create([
            'wallet_id' => $mainWallet->id,
            'type' => 'out',
            'amount' => $totalAmount,
            'description' => "توزيع رأس المال والربح للمستثمر {$investor->name} من بيع السيارة رقم {$this->no}",
            'morphed_type' => 'App\Models\Car',
            'morphed_id' => $this->id,
            'user_id' => auth()->id() ?? 1,
            'is_pay' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // معاملة 2: إضافة للمحفظة (رأس المال + الربح)
        $depositTransaction = \App\Models\Transactions::create([
            'wallet_id' => $investorWallet->id,
            'type' => 'investor_profit',
            'amount' => $totalAmount,
            'description' => "إيداع رأس المال + ربح المستثمر من بيع السيارة - " . $this->name . " (PIN: " . $this->pin . ") - المستثمر: " . $investor->name,
            'morphed_type' => 'App\Models\Car',
            'morphed_id' => $this->id,
            'user_id' => auth()->id() ?? 1,
            'is_pay' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // تحديث رصيد المحفظة (رأس المال + الربح)
        $investorWallet->increment('balance', $totalAmount);
        
        \Log::info('Simplified profit transactions created successfully', [
            'car_id' => $this->id,
            'car_no' => $this->no,
            'investor_id' => $investor->id,
            'investor_name' => $investor->name,
            'invested_amount' => $investmentCar->invested_amount,
            'profit_share' => $profitShare,
            'total_amount' => $totalAmount,
            'percentage' => $investmentCar->percentage,
            'withdraw_transaction_id' => $withdrawTransaction->id,
            'deposit_transaction_id' => $depositTransaction->id,
            'user_wallet_id' => $investorWallet->id,
            'main_wallet_id' => $mainWallet->id
        ]);
    }

    // الحصول على تفاصيل المستثمرين في هذه السيارة
    public function getInvestorDetails()
    {
        return $this->investmentCars()
            ->whereHas('investment', function($query) {
                $query->where('status', 'active');
            })
            ->with(['investment.user'])
            ->get()
            ->map(function($investmentCar) {
                return [
                    'investor_id' => $investmentCar->investment->user->id,
                    'investor_name' => $investmentCar->investment->user->name,
                    'invested_amount' => $investmentCar->invested_amount,
                    'investment_percentage' => $investmentCar->percentage,
                    'profit_share' => $investmentCar->profit_share,
                    'investment_id' => $investmentCar->investment_id
                ];
            });
    }
    protected $dates = ['deleted_at']; // Define the deleted_at column as a date
    public function Client()
    {
        return $this->belongsTo(User::class);
    }
        
    public function transactions()
    {
        return $this->morphMany(Transactions::class, 'morphed');
    }

    protected $appends = ['image_url'];

    /**
     * Get Added Image Attribute URL.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): array
    {

        $images = json_decode($this->image);

        if (!is_array($images)) {
            return [];
        }
    
        $imageUrls = [];
    
        foreach ($images as $image) {
            $imageUrl = url('') . "/storage/car/" . $image;
            if (Str::contains($imageUrl, '/car/car/')) {
                $imageUrl = str_replace('/car/car/', '/car/', $imageUrl);
            }
            
            $imageUrls[] = $imageUrl;
        }
    
        return $imageUrls;
    }

    public function fieldHistories()
    {
        return $this->hasMany(CarFieldHistory::class, 'car_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Method to log field changes
    public function logFieldChange($field, $oldValue, $newValue, $userId = null)
    {
        CarFieldHistory::create([
            'car_id' => $this->id,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => $userId,
        ]);
    }

  }