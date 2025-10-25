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

class SimpleAccountingController extends Controller
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
        // Get account balances
        $inAccountBalance = $this->inAccount && $this->inAccount->wallet ? $this->inAccount->wallet->balance : 0;
        $outAccountBalance = $this->outAccount && $this->outAccount->wallet ? $this->outAccount->wallet->balance : 0;
        $transfersAccountBalance = $this->transfersAccount && $this->transfersAccount->wallet ? $this->transfersAccount->wallet->balance : 0;
        
        // Get cars summary
        $totalCars = Car::count();
        $soldCars = Car::whereNotNull('client_id')->count();
        $carsInStock = Car::whereNull('client_id')->count();
        
        // Calculate total cost
        $totalCost = Car::selectRaw('
            SUM(
                purchase_price + COALESCE(dubai_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(erbil_exp, 0)
            ) as total_cost
        ')->value('total_cost') ?: 0;
        
        // Get all cars
        $cars = Car::select('id', 'name', 'no')->get();
        
        // Get customer info if customer_id is provided
        $customer = null;
        if ($request->customer_id) {
            $customer = User::find($request->customer_id);
        }
        
        return Inertia::render('SimpleAccounting', [
            'url' => $this->url,
            'inAccountBalance' => $inAccountBalance,
            'outAccountBalance' => $outAccountBalance,
            'transfersAccountBalance' => $transfersAccountBalance,
            'totalCars' => $totalCars,
            'soldCars' => $soldCars,
            'carsInStock' => $carsInStock,
            'totalCost' => $totalCost,
            'customer_id' => $request->customer_id,
            'customer' => $customer
        ]);
    }
}
