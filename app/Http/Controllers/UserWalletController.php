<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Transactions;
use App\Models\Investment;
use App\Models\Car;
use App\Models\Wallet;
use App\Models\InvestmentCar;

class UserWalletController extends Controller
{
    /**
     * عرض صفحة قاسة المستخدم
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id');
        
        // إذا لم يتم تمرير معرف المستخدم، استخدم المستخدم الحالي
        if (!$userId) {
            $user = Auth::user();
        } else {
            $user = User::findOrFail($userId);
            
            // التحقق من أن المستخدم يملك صلاحية عرض القاسة
            if (!$user->show_wallet) {
                abort(403, 'ليس لديك صلاحية لعرض القاسة');
            }
        }
        
        // إنشاء wallet إذا لم يكن موجوداً
        $user->getWalletOrCreate();
        
        // جلب معاملات المستخدم الشخصية فقط
        $userTransactions = $this->getUserTransactionsWithProfit($user);

        // حساب رصيد المستخدم
        $userWalletBalance = $this->calculateUserWalletBalance($user->id);

        // حساب رأس المال (مجموع سعر الشراء + المصاريف لجميع السيارات)
        $capital = Car::selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
            ->value('total') ?? 0;

        return Inertia::render('UserWallet', [
            'user' => $user,
            'userTransactions' => $userTransactions,
            'userWalletBalance' => $userWalletBalance,
            'capital' => $capital
        ]);
    }

    /**
     * جلب معاملات المستخدم مع معاملات الربح
     */
    private function getUserTransactionsWithProfit($user)
    {
        $transactions = Transactions::where(function($query) use ($user) {
            // معاملات مرتبطة بالمستخدم مباشرة
            $query->where('morphed_type', 'App\Models\User')
                  ->where('morphed_id', $user->id)
                  ->whereIn('type', ['user_in', 'user_out']);
        })->orWhere(function($query) use ($user) {
            // معاملات الربح من السيارات للمستخدم
            $query->where('wallet_id', $user->wallet->id)
                  ->where('type', 'user_in')
                  ->where(function($subQuery) {
                      $subQuery->where('description', 'LIKE', '%إرجاع رأس المال%')
                               ->orWhere('description', 'LIKE', '%نصيب الربح%');
                  });
        })->orWhere(function($query) use ($user) {
            // معاملات ربح المستثمر الجديدة
            $query->where('wallet_id', $user->wallet->id)
                  ->where('type', 'investor_profit');
        })->orWhere(function($query) use ($user) {
            // معاملات الاستثمار
            $query->where('wallet_id', $user->wallet->id)
                  ->where('type', 'investment');
        })->orderBy('created_at', 'desc')->get();
        
        // تسجيل المعاملات للتأكد
        \Log::info('User transactions retrieved', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'wallet_id' => $user->wallet->id,
            'transactions_count' => $transactions->count(),
            'investor_profit_count' => $transactions->where('type', 'investor_profit')->count(),
            'investment_count' => $transactions->where('type', 'investment')->count()
        ]);
        
        return $transactions;
    }

    /**
     * عرض صفحة قاسة مستخدم محدد
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        
        // التحقق من أن المستخدم يملك صلاحية عرض القاسة
        if (!$user->show_wallet) {
            abort(403, 'ليس لديك صلاحية لعرض القاسة');
        }
        
        // إنشاء wallet إذا لم يكن موجوداً
        $user->getWalletOrCreate();
        
        // جلب معاملات المستخدم الشخصية فقط
        $userTransactions = $this->getUserTransactionsWithProfit($user);

        // حساب رصيد المستخدم
        $userWalletBalance = $this->calculateUserWalletBalance($user->id);

        // حساب رأس المال (مجموع سعر الشراء + المصاريف لجميع السيارات)
        $capital = Car::selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
            ->value('total') ?? 0;

        // حساب إجمالي الربح من مدفوعات السيارات
        $totalCarPayments = Car::whereNotNull('paid_amount_pay')
            ->sum('paid_amount_pay') ?? 0;
        $totalProfit = max(0, $totalCarPayments - $capital);

        // جلب بيانات الاستثمارات للمستخدم - مجمعة حسب المستخدم
        $allActiveInvestments = Investment::where('status', 'active')->with('user')->get();
        $groupedInvestments = $allActiveInvestments->groupBy('user_id');
        
        // جلب الاستثمارات مع إعادة تحميل البيانات للتأكد من أحدث المعلومات
        $activeInvestments = $user->activeInvestments()->get();
        
        // إعادة تحميل كل استثمار للتأكد من أحدث البيانات
        foreach ($activeInvestments as $investment) {
            $investment->refresh();
            foreach ($investment->investmentCars as $investmentCar) {
                $investmentCar->refresh();
                $investmentCar->car->refresh();
            }
        }
        
        $userInvestments = [
            'totalAmount' => $user->activeInvestments()->sum('amount'),
            'totalPercentage' => 0,
            'totalProfitShare' => $user->getTotalProfitFromCars(),
            'activeInvestments' => $activeInvestments,
            'groupedInvestments' => $groupedInvestments->map(function ($investments) {
                $firstInvestment = $investments->first();
                return [
                    'user' => $firstInvestment->user,
                    'totalAmount' => $investments->sum('amount'),
                    'totalPercentage' => 0, // سيتم حسابها من investment_cars
                    'totalProfitShare' => $firstInvestment->getTotalProfitFromCars(),
                    'investments' => $investments,
                    'investmentCount' => $investments->count()
                ];
            })->values()
        ];

        // حساب النسبة المئوية للاستثمارات
        if ($capital > 0) {
            $userInvestments['totalPercentage'] = ($userInvestments['totalAmount'] / $capital) * 100;
        }

        // حساب جميع الاستثمارات النشطة (ليس فقط للمستخدم الحالي)
        $totalActiveInvestments = Investment::where('status', 'active')->sum('amount');
        
        // حساب رأس المال - جميع الاستثمارات النشطة
        $capitalInvestmentDifference = $capital - $totalActiveInvestments;
        $suggestedInvestmentAmount = $capitalInvestmentDifference > 0 ? $capitalInvestmentDifference : 0;

        return Inertia::render('UserWallet', [
            'user' => $user,
            'userTransactions' => $userTransactions,
            'userWalletBalance' => $userWalletBalance,
            'capital' => $capital,
            'totalProfit' => $totalProfit,
            'userInvestments' => $userInvestments,
            'capitalInvestmentDifference' => $capitalInvestmentDifference,
            'suggestedInvestmentAmount' => $suggestedInvestmentAmount
        ]);
    }

    /**
     * إضافة مبلغ إلى قاسة المستخدم
     */
    public function addToWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $userId = $request->get('user_id') ?: Auth::id();
            $amount = $request->amount;
            $description = $request->description ?: 'إضافة إلى القاسة';

            // التحقق من وجود المستخدم
            if (!$userId) {
                throw new \Exception('معرف المستخدم غير محدد - يرجى تسجيل الدخول أولاً');
            }

            $user = User::find($userId);
            if (!$user) {
                throw new \Exception("المستخدم غير موجود (ID: {$userId})");
            }
            $wallet = $user->getWalletOrCreate();

            // زيادة رصيد المحفظة
            $wallet->increment('balance', $amount);

            // إنشاء معاملة إدخال
            Transactions::create([
                'wallet_id' => $wallet->id,
                'type' => 'user_in',
                'amount' => $amount,
                'description' => $description,
                'morphed_type' => 'App\Models\User',
                'morphed_id' => $userId,
                'user_id' => Auth::id(), // المستخدم الذي قام بالمعاملة
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المبلغ بنجاح'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error adding to user wallet: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء إضافة المبلغ'
        ], 500);
    }
}

/**
 * إضافة استثمار مباشر في سيارات محددة (إضافة للقاسة ثم استثمار نفس المبلغ في سيارات)
 */
public function addDirectCarInvestment(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'cars' => 'required|array|min:1',
        'cars.*.car_id' => 'required|exists:car,id',
        'cars.*.amount' => 'required|numeric|min:0.01',
        'note' => 'nullable|string|max:255',
        'user_id' => 'nullable|exists:users,id'
    ]);

    try {
        DB::beginTransaction();

        $userId = $request->get('user_id') ?: Auth::id();
        $amount = $request->amount;
        $note = $request->note ?: 'استثمار مباشر في سيارات';

        // الحصول على المستخدم وإنشاء wallet إذا لم يكن موجوداً
        $user = User::findOrFail($userId);
        $wallet = $user->getWalletOrCreate();

        // التحقق من صحة الاستثمار في السيارات
        $totalCarAmount = 0;
        foreach ($request->cars as $carData) {
            $car = Car::find($carData['car_id']);
            if (!$car || !$car->canBeInvestedIn()) {
                throw new \Exception("السيارة رقم {$car->no} غير متاحة للاستثمار");
            }
            
            if ($carData['amount'] > $car->total_cost) {
                throw new \Exception("المبلغ المطلوب للسيارة رقم {$car->no} يتجاوز سعر السيارة الإجمالي");
            }
            
            $totalCarAmount += $carData['amount'];
        }

        if (abs($totalCarAmount - $amount) > 0.01) {
            throw new \Exception('إجمالي مبالغ السيارات يجب أن يساوي المبلغ الإجمالي');
        }

        // التحقق من وجود رصيد كافي في المحفظة
        if ($wallet->balance < $amount) {
            throw new \Exception('الرصيد غير كافي في المحفظة للاستثمار');
        }

        // الخطوة 1: سحب المبلغ من المحفظة
        $wallet->decrement('balance', $amount);
        
        // تسجيل معاملة السحب من القاسة
        Transactions::create([
            'wallet_id' => $wallet->id,
            'type' => 'investment',
            'amount' => $amount,
            'description' => "سحب للاستثمار المباشر في سيارات - {$note}",
            'morphed_type' => null, // سيتم تحديثها بعد إنشاء الاستثمار
            'morphed_id' => null,
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // الخطوة 2: إضافة المبلغ للصندوق الأساسي
        $mainWallet = Wallet::whereHas('user', function($query) {
            $query->where('email', 'main@account.com');
        })->first();
        
        if ($mainWallet) {
            $mainWallet->increment('balance', $amount);
            
            // إنشاء معاملة دخول للصندوق الأساسي
            Transactions::create([
                'wallet_id' => $mainWallet->id,
                'type' => 'in',
                'amount' => $amount,
                'description' => "إيداع استثمار مباشر في سيارات - {$note}",
                'morphed_type' => 'App\Models\User',
                'morphed_id' => $userId,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // إنشاء الاستثمار
        $investment = Investment::create([
            'user_id' => $userId,
            'amount' => $amount,
            'note' => $note,
            'status' => 'active',
            'investment_type' => 'specific_cars'
        ]);

        // تحديث معاملة السحب بربطها بالاستثمار
        Transactions::where('wallet_id', $wallet->id)
            ->where('type', 'investment')
            ->where('description', 'LIKE', '%سحب للاستثمار المباشر%')
            ->whereNull('morphed_id')
            ->latest()
            ->first()
            ->update([
                'morphed_id' => $investment->id,
                'morphed_type' => 'App\Models\Investment'
            ]);

        // إنشاء استثمارات السيارات
        foreach ($request->cars as $carData) {
            $car = Car::find($carData['car_id']);
            
            $investmentCar = InvestmentCar::create([
                'investment_id' => $investment->id,
                'car_id' => $car->id,
                'invested_amount' => $carData['amount'],
                'percentage' => 0,
                'profit_share' => 0
            ]);

            // حساب النسبة المئوية للاستثمار في هذه السيارة
            $investmentCar->calculatePercentage($car->total_cost);
            
            // إذا كانت السيارة مباعة، حساب وتوزيع الربح فوراً
            if ($car->results != 0) {
                // إعادة تحميل البيانات للتأكد من وجود النسبة المحدثة
                $investmentCar->refresh();
                $car->distributeProfitToInvestors();
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الاستثمار المباشر في السيارات بنجاح - تم سحب المبلغ من القاسة وإيداعه في الصندوق',
            'investment' => [
                'id' => $investment->id,
                'amount' => $investment->amount,
                'cars_count' => count($request->cars),
                'sold_cars_count' => count(array_filter($request->cars, function($carData) {
                    $car = Car::find($carData['car_id']);
                    return $car && $car->results != 0;
                }))
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error adding direct car investment: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء إضافة الاستثمار المباشر: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * سحب مبلغ من قاسة المستخدم
     */
    public function withdrawFromWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $userId = $request->get('user_id') ?: Auth::id();
            $amount = $request->amount;
            $description = $request->description ?: 'سحب من القاسة';

            // الحصول على المستخدم والمحفظة
            $user = User::findOrFail($userId);
            $wallet = $user->getWalletOrCreate();

            // التحقق من الرصيد المتاح
            if ($amount > $wallet->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'المبلغ المطلوب أكبر من الرصيد المتاح'
                ], 400);
            }

            // تقليل رصيد المحفظة
            $wallet->decrement('balance', $amount);

            // إنشاء معاملة سحب
            Transactions::create([
                'wallet_id' => $wallet->id,
                'type' => 'user_out',
                'amount' => $amount,
                'description' => $description,
                'morphed_type' => 'App\Models\User',
                'morphed_id' => $userId,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم سحب المبلغ بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error withdrawing from user wallet: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء سحب المبلغ'
            ], 500);
        }
    }

    /**
     * حذف معاملة المستخدم
     */
    public function deleteTransaction(Request $request, $transactionId)
    {
        try {
            DB::beginTransaction();

            $userId = $request->get('user_id') ?: Auth::id();
            
            // البحث عن المعاملة والتأكد من أنها تخص المستخدم المحدد
            $transaction = Transactions::where('id', $transactionId)
                ->where('morphed_type', 'App\Models\User')
                ->where('morphed_id', $userId)
                ->whereIn('type', ['user_in', 'user_out'])
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'المعاملة غير موجودة أو لا تملك صلاحية حذفها'
                ], 404);
            }

            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المعاملة بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting user transaction: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المعاملة'
            ], 500);
        }
    }

    /**
     * حساب رصيد المستخدم
     */
    private function calculateUserWalletBalance($userId)
    {
        // حساب المعاملات الداخلة (user_in + investor_profit)
        $totalIn = Transactions::where(function($query) use ($userId) {
            $query->where(function($subQuery) use ($userId) {
                // معاملات المستخدم الشخصية
                $subQuery->where('morphed_type', 'App\Models\User')
                         ->where('morphed_id', $userId)
                         ->where('type', 'user_in');
            })->orWhere(function($subQuery) use ($userId) {
                // معاملات ربح المستثمر
                $subQuery->where('type', 'investor_profit')
                         ->whereHas('wallet', function($walletQuery) use ($userId) {
                             $walletQuery->where('user_id', $userId);
                         });
            });
        })->sum('amount') ?? 0;

        // حساب المعاملات الخارجة (user_out + investment)
        $totalOut = Transactions::where(function($query) use ($userId) {
            $query->where(function($subQuery) use ($userId) {
                // معاملات المستخدم الشخصية
                $subQuery->where('morphed_type', 'App\Models\User')
                         ->where('morphed_id', $userId)
                         ->where('type', 'user_out');
            })->orWhere(function($subQuery) use ($userId) {
                // معاملات الاستثمار
                $subQuery->where('type', 'investment')
                         ->whereHas('wallet', function($walletQuery) use ($userId) {
                             $walletQuery->where('user_id', $userId);
                         });
            });
        })->sum('amount') ?? 0;

        $balance = $totalIn - $totalOut;
        
        // تسجيل حساب الرصيد للتأكد
        \Log::info('User wallet balance calculated', [
            'user_id' => $userId,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $balance
        ]);

        return $balance;
    }

    /**
     * جلب إحصائيات المستخدمين للمحاسبة
     */
    public function getUserStats()
    {
        $users = User::where('email', '!=', 'admin@admin.com')
            ->withCount(['transactions as user_in_count' => function($query) {
                $query->where('type', 'user_in');
            }])
            ->withCount(['transactions as user_out_count' => function($query) {
                $query->where('type', 'user_out');
            }])
            ->get()
            ->map(function($user) {
                $userInTotal = Transactions::where('morphed_type', 'App\Models\User')
                    ->where('morphed_id', $user->id)
                    ->where('type', 'user_in')
                    ->sum('amount');
                
                $userOutTotal = Transactions::where('morphed_type', 'App\Models\User')
                    ->where('morphed_id', $user->id)
                    ->where('type', 'user_out')
                    ->sum('amount');
                
                $user->user_in_total = $userInTotal;
                $user->user_out_total = $userOutTotal;
                $user->user_balance = $userInTotal - $userOutTotal;
                
                return $user;
            });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    // الحصول على السيارات المتاحة للاستثمار
    public function getAvailableCarsForInvestment(Request $request)
    {
        $search = $request->get('search', '');
        
        $query = Car::where('results', 0) // السيارات في المخزن فقط
            ->with(['investmentCars.investment.user']);
            
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('pin', 'LIKE', '%' . $search . '%')
                  ->orWhere('no', 'LIKE', '%' . $search . '%');
            });
        }
        
        $cars = $query->get()->map(function($car) {
            $car->total_cost = $car->total_cost;
            $car->total_investments = $car->total_investments;
            $car->available_for_investment = $car->available_for_investment;
            $car->can_be_invested = $car->canBeInvestedIn();
            
            // معلومات المستثمرين الحاليين
            $car->current_investors = $car->investmentCars->map(function($investmentCar) {
                return [
                    'id' => $investmentCar->investment->user->id,
                    'name' => $investmentCar->investment->user->name,
                    'amount' => $investmentCar->invested_amount,
                    'percentage' => $investmentCar->percentage,
                ];
            });
            
            return $car;
        })->filter(function($car) {
            // فلترة السيارات التي يمكن الاستثمار فيها (حتى لو كانت 100% مستثمرة)
            return $car->can_be_invested;
        });
        
        return response()->json([
            'cars' => $cars,
            'total' => $cars->count()
        ]);
    }

    // إنشاء استثمار في سيارات محددة
    public function createCarInvestment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'cars' => 'required|array|min:1',
            'cars.*.car_id' => 'required|exists:car,id',
            'cars.*.amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $wallet = $user->getWalletOrCreate();

            // التحقق من وجود رصيد كافي
            if ($wallet->balance < $request->amount) {
                return response()->json(['error' => 'الرصيد غير كافي'], 400);
            }

            // التحقق من صحة الاستثمار في السيارات
            $totalCarAmount = 0;
            foreach ($request->cars as $carData) {
                $car = Car::find($carData['car_id']);
                if (!$car || !$car->canBeInvestedIn()) {
                    return response()->json(['error' => "السيارة رقم {$car->no} غير متاحة للاستثمار"], 400);
                }
                
                if ($carData['amount'] > $car->total_cost) {
                    return response()->json(['error' => "المبلغ المطلوب للسيارة رقم {$car->no} يتجاوز سعر السيارة الإجمالي"], 400);
                }
                
                $totalCarAmount += $carData['amount'];
            }

            if (abs($totalCarAmount - $request->amount) > 0.01) {
                return response()->json(['error' => 'إجمالي مبالغ السيارات يجب أن يساوي المبلغ الإجمالي'], 400);
            }

            // إنشاء الاستثمار
            $investment = Investment::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'note' => $request->note,
                'status' => 'active',
                'investment_type' => 'specific_cars'
            ]);

            // إنشاء استثمارات السيارات
            foreach ($request->cars as $carData) {
                $car = Car::find($carData['car_id']);
                
                $investmentCar = InvestmentCar::create([
                    'investment_id' => $investment->id,
                    'car_id' => $car->id,
                    'invested_amount' => $carData['amount'],
                    'percentage' => 0,
                    'profit_share' => 0
                ]);

                // حساب النسبة المئوية للاستثمار في هذه السيارة
                $investmentCar->calculatePercentage($car->total_cost);
                
                // إذا كانت السيارة مباعة، حساب وتوزيع الربح فوراً
                if ($car->results != 0) {
                    // إعادة تحميل البيانات للتأكد من وجود النسبة المحدثة
                    $investmentCar->refresh();
                    $car->distributeProfitToInvestors();
                }
            }

            // خصم المبلغ من المحفظة
            $wallet->decrement('balance', $request->amount);

            // إنشاء معاملة
            Transactions::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'investment_out',
                'description' => 'استثمار في سيارات محددة - ' . $investment->cars->pluck('no')->implode(', '),
                'morphed_id' => $investment->id,
                'morphed_type' => Investment::class,
            ]);

            DB::commit();

            return response()->json([
                'success' => 'تم إنشاء الاستثمار في السيارات المحددة بنجاح',
                'investment' => $investment->load(['investmentCars.car', 'user'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'حدث خطأ أثناء إنشاء الاستثمار: ' . $e->getMessage()], 500);
        }
    }

    // الحصول على تفاصيل استثمار معين
    public function getInvestmentDetails($investmentId)
    {
        $investment = Investment::with([
            'user',
            'investmentCars.car',
            'investmentCars' => function($query) {
                $query->with(['car' => function($carQuery) {
                    $carQuery->with(['investmentCars.investment.user']);
                }]);
            }
        ])->findOrFail($investmentId);

        $investment->cars_details = $investment->investmentCars->map(function($investmentCar) {
            $car = $investmentCar->car;
            return [
                'car_id' => $car->id,
                'car_no' => $car->no,
                'car_name' => $car->name,
                'car_pin' => $car->pin,
                'car_color' => $car->color,
                'car_model' => $car->model,
                'car_total_cost' => $car->total_cost,
                'car_status' => $car->results == 0 ? 'في المخزن' : 'مباعة',
                'car_profit' => $car->profit,
                'invested_amount' => $investmentCar->invested_amount,
                'investment_percentage' => $investmentCar->percentage,
                'profit_share' => $investmentCar->profit_share,
                'other_investors' => $car->investmentCars
                    ->where('investment_id', '!=', $investmentCar->investment_id)
                    ->map(function($otherInvestment) {
                        return [
                            'investor_name' => $otherInvestment->investment->user->name,
                            'amount' => $otherInvestment->invested_amount,
                            'percentage' => $otherInvestment->percentage
                        ];
                    })
            ];
        });

        return response()->json($investment);
    }

    // حساب وتوزيع الربح عند بيع السيارة
    public function calculateProfitOnCarSale(Request $request, $carId)
    {
        try {
            $car = Car::findOrFail($carId);
            
            if ($car->results == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'السيارة لم تباع بعد'
                ], 400);
            }

            // توزيع الربح على المستثمرين
            $car->distributeProfitToInvestors();

            // الحصول على تفاصيل المستثمرين
            $investorDetails = $car->getInvestorDetails();

            return response()->json([
                'success' => true,
                'message' => 'تم حساب وتوزيع الربح بنجاح',
                'car' => [
                    'id' => $car->id,
                    'no' => $car->no,
                    'name' => $car->name,
                    'total_cost' => $car->total_cost,
                    'sale_price' => $car->pay_price,
                    'profit' => $car->profit
                ],
                'investors' => $investorDetails
            ]);

        } catch (\Exception $e) {
            \Log::error('Error calculating profit on car sale: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب الربح: ' . $e->getMessage()
            ], 500);
        }
    }

    // الحصول على تقرير الربح للمستثمر
    public function getInvestorProfitReport(Request $request, $userId = null)
    {
        try {
            $userId = $userId ?: Auth::id();
            $user = User::findOrFail($userId);

            // الحصول على جميع استثمارات المستخدم النشطة
            $investments = Investment::where('user_id', $userId)
                ->where('status', 'active')
                ->with(['investmentCars.car'])
                ->get();

            $totalInvested = $investments->sum('amount');
            $totalProfit = 0;
            $carDetails = [];

            foreach ($investments as $investment) {
                $carProfitDetails = $investment->getCarProfitDetails();
                
                foreach ($carProfitDetails as $carDetail) {
                    $totalProfit += $carDetail['profit_share'];
                    $carDetails[] = $carDetail;
                }
            }

            return response()->json([
                'success' => true,
                'investor' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'summary' => [
                    'total_invested' => $totalInvested,
                    'total_profit' => $totalProfit,
                    'profit_percentage' => $totalInvested > 0 ? ($totalProfit / $totalInvested) * 100 : 0,
                    'cars_count' => count($carDetails)
                ],
                'car_details' => $carDetails
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting investor profit report: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على تقرير الربح: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب السيارات التي تحتاج إكمال استثمار للمستخدم الحالي
     */
    public function getCarsNeedingCompletionForUser()
    {
        try {
            $userId = auth()->id();
            \Log::info("جلب السيارات التي تحتاج إكمال استثمار للمستخدم: {$userId}");
            
            // جلب السيارات غير المباعة التي استثمر فيها المستخدم من قبل فقط
            $carsWithInvestments = \App\Models\Car::where('results', 0)
                ->whereHas('investmentCars', function($query) use ($userId) {
                    $query->whereHas('investment', function($q) use ($userId) {
                        $q->where('user_id', $userId)
                          ->where('status', 'active');
                    });
                })
                ->with(['investmentCars.investment.user', 'client'])
                ->get();
                
            \Log::info('عدد السيارات التي استثمر فيها المستخدم: ' . $carsWithInvestments->count());
            
            // تسجيل تفاصيل كل سيارة للتشخيص
            foreach ($carsWithInvestments as $car) {
                \Log::info("السيارة رقم {$car->no} - {$car->name} - PIN: {$car->pin}");
            }
            
            $carsNeedingCompletion = $carsWithInvestments->map(function ($car) use ($userId) {
                $carTotalCost = $car->purchase_price + 
                               ($car->erbil_exp ?? 0) + 
                               ($car->erbil_shipping ?? 0) + 
                               ($car->dubai_exp ?? 0) + 
                               ($car->dubai_shipping ?? 0);
                
                $totalInvestedAmount = $car->investmentCars()
                    ->whereHas('investment', function($query) {
                        $query->where('status', 'active');
                    })
                    ->sum('invested_amount');
                
                $userInvestedAmount = $car->investmentCars()
                    ->whereHas('investment', function($query) use ($userId) {
                        $query->where('user_id', $userId)
                              ->where('status', 'active');
                    })
                    ->sum('invested_amount');
                
                $remainingAmount = max(0, $carTotalCost - $totalInvestedAmount);
                $needsCompletion = $remainingAmount > 0;
                
                // التحقق إذا كان المستخدم مستثمر أم لا
                $isUserInvestor = $userInvestedAmount > 0;
                
                \Log::info("السيارة رقم {$car->no}: التكلفة الإجمالية = {$carTotalCost}, المستثمر إجمالي = {$totalInvestedAmount}, المستثمر الحالي = {$userInvestedAmount}, المتبقي = {$remainingAmount}, يحتاج إكمال = " . ($needsCompletion ? 'نعم' : 'لا') . ", المستخدم مستثمر = " . ($isUserInvestor ? 'نعم' : 'لا'));
                
                return [
                    'id' => $car->id,
                    'no' => $car->no,
                    'name' => $car->name,
                    'pin' => $car->pin, // رقم الشاسي
                    'purchase_price' => $car->purchase_price,
                    'total_cost' => $carTotalCost,
                    'total_invested' => $totalInvestedAmount,
                    'user_invested' => $userInvestedAmount,
                    'remaining_amount' => $remainingAmount,
                    'investment_percentage' => $carTotalCost > 0 ? ($totalInvestedAmount / $carTotalCost) * 100 : 0,
                    'user_investment_percentage' => $carTotalCost > 0 ? ($userInvestedAmount / $carTotalCost) * 100 : 0,
                    'needs_completion' => $needsCompletion,
                    'is_user_investor' => $isUserInvestor,
                    'available_for_investment' => $remainingAmount,
                    'client_name' => $car->client->name ?? 'في المخزن',
                    'color' => $car->color ?? '',
                    'model' => $car->model ?? '',
                    'source' => $car->source ?? '', // مصدر السيارة
                    'company' => $car->company ?? '', // الشركة
                    'current_investors' => $car->investmentCars()
                        ->whereHas('investment', function($query) {
                            $query->where('status', 'active');
                        })
                        ->with('investment.user')
                        ->get()
                        ->map(function ($investmentCar) {
                            return [
                                'id' => $investmentCar->investment->user->id,
                                'name' => $investmentCar->investment->user->name,
                                'invested_amount' => $investmentCar->invested_amount,
                                'percentage' => $investmentCar->percentage,
                            ];
                        }),
                    'total_cost_breakdown' => [
                        'purchase_price' => $car->purchase_price,
                        'erbil_exp' => $car->erbil_exp ?? 0,
                        'erbil_shipping' => $car->erbil_shipping ?? 0,
                        'dubai_exp' => $car->dubai_exp ?? 0,
                        'dubai_shipping' => $car->dubai_shipping ?? 0,
                    ]
                ];
            })
            ->filter(function ($car) {
                // عرض السيارات التي تحتاج إكمال أو المتاحة للاستثمار
                $showCar = $car['needs_completion'] && $car['remaining_amount'] > 0;
                \Log::info("السيارة {$car['no']}: needs_completion = " . ($car['needs_completion'] ? 'true' : 'false') . ", remaining_amount = {$car['remaining_amount']}, show = " . ($showCar ? 'true' : 'false'));
                return $showCar;
            })
            ->values();

            \Log::info('عدد السيارات التي تحتاج إكمال استثمار للمستخدم الحالي: ' . $carsNeedingCompletion->count());

            return response()->json([
                'success' => true,
                'cars' => $carsNeedingCompletion,
                'debug_info' => [
                    'user_id' => $userId,
                    'total_cars_with_user_investments' => $carsWithInvestments->count(),
                    'cars_needing_completion' => $carsNeedingCompletion->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting cars needing completion for user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب السيارات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إكمال استثمار السيارة - تحويل المبلغ من الصندوق إلى قاسة المستثمر
     */
    public function completeCarInvestment(Request $request)
    {
        try {
            $userId = auth()->id();
            $carId = $request->input('car_id');
            $amount = $request->input('amount');
            
            \Log::info("إكمال استثمار السيارة: المستخدم = {$userId}, السيارة = {$carId}, المبلغ = {$amount}");
            
            // التحقق من صحة البيانات
            if (!$carId || !$amount || $amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة'
                ], 400);
            }
            
            // جلب السيارة والتحقق من وجودها
            $car = \App\Models\Car::find($carId);
            if (!$car) {
                return response()->json([
                    'success' => false,
                    'message' => 'السيارة غير موجودة'
                ], 404);
            }
            
            // التحقق من أن المستخدم مستثمر في هذه السيارة
            $userInvestment = $car->investmentCars()
                ->whereHas('investment', function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('status', 'active');
                })
                ->first();
                
            if (!$userInvestment) {
                return response()->json([
                    'success' => false,
                    'message' => 'أنت لست مستثمراً في هذه السيارة'
                ], 403);
            }
            
            // حساب المبلغ المتبقي المطلوب
            $carTotalCost = $car->purchase_price + 
                           ($car->erbil_exp ?? 0) + 
                           ($car->erbil_shipping ?? 0) + 
                           ($car->dubai_exp ?? 0) + 
                           ($car->dubai_shipping ?? 0);
            
            $totalInvestedAmount = $car->investmentCars()
                ->whereHas('investment', function($query) {
                    $query->where('status', 'active');
                })
                ->sum('invested_amount');
            
            $remainingAmount = max(0, $carTotalCost - $totalInvestedAmount);
            
            if ($amount > $remainingAmount) {
                return response()->json([
                    'success' => false,
                    'message' => "المبلغ المطلوب ({$amount}) أكبر من المبلغ المتبقي ({$remainingAmount})"
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // 1. سحب المبلغ من الصندوق الرئيسي
                $mainFundWallet = \App\Models\Wallet::where('type', 'main_fund')->first();
                if (!$mainFundWallet) {
                    throw new \Exception('محفظة الصندوق الرئيسي غير موجودة');
                }
                
                // معاملة سحب من الصندوق
                $withdrawTransaction = \App\Models\Transactions::create([
                    'wallet_id' => $mainFundWallet->id,
                    'type' => 'fund_out',
                    'amount' => $amount,
                    'description' => "سحب لإكمال استثمار السيارة رقم {$car->no} - {$car->name} (PIN: {$car->pin}) - المستثمر: " . auth()->user()->name,
                    'morphed_type' => 'App\Models\Car',
                    'morphed_id' => $car->id,
                    'user_id' => $userId,
                    'is_pay' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // 2. إضافة المبلغ إلى قاسة المستثمر
                $userWallet = \App\Models\Wallet::where('user_id', $userId)->first();
                if (!$userWallet) {
                    throw new \Exception('محفظة المستخدم غير موجودة');
                }
                
                // معاملة إضافة للقاسة
                $depositTransaction = \App\Models\Transactions::create([
                    'wallet_id' => $userWallet->id,
                    'type' => 'user_in',
                    'amount' => $amount,
                    'description' => "إيداع لإكمال استثمار السيارة رقم {$car->no} - {$car->name} (PIN: {$car->pin})",
                    'morphed_type' => 'App\Models\Car',
                    'morphed_id' => $car->id,
                    'user_id' => $userId,
                    'is_pay' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // 3. تحديث استثمار المستثمر في السيارة
                $userInvestment->invested_amount += $amount;
                $userInvestment->save();
                
                DB::commit();
                
                \Log::info("تم إكمال استثمار السيارة بنجاح: السيارة = {$car->no}, المبلغ = {$amount}");
                
                return response()->json([
                    'success' => true,
                    'message' => "تم إكمال استثمار السيارة رقم {$car->no} بنجاح - تم تحويل {$amount}$ من الصندوق إلى قاستك",
                    'transaction_details' => [
                        'withdraw_transaction_id' => $withdrawTransaction->id,
                        'deposit_transaction_id' => $depositTransaction->id,
                        'amount' => $amount,
                        'car_no' => $car->no,
                        'car_name' => $car->name
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Error completing car investment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إكمال الاستثمار: ' . $e->getMessage()
            ], 500);
        }
    }
}
