<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Helpers\TenantDataHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerWalletController extends Controller
{
    public function index()
    {
        // جلب نوع المستخدم "client" فقط من قاعدة بيانات الـ tenant
        $clientTypeId = TenantDataHelper::getUserTypeId('client');
        
        if (!$clientTypeId) {
            // إذا لم يوجد نوع client، نعرض قائمة فارغة
            return Inertia::render('CustomerWallet', [
                'customers' => []
            ]);
        }
        
        // جلب المستخدمين من نوع client فقط
        $customers = User::where('type_id', $clientTypeId)
            ->whereNotIn('email', ['in@account.com', 'out@account.com', 'transfers@account.com'])
            ->where('email', '!=', 'a@a.com') // استبعاد Admin
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('CustomerWallet', [
            'customers' => $customers
        ]);
    }

    public function createWallet(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id'
        ]);

        $customer = User::find($request->customer_id);
        
        // إنشاء أو الحصول على قاسة المستخدم
        $wallet = $customer->getWalletOrCreate();
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء قاسة للزبون: ' . $customer->name,
            'wallet_id' => $wallet->id,
            'customer' => $customer
        ]);
    }

    public function getCustomerWallet($customerId)
    {
        $customer = User::find($customerId);
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'الزبون غير موجود'
            ], 404);
        }

        // Check if wallet exists, don't create automatically
        $wallet = $customer->wallet;
        
        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم إنشاء قاسة لهذا الزبون بعد',
                'customer' => $customer,
                'wallet' => null,
                'transactions' => [],
                'balance' => 0
            ]);
        }
        
        // حساب الرصيد من المعاملات الفعلية
        $totalIn = $wallet->transactions()
            ->where('type', 'in')
            ->sum('amount');
            
        $totalOut = $wallet->transactions()
            ->where('type', 'out')
            ->sum('amount');
            
        $calculatedBalance = $totalIn - $totalOut;
        
        // تحديث رصيد المحفظة إذا كان هناك فرق
        if ($wallet->balance != $calculatedBalance) {
            $wallet->balance = $calculatedBalance;
            $wallet->save();
            \Log::info("Corrected wallet balance for customer {$customer->id}", [
                'old_balance' => $wallet->balance,
                'new_balance' => $calculatedBalance,
                'total_in' => $totalIn,
                'total_out' => $totalOut
            ]);
        }
        
        // جلب المعاملات الأخيرة من المحفظة (الـ wallet_id كافي)
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'balance' => $calculatedBalance // استخدام الرصيد المحسوب
        ]);
    }
}

