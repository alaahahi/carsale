<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Transactions;

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
        }
        
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

            // إنشاء معاملة إدخال
            Transactions::create([
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

            // التحقق من الرصيد المتاح
            $currentBalance = $this->calculateUserWalletBalance($userId);
            if ($amount > $currentBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'المبلغ المطلوب أكبر من الرصيد المتاح'
                ], 400);
            }

            // إنشاء معاملة سحب
            Transactions::create([
                'type' => 'user_out',
                'amount' => $amount,
                'description' => $description,
                'morphed_type' => 'App\Models\User',
                'morphed_id' => $userId,
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
