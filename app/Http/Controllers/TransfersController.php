<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use App\Models\Transfers;
use App\Models\User;
use App\Models\Car;
use App\Models\Company;
use App\Models\Name;
use App\Models\CarModel;
use App\Models\Color;
use App\Models\Wallet;
use App\Models\UserType;
use App\Models\ExpensesType;
use Illuminate\Support\Facades\DB;
use App\Models\Transactions;
use App\Models\Expenses;
use App\Models\Investment;

use Carbon\Carbon;
use Inertia\Inertia;

class TransfersController extends Controller
{
    public function __construct(AccountingController $accountingController)
    {
    $this->accountingController = $accountingController;
    $this->url = env('FRONTEND_URL');
    $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
    $this->userSeles =  UserType::where('name', 'seles')->first()->id;
    $this->userClient =  UserType::where('name', 'client')->first()->id;
    $this->userAccount =  UserType::where('name', 'account')->first()->id;

    $this->mainAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','main@account.com')->first();
    $this->inAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','in@account.com')->first();
    $this->outAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','out@account.com')->first();
    $this->transfersAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','transfers@account.com')->first();
    $this->outSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-out')->first();
    $this->debtSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-debt')->first();
    }
    public function __invoke(Request $request)
    {
        return $this->index($request);
    }
    
    public function index(Request $request)
    {
        // حساب إجمالي الدخل والخرج للصندوق
        $totalIncome = Transactions::where('type', 'in')->sum('amount');
        $totalExpenses = Transactions::where('type', 'out')->sum('amount');
        $cashboxBalance = $totalIncome - $totalExpenses;
        
        // حساب دخل الصندوق فقط
        $totalFundIncome = Transactions::where('type', 'in')
            ->whereHas('wallet.user', function($query) {
                $query->where('email', 'main@account.com');
            })->sum('amount');
        
        // حساب الدين
        $totalDebt = Car::where('results', '!=', 0)->sum('pay_price') - 
                    Car::where('results', '!=', 0)->sum('paid_amount_pay');
        
        // حساب رأس المال (مجموع سعر الشراء + جميع المصاريف لجميع السيارات)
        $totalCapital = Car::sum('purchase_price') + 
                       Car::sum('erbil_exp') + 
                       Car::sum('erbil_shipping') + 
                       Car::sum('dubai_exp') + 
                       Car::sum('dubai_shipping');
        
        // حساب إحصائيات المستخدمين
        $totalUserIn = Transactions::where('morphed_type', 'App\Models\User')
            ->where('type', 'user_in')
            ->sum('amount');
        $totalUserOut = Transactions::where('morphed_type', 'App\Models\User')
            ->where('type', 'user_out')
            ->sum('amount');
        
        // حساب المدفوع من الصندوق (المبلغ المدفوع من القاسة)
        $totalPaidFromCashbox = Transactions::where('type', 'out')
            ->whereHas('wallet.user', function($query) {
                $query->where('email', 'out@account.com');
            })->sum('amount');
        
        // حساب رأس المال المتبقي (رأس المال - المدفوع من الصندوق)
        $remainingCapital = $totalCapital - $totalPaidFromCashbox;
        
        // حساب إجمالي المدفوعات من السيارات المباعة فقط
        $totalCarPayments = Car::where('results', '!=', 0)->sum('paid_amount_pay');
        
        // حساب إجمالي تكلفة السيارات المباعة فقط
        $totalSoldCarsCost = Car::where('results', '!=', 0)
            ->selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
            ->value('total') ?? 0;
        
        // حساب الربح الصحيح (المدفوعات من السيارات المباعة - تكلفة السيارات المباعة)
        $totalProfit = $totalCarPayments - $totalSoldCarsCost;
        
        // جلب المستخدمين الذين لديهم محافظ
        $usersWithWallets = User::whereHas('wallet')
            ->where('show_wallet', true)
            ->with(['wallet', 'userType'])
            ->get();

        // حساب الاستثمارات النشطة
        $totalActiveInvestments = Investment::getTotalActiveInvestments();
        $activeInvestors = Investment::getActiveInvestors();
        
        // تحديث النسب والأرباح للاستثمارات الموجودة
        $this->updateInvestmentPercentagesAndProfits();
        
        // حساب رأس المال بعد الاستثمارات (رأس المال - الاستثمارات النشطة)
        $capitalAfterInvestments = max(0, $totalCapital - $totalActiveInvestments);
        
        // حساب رأس المال المتبقي بعد الاستثمارات والمدفوعات
        $finalRemainingCapital = max(0, $capitalAfterInvestments - $totalPaidFromCashbox);
        
        // حساب مؤشرات الألوان
        $capitalStatus = $finalRemainingCapital > 0 ? 'needs_investment' : 'sufficient_investment';
        $profitStatus = $totalProfit > 0 ? 'profit' : 'loss';
        
        // تجميع الاستثمارات حسب المستخدم
        $allActiveInvestments = Investment::where('status', 'active')->with('user')->get();
        $groupedInvestments = $allActiveInvestments->groupBy('user_id');
        $groupedInvestors = $groupedInvestments->map(function ($investments) {
            $firstInvestment = $investments->first();
            return [
                'user' => $firstInvestment->user,
                'totalAmount' => $investments->sum('amount'),
                'totalPercentage' => $investments->sum('percentage'),
                'totalProfitShare' => $investments->sum('profit_share'),
                'investments' => $investments,
                'investmentCount' => $investments->count()
            ];
        })->values();
        
        $data = [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'cashboxBalance' => $cashboxBalance,
            'totalFundIncome' => $totalFundIncome,
            'totalDebt' => $totalDebt,
            'totalCapital' => $totalCapital,
            'totalUserIn' => $totalUserIn,
            'totalUserOut' => $totalUserOut,
            'totalPaidFromCashbox' => $totalPaidFromCashbox,
            'remainingCapital' => $remainingCapital,
            'totalCarPayments' => $totalCarPayments,
            'totalProfit' => $totalProfit,
            'totalSoldCarsCost' => $totalSoldCarsCost,
            'usersWithWallets' => $usersWithWallets,
            'totalActiveInvestments' => $totalActiveInvestments,
            'activeInvestors' => $activeInvestors,
            'groupedInvestors' => $groupedInvestors,
            'capitalAfterInvestments' => $capitalAfterInvestments,
            'finalRemainingCapital' => $finalRemainingCapital,
            'capitalStatus' => $capitalStatus,
            'profitStatus' => $profitStatus,
            'mainAccount' => $this->mainAccount,
            'inAccount' => $this->inAccount,
            'outAccount' => $this->outAccount,
            'transfersAccount' => $this->transfersAccount,
            'outSupplier' => $this->outSupplier,
            'debtSupplier' => $this->debtSupplier,
        ];
        
        return Inertia::render('Transfers', $data);
    }
    
    public function getTransactions(Request $request)
    {
        $type = $request->get('type', ''); // 'in', 'out', or empty for all
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = Transactions::with(['wallet.user', 'morphed', 'user'])
            ->orderBy('created_at', 'desc');
        
        if ($type) {
            $query->where('type', $type);
        }
        
        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }
        
        $transactions = $query->paginate(20);
        
        // إضافة معلومات السيارة والزبون للمعاملات المرتبطة بالسيارات
        foreach ($transactions->items() as $transaction) {
            if ($transaction->morphed_type === 'App\Models\Car' && $transaction->morphed) {
                $transaction->car_name = $transaction->morphed->name ?? 'غير محدد';
                $transaction->car_pin = $transaction->morphed->pin ?? 'غير محدد';
                $transaction->client_name = $transaction->morphed->client->name ?? 'غير محدد';
            }
            
            // إضافة معلومات المستخدم للمعاملات الشخصية
            if (in_array($transaction->type, ['user_in', 'user_out']) && $transaction->morphed_type === 'App\Models\User' && $transaction->morphed) {
                $transaction->user_name = $transaction->morphed->name ?? 'غير محدد';
            }
        }
        
        return Response::json([
            'transactions' => $transactions,
            'stats' => [
                'total_income' => Transactions::where('type', 'in')->sum('amount'),
                'total_expenses' => Transactions::where('type', 'out')->sum('amount'),
                'balance' => Transactions::where('type', 'in')->sum('amount') - Transactions::where('type', 'out')->sum('amount')
            ]
        ], 200);
    }
    
    public function deleteTransaction($transactionId)
    {
        $transaction = Transactions::find($transactionId);
        if (!$transaction) {
            return response()->json(['error' => 'المعاملة غير موجودة'], 404);
        }
        
        // إذا كانت المعاملة مرتبطة بسيارة، نحتاج لتحديث السيارة
        if ($transaction->morphed_type === 'App\Models\Car' && $transaction->morphed) {
            $car = $transaction->morphed;
            
            if ($transaction->type === 'in') {
                // إذا كانت دفعة دخول، نطرحها من المبلغ المدفوع
                $car->paid_amount_pay = max(0, $car->paid_amount_pay - $transaction->amount);
                
                // إعادة حساب حالة السيارة
                $totalCost = $car->purchase_price + $car->erbil_exp + $car->erbil_shipping + $car->dubai_exp + $car->dubai_shipping;
                
                if ($car->paid_amount_pay >= $totalCost && $car->pay_price) {
                    $car->results = 2; // مدفوعة بالكامل
                } else if ($car->paid_amount_pay > 0 && $car->pay_price) {
                    $car->results = 1; // مدفوعة جزئياً
                } else {
                    $car->results = 0; // غير مدفوعة
                }
                
                $car->save();
            }
        }
        
        $transaction->delete();
        
        return response()->json(['success' => 'تم حذف المعاملة بنجاح'], 200);
    }
    
    public function getIndexAccountsSelas()
    { 
        $user_id = $_GET['user_id'] ?? 0;
        $sales = User::with('wallet')->where('id', $user_id)->first();
        $transactions = Transactions ::where('wallet_id', $sales?->wallet?->id);

        $data = $transactions->paginate(10);
        $profile_count = Car::where('user_id', $sales?->id)->where('results',1)->count();
        // Additional logic to retrieve sales data
        $salesData = [
            'totalAmount' =>  $transactions->sum('amount'),
            'count' => $profile_count,
            'total' => $data?->total(),
            'to' => $data?->currentPage(),
            'from' => $data?->lastPage(),
            'current_page' => $data?->currentPage(),
            'per_page' => $data?->perPage(),
            'last_page' => $data?->lastPage(),
            'data' => $data?->items(),
            'sales'=>$sales,
            'date'=> Carbon::now()->format('Y-m-d')
        ];
        return Response::json($salesData, 200);
    }
    public function getcountComp(Request $request)
    {
        $profile=  Car::all();
        $start = $request->get('start');
        $end = $request->get('end');
        if($start && $end ){
            $countComp =Car::whereBetween('created_at', [$start, $end])->where('client_id','!=',null)->count();
        }
        else{
            $countComp =Car::where('client_id','!=',null)->count();  
        }
        return response()->json($countComp); 
    }

    // إضافة استثمار جديد
    public function addInvestment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $wallet = $user->getWalletOrCreate();

            // التحقق من وجود رصيد كافي
            if ($wallet->balance < $request->amount) {
                return response()->json(['error' => 'الرصيد غير كافي في المحفظة'], 400);
            }

            // خصم المبلغ من المحفظة
            $wallet->decrement('balance', $request->amount);

            // إنشاء الاستثمار
            $investment = Investment::create([
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'note' => $request->note,
                'status' => 'active'
            ]);

            // حساب النسبة المئوية
            $totalCapital = Car::sum('purchase_price') + 
                           Car::sum('erbil_exp') + 
                           Car::sum('erbil_shipping') + 
                           Car::sum('dubai_exp') + 
                           Car::sum('dubai_shipping');
            
            $investment->calculatePercentage($totalCapital);
            
            // حساب نصيب الربح
            $totalProfit = Car::sum('paid_amount_pay') - $totalCapital;
            $investment->calculateProfitShare($totalProfit);

            // تسجيل المعاملة
            Transactions::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'investment',
                'description' => 'استثمار من المحفظة - ' . ($request->note ?? 'بدون ملاحظات'),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الاستثمار بنجاح',
                'investment' => $investment->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'حدث خطأ في إضافة الاستثمار: ' . $e->getMessage()], 500);
        }
    }

    // سحب استثمار
    public function withdrawInvestment(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $investment = Investment::findOrFail($id);
            
            if ($investment->status !== 'active') {
                return response()->json(['error' => 'الاستثمار غير نشط'], 400);
            }

            $user = $investment->user;
            $wallet = $user->getWalletOrCreate();

            // إرجاع المبلغ إلى المحفظة
            $wallet->increment('balance', $investment->amount);

            // تحديث حالة الاستثمار
            $investment->update(['status' => 'withdrawn']);

            // تسجيل المعاملة
            Transactions::create([
                'wallet_id' => $wallet->id,
                'amount' => $investment->amount,
                'type' => 'investment_withdrawal',
                'description' => 'سحب استثمار - ' . ($investment->note ?? 'بدون ملاحظات'),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم سحب الاستثمار بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'حدث خطأ في سحب الاستثمار: ' . $e->getMessage()], 500);
        }
    }

    // تحديث النسب والأرباح للاستثمارات الموجودة
    private function updateInvestmentPercentagesAndProfits()
    {
        $totalCapital = Car::sum('purchase_price') + 
                       Car::sum('erbil_exp') + 
                       Car::sum('erbil_shipping') + 
                       Car::sum('dubai_exp') + 
                       Car::sum('dubai_shipping');
        
        $totalProfit = Car::sum('paid_amount_pay') - $totalCapital;
        
        $activeInvestments = Investment::where('status', 'active')->get();
        
        foreach ($activeInvestments as $investment) {
            $investment->calculatePercentage($totalCapital);
            $investment->calculateProfitShare($totalProfit);
        }
    }
    public function addTransfers()
    {
        $maxNo = Transfers::max('no');
        $no = $maxNo + 1;
        $tran=Transfers::create([
            'no'=>$no,
            'user_id' =>$_GET['user_id'],
            'amount'=> $_GET['amount'],
            'note'=>$_GET['note'],
             ]);
        if($tran->id){
            $desc=trans('text.addTransfer').' '.$tran->amount.' '.($tran->currency ?? '$').' || '.$_GET['note']??'';
            $this->accountingController->increaseWallet($tran->amount, $desc,$this->transfersAccount->id,$tran->id,'App\Models\Transactions');
        }
        return Response::json('ok', 200);    
    }
    public function editCar(Request $request)
    {
        $maxNo = Car::max('no');
        $no = $maxNo + 1;
        $car=Car::updateOrCreate(['id' => $_GET['id']],[
            'company_id' =>$_GET['company_id'],
            'name_id'=> $_GET['name_id'],
            'model_id'=> $_GET['model_id'],
            'color_id'=> $_GET['color_id'],
            'pin'=> $_GET['pin'],
            'purchase_data'=> $_GET['purchase_data'],
            'purchase_price'=> $_GET['purchase_price'],
            'paid_amount'=> $_GET['paid_amount'],
            'note'=> $_GET['note']??'',
            'user_id'=> auth()->user()->id,
            'no'=>$no
             ]);
        
            return Response::json('ok', 200);    
    }
    public function GenExpenses (){
        $expenses = Expenses::create([
            'user_id' => $_GET['user_id'],
            'reason' => $_GET['reason'],
            'amount' => $_GET['amount'],
            'note' => $_GET['note'],
        ]);
        if($expenses->id){
            $desc=trans('text.genExpenses');
            $this->accountingController->increaseWallet($expenses->amount, $desc,$this->outAccount->id);
            $this->accountingController->decreaseWallet($expenses->amount, $desc,$this->mainAccount->id);
        }
    }
    public function payCar(Request $request)
    {   
        if( $_GET['client_name']){
            $client = new User;
            $client->name = $_GET['client_name'];
            $client->phone = $_GET['client_phone'];
            $client->save();
            Wallet::create(['user_id' => $user->id]);
        }

        $car=Car::updateOrCreate(['id' => $_GET['id']],[
            'note_pay' =>$_GET['note_pay'],
            'client_id'=> ($client??'') ? $client->id : $_GET['client_id'],
            'pay_data'=> Carbon::now()->format('Y-m-d'),
            'pay_price'=> $_GET['pay_price'],
            'paid_amount_pay' =>  $_GET['paid_amount_pay'],
            'results'=>1
             ]);
        if($car->id){
                $desc=trans('text.buyCar').' '.$car->pay_price.trans('text.payDone').$car->paid_amount_pay;
                $this->accountingController->decreaseWallet($car->paid_amount_pay, $desc,$this->mainAccount->id);
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->inAccount->id);
            }
            return Response::json('ok', 200);    
    }
    public function getIndexCar()
    {
        $data =  Car::with('carmodel')->with('name')->with('color')->with('company')->with('client');
        $type =$_GET['type'] ?? '';
        if($type){
        $data =    $data->where('results', $type);
        }
        if($type==0){
            $data =    $data->where('results', $type);
        }
        $data =$data->orderByRaw('CAST(no AS UNSIGNED) DESC')->paginate(10);
        return Response::json($data, 200);
    }
    
}
