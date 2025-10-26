<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Car;
use App\Models\ExpensesType;
use App\Models\Wallet;
use App\Models\Transactions;
use App\Models\Expenses;
use App\Models\UserType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SimpleCashController extends Controller
{
    public function __construct()
    {
        $this->url = env('FRONTEND_URL');
        
        // Get account users
        $this->userAccount = UserType::where('name', 'account')->first()->id;
        $this->inAccount = User::with('wallet')->where('type_id', $this->userAccount)->where('email','in@account.com')->first();
        $this->outAccount = User::with('wallet')->where('type_id', $this->userAccount)->where('email','out@account.com')->first();
        $this->transfersAccount = User::with('wallet')->where('type_id', $this->userAccount)->where('email','transfers@account.com')->first();
    }

    public function index(Request $request)
    {
        // Calculate cashbox balance from transactions
        $cashboxBalance = 0;
        if ($this->outAccount && $this->outAccount->wallet) {
            $wallet = $this->outAccount->wallet;
            
            // حساب الرصيد من المعاملات الفعلية
            $totalIn = $wallet->transactions()
                ->where('type', 'in')
                ->sum('amount');
                
            $totalOut = $wallet->transactions()
                ->where('type', 'out')
                ->sum('amount');
                
            $cashboxBalance = $totalIn - $totalOut;
            
            // تحديث رصيد المحفظة إذا كان هناك فرق
            if ($wallet->balance != $cashboxBalance) {
                $wallet->balance = $cashboxBalance;
                $wallet->save();
            }
        }
        
        // Get all cars
        $cars = Car::select('id', 'name', 'no')->get();
        
        // Get customer info if customer_id is provided
        $customer = null;
        if ($request->customer_id) {
            $customer = User::find($request->customer_id);
        }
        
        // Get all customer wallets with their balances
        $clientTypeId = UserType::where('name', 'client')->first()->id ?? null;
        $customerWallets = [];
        $totalCustomerWallets = 0;
        $allCustomers = [];
        
        if ($clientTypeId) {
            $customers = User::where('type_id', $clientTypeId)
                ->whereNotIn('email', ['in@account.com', 'out@account.com', 'transfers@account.com'])
                ->where('email', '!=', 'a@a.com')
                ->with('wallet')
                ->orderBy('name', 'asc')
                ->get();
            
            // Pass all customers for the list
            $allCustomers = $customers->map(function($cust) {
                return [
                    'id' => $cust->id,
                    'name' => $cust->name,
                    'email' => $cust->email,
                ];
            });
            
            foreach ($customers as $cust) {
                if ($cust->wallet) {
                    // حساب الرصيد من المعاملات
                    $totalIn = $cust->wallet->transactions()->where('type', 'in')->sum('amount');
                    $totalOut = $cust->wallet->transactions()->where('type', 'out')->sum('amount');
                    $balance = $totalIn - $totalOut;
                    
                    // تحديث رصيد المحفظة
                    if ($cust->wallet->balance != $balance) {
                        $cust->wallet->balance = $balance;
                        $cust->wallet->save();
                    }
                    
                    $customerWallets[] = [
                        'id' => $cust->wallet->id,
                        'customer_id' => $cust->id,
                        'customer_name' => $cust->name,
                        'customer_email' => $cust->email,
                        'balance' => $balance
                    ];
                    
                    $totalCustomerWallets += $balance;
                }
            }
        }
        
        // حساب رأس المال = سعر الشراء + جميع المصاريف لجميع السيارات + مصاريف عامة
        $totalCarsPrice = Car::selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
                       ->value('total') ?? 0;
        $totalExpenses = Expenses::sum('amount') ?? 0;
        $capital = $totalCarsPrice + $totalExpenses;
        
        return Inertia::render('SimpleCash', [
            'url' => $this->url,
            'cashboxBalance' => $cashboxBalance,
            'cars' => $cars,
            'customer_id' => $request->customer_id,
            'customer' => $customer,
            'customerWallets' => $customerWallets,
            'allCustomers' => $allCustomers,
            'totalCustomerWallets' => $totalCustomerWallets,
            'capital' => $capital,
            'totalCarsPrice' => $totalCarsPrice,
            'totalExpenses' => $totalExpenses
        ]);
    }
}
