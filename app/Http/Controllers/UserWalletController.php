<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Transactions;
use App\Models\Investment;

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
        $userTransactions = Transactions::where('morphed_type', 'App\Models\User')
            ->where('morphed_id', $user->id)
            ->whereIn('type', ['user_in', 'user_out'])
            ->orderBy('created_at', 'desc')
            ->get();

        // حساب رصيد المستخدم
        $userWalletBalance = $this->calculateUserWalletBalance($user->id);

        // حساب رأس المال (مجموع سعر الشراء + المصاريف لجميع السيارات)
        $capital = DB::table('car')
            ->selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
            ->value('total') ?? 0;

        return Inertia::render('UserWallet', [
            'user' => $user,
            'userTransactions' => $userTransactions,
            'userWalletBalance' => $userWalletBalance,
            'capital' => $capital
        ]);
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
        $userTransactions = Transactions::where('morphed_type', 'App\Models\User')
            ->where('morphed_id', $user->id)
            ->whereIn('type', ['user_in', 'user_out'])
            ->orderBy('created_at', 'desc')
            ->get();

        // حساب رصيد المستخدم
        $userWalletBalance = $this->calculateUserWalletBalance($user->id);

        // حساب رأس المال (مجموع سعر الشراء + المصاريف لجميع السيارات)
        $capital = DB::table('car')
            ->selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
            ->value('total') ?? 0;

        // حساب إجمالي الربح من مدفوعات السيارات
        $totalCarPayments = DB::table('car')
            ->whereNotNull('paid_amount_pay')
            ->sum('paid_amount_pay') ?? 0;
        $totalProfit = max(0, $totalCarPayments - $capital);

        // جلب بيانات الاستثمارات للمستخدم - مجمعة حسب المستخدم
        $allActiveInvestments = Investment::where('status', 'active')->with('user')->get();
        $groupedInvestments = $allActiveInvestments->groupBy('user_id');
        
        $userInvestments = [
            'totalAmount' => $user->activeInvestments()->sum('amount'),
            'totalPercentage' => 0,
            'totalProfitShare' => $user->activeInvestments()->sum('profit_share'),
            'activeInvestments' => $user->activeInvestments()->with('user')->get(),
            'groupedInvestments' => $groupedInvestments->map(function ($investments) {
                $firstInvestment = $investments->first();
                return [
                    'user' => $firstInvestment->user,
                    'totalAmount' => $investments->sum('amount'),
                    'totalPercentage' => $investments->sum('percentage'),
                    'totalProfitShare' => $investments->sum('profit_share'),
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

            // الحصول على المستخدم وإنشاء wallet إذا لم يكن موجوداً
            $user = User::findOrFail($userId);
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
 * إضافة استثمار مباشر (إضافة للقاسة ثم استثمار نفس المبلغ)
 */
public function addDirectInvestment(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'note' => 'nullable|string|max:255',
        'user_id' => 'nullable|exists:users,id'
    ]);

    try {
        DB::beginTransaction();

        $userId = $request->get('user_id') ?: Auth::id();
        $amount = $request->amount;
        $note = $request->note ?: 'استثمار مباشر';

        // الحصول على المستخدم وإنشاء wallet إذا لم يكن موجوداً
        $user = User::findOrFail($userId);
        $wallet = $user->getWalletOrCreate();

        // الخطوة 1: إضافة المبلغ للقاسة
        $wallet->increment('balance', $amount);

        // إنشاء معاملة إدخال للقاسة
        Transactions::create([
            'wallet_id' => $wallet->id,
            'type' => 'user_in',
            'amount' => $amount,
            'description' => 'إضافة للقاسة قبل الاستثمار المباشر',
            'morphed_type' => 'App\Models\User',
            'morphed_id' => $userId,
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // الخطوة 2: استثمار نفس المبلغ (خصم من الرصيد وإضافة للاستثمار)
        $wallet->decrement('balance', $amount);

        // إنشاء معاملة خروج للقاسة (للاستثمار)
        Transactions::create([
            'wallet_id' => $wallet->id,
            'type' => 'user_out',
            'amount' => $amount,
            'description' => 'استثمار مباشر من القاسة',
            'morphed_type' => 'App\Models\User',
            'morphed_id' => $userId,
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // حساب رأس المال الحالي
        $totalCapital = Car::selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
        ->value('total') ?? 0;

        // إنشاء الاستثمار
        $investment = Investment::create([
            'user_id' => $userId,
            'amount' => $amount,
            'note' => $note,
            'status' => 'active'
        ]);

        // حساب النسبة المئوية
        $investment->calculatePercentage($totalCapital);

        // حساب نصيب الربح
        $totalCarPayments = DB::table('car')
            ->whereNotNull('paid_amount_pay')
            ->sum('paid_amount_pay') ?? 0;
        $totalSoldCarsCost = DB::table('car')
            ->where('results', '!=', 0)
            ->selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
            ->value('total') ?? 0;
        $totalProfit = max(0, $totalCarPayments - $totalSoldCarsCost);
        $investment->calculateProfitShare($totalProfit);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الاستثمار المباشر بنجاح (تم إضافة المبلغ للقاسة ثم استثماره)'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error adding direct investment: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء إضافة الاستثمار المباشر'
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
        $totalIn = Transactions::where('morphed_type', 'App\Models\User')
            ->where('morphed_id', $userId)
            ->where('type', 'user_in')
            ->sum('amount');

        $totalOut = Transactions::where('morphed_type', 'App\Models\User')
            ->where('morphed_id', $userId)
            ->where('type', 'user_out')
            ->sum('amount');

        return $totalIn - $totalOut;
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
}
