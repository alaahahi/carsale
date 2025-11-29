<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
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
use App\Models\CarFieldHistory;

use App\Helpers\UploadHelper;
use App\Helpers\TenantDataHelper;

use Carbon\Carbon;

use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $accountingController;
    protected $url;
    protected $userAdmin;
    protected $userSeles;
    protected $userClient;
    protected $userAccount;
    protected $inAccount;
    protected $outAccount;
    protected $transfersAccount;

    public function __construct(AccountingController $accountingController)
    {
        $this->accountingController = $accountingController;
        $this->url = env('FRONTEND_URL');
        
        // تأجيل جلب بيانات الـ tenant لما بعد تهيئة التيننسي عبر ميدلوير الكنترولر
        $this->middleware(function ($request, $next) {
            $userTypeIds = TenantDataHelper::getUserTypeIds();
            $this->userAdmin = $userTypeIds['admin'];
            $this->userSeles = $userTypeIds['seles'];
            $this->userClient = $userTypeIds['client'];
            $this->userAccount = $userTypeIds['account'];

            $accountingUsers = TenantDataHelper::getAccountingUsers();
            $this->inAccount = $accountingUsers['in'];
            $this->outAccount = $accountingUsers['out'];
            $this->transfersAccount = $accountingUsers['transfers'];
            return $next($request);
        });
    }

    private function ensureInitialized(): void
    {
        if (!$this->userClient || !$this->inAccount || !$this->outAccount || !$this->transfersAccount) {
            $userTypeIds = TenantDataHelper::getUserTypeIds();
            $this->userAdmin = $userTypeIds['admin'];
            $this->userSeles = $userTypeIds['seles'];
            $this->userClient = $userTypeIds['client'];
            $this->userAccount = $userTypeIds['account'];

            $accounts = TenantDataHelper::getAccountingUsers();
            // إنشاء الحسابات الأساسية إذا كانت غير موجودة
            if (!$accounts['in'] && $this->userAccount) {
                $this->inAccount = \App\Models\User::firstOrCreate(
                    ['email' => 'in@account.com', 'type_id' => $this->userAccount],
                    ['name' => 'حساب الدخل', 'password' => bcrypt('password'), 'show_wallet' => false]
                );
                $this->inAccount->getWalletOrCreate();
            } else {
                $this->inAccount = $accounts['in'];
            }

            if (!$accounts['out'] && $this->userAccount) {
                $this->outAccount = \App\Models\User::firstOrCreate(
                    ['email' => 'out@account.com', 'type_id' => $this->userAccount],
                    ['name' => 'حساب الخرج', 'password' => bcrypt('password'), 'show_wallet' => false]
                );
                $this->outAccount->getWalletOrCreate();
            } else {
                $this->outAccount = $accounts['out'];
            }

            if (!$accounts['transfers'] && $this->userAccount) {
                $this->transfersAccount = \App\Models\User::firstOrCreate(
                    ['email' => 'transfers@account.com', 'type_id' => $this->userAccount],
                    ['name' => 'حساب التحويلات', 'password' => bcrypt('password'), 'show_wallet' => false]
                );
                $this->transfersAccount->getWalletOrCreate();
            } else {
                $this->transfersAccount = $accounts['transfers'];
            }
        }
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
    
    public function stats()
    {
        $results = null;
       // $client = new Client( $this->url, 'masterKey');
       // $results = $client->stats();
        //dd($results);
        return Inertia::render('dashboard', ['url'=>$this->url]);   

    }
    public function index(Request $request)
    {
        $this->ensureInitialized();
        $authUser = auth()->user();
        $results = null;
        $user=   User::where('type_id', $this->userSeles)->get();
        $car = Car::all();
        $carUser=  $car->where('user_id', $authUser->id)->count();
       
        $client = User::where('type_id', $this->userClient)->get();
    
        $expenses=ExpensesType::all(); 
        
        // جلب الدفعات الأخيرة
        $recentPayments = Transactions::with(['wallet.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $grandTotal = \App\Models\Car::selectRaw('
        SUM(
                    purchase_price + COALESCE(dubai_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(erbil_exp, 0)
                ) as grand_total
            ')
            ->value('grand_total'); // Directly retrieve the value


        $systemConfig = TenantDataHelper::getSystemConfig();
        $externalMerchantIds = $systemConfig['external_merchant_ids'] ?? [];
        
        return Inertia::render('Dashboard', [
            'url'=>$this->url,
            'transfersAccount'=>$this->transfersAccount,
            'outAccount'=>$this->outAccount,
            'inAccount'=>$this->inAccount,
            'expenses'=> $expenses,
            'user'=> $user,
            'client'=>$client,
            'carCount'=> $car->count(),
            'working'=> $car->where('client_id',null)->count(),
            'systemConfig' => $systemConfig,
            'externalMerchantIds' => $externalMerchantIds
        ]);   

    }
    public function totalInfo(Request $request)
    {
        $this->ensureInitialized();
        $expenses=ExpensesType::all();
        $car = Car::all();

        $data = [
        'transfersAccount'=>$this->transfersAccount && $this->transfersAccount->wallet ? $this->transfersAccount->wallet->balance : 0,
        'outAccount'=>$this->outAccount && $this->outAccount->wallet ? $this->outAccount->wallet->balance : 0,
        'inAccount'=>$this->inAccount && $this->inAccount->wallet ? $this->inAccount->wallet->balance : 0,
        'allCars'=>$car->count(),
        'carsInStock'=>$car->where('client_id',null)->count()
        ];
        return response()->json(['data'=>$data]); 

    }
    public function client(Request $request)
    {
        $client = User::where('type_id', $this->userClient)->with('wallet')->paginate(10);
        return response()->json($client); 
    }
    public function getcountComp(Request $request)
    {
        $profile=  Car::all();
        $start = $request->get('start');
        $end = $request->get('end');
        if($start && $end ){
            // عد السيارات المباعة (التي لها client_id)
            $countComp =Car::whereBetween('created_at', [$start, $end])->where('client_id','!=',null)->count();
        }
        else{
            // عد السيارات المباعة (التي لها client_id)
            $countComp =Car::where('client_id','!=',null)->count();  
        }
        return response()->json($countComp); 
    }
    public function addCar(Request $request)
    {
        $car_id = $request->id ?? 0;
        $maxNo = Car::max(DB::raw('CAST(no AS UNSIGNED)'));
        $no = $car_id ? $request->no : ($maxNo ?? 0) + 1;
        $exp_note=$request->exp_note;
        $images = [];
        if ($request->image) {
            foreach ($request->image as $image) {
                $imageName = $image->getClientOriginalName() . $no;
                $filename = pathinfo($imageName, PATHINFO_FILENAME);
                $imagePath = UploadHelper::upload('image', $image, $filename, 'storage/car');
                $images[] = $imagePath;
            }
        }
    
        if (!$car_id) {
            $car = Car::create([
                'name' => $request->name,
                'model' => $request->model,
                'color' => $request->color,
                'pin' => $request->pin,
                'source' => $request->source,
                'purchase_data' => $request->purchase_data,
                'purchase_price' => $request->purchase_price,
                'note' => $request->note ?? '',
                'image' => $images ? json_encode($images) : null,
                 'erbil_exp' => $request->erbil_exp,
                'erbil_shipping' => $request->erbil_shipping,
                'dubai_exp' => $request->dubai_exp,
                'dubai_shipping' => $request->dubai_shipping,
                'no' => $no,
                'results' => 0
            ]);
            
            // إضافة دفعة من نوع out عند إضافة السيارة
            if ($car->id) {
                // الحصول على حساب الخرج أو إنشاء واحد جديد
                $outAccount = User::where('email', 'out@account.com')->first();
                
                if (!$outAccount) {
                    // إنشاء حساب الخرج إذا لم يكن موجوداً
                    $accountTypeId = TenantDataHelper::getUserTypeId('account');
                    if ($accountTypeId) {
                        $outAccount = User::create([
                            'name' => 'حساب الخرج',
                            'email' => 'out@account.com',
                            'password' => bcrypt('password'),
                            'type_id' => $accountTypeId,
                            'show_wallet' => false,
                        ]);
                        
                        // إنشاء wallet للحساب
                        Wallet::create([
                            'user_id' => $outAccount->id,
                            'balance' => 0,
                        ]);
                    }
                }
                
                if ($outAccount) {
                    $totalCost = $car->purchase_price + $car->erbil_exp + $car->erbil_shipping + $car->dubai_exp + $car->dubai_shipping;
                    $description = 'شراء سيارة - ' . $car->name . ' - رقم: ' . $car->pin . ' - سنة: ' . $car->model . ' - لون: ' . $car->color;
                    
                    // إنشاء wallet إذا لم يكن موجوداً
                    $wallet = $outAccount->getWalletOrCreate();
                    
                    Transactions::create([
                        'amount' => $totalCost,
                        'type' => 'out',
                        'description' => $description,
                        'wallet_id' => $wallet->id,
                        'morphed_id' => $car->id,
                        'morphed_type' => 'App\Models\Car',
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        } else {
            $car = Car::find($car_id);
    
            // List of fields to track
            $trackedFields = ['erbil_exp', 'erbil_shipping', 'dubai_exp', 'dubai_shipping'];
            foreach ($trackedFields as $field) {
                if ($request->has($field) && $car->$field != $request->$field) {
                    // Log the change in car_field_histories table
                    DB::table('car_field_histories')->insert([
                        'car_id' => $car->id,
                        'field' => $field,
                        'old_value' => $car->$field,
                        'new_value' => $request->$field,
                         'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
    
            // Update car fields
            $car->update([
                'company_id' => $request->company_id,
                'name_id' => $request->name_id,
                'model_id' => $request->model_id,
                'color_id' => $request->color_id,
                'name' => $request->name,
                'model' => $request->model,
                'color' => $request->color,
                'pin' => $request->pin,
                'purchase_data' => $request->purchase_data,
                'purchase_price' => $request->purchase_price,
                'note' => $request->note ?? '',
                'image' => $images ? json_encode($images) : null,
                'erbil_exp' => $request->erbil_exp,
                'erbil_shipping' => $request->erbil_shipping,
                'dubai_exp' => $request->dubai_exp,
                'dubai_shipping' => $request->dubai_shipping,
                'source' => $request->source,
                'no' => $no
            ]);
            
            // إعادة حساب حالة السيارة بعد التحديث
            $car->refresh(); // تحديث البيانات من قاعدة البيانات
            $totalCost = $car->purchase_price + $car->erbil_exp + $car->erbil_shipping + $car->dubai_exp + $car->dubai_shipping;
            
            // إذا كانت السيارة مبيعة (results = 2) وتغيرت التكلفة الإجمالية
            if ($car->results == 2 && $car->pay_price) {
                // إعادة حساب حالة السيارة حسب المبلغ المدفوع
                if ($car->paid_amount_pay >= $totalCost) {
                    $car->results = 2; // مدفوعة بالكامل
                } else if ($car->paid_amount_pay > 0) {
                    $car->results = 1; // مدفوعة جزئياً
                } else {
                    $car->results = 0; // غير مبيعة
                }
                $car->save();
            }
        }
    
        return response()->json('ok', 200);
    }
    
    public function getIndexExpenses () {
        $expenses = Expenses::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Add car information to expenses
        $expenses->getCollection()->transform(function ($expense) {
            // Try to extract car info from reason
            if (strpos($expense->reason, 'مصروفات السيارة:') !== false) {
                $expense->car_name = str_replace('مصروفات السيارة: ', '', $expense->reason);
            } else {
                $expense->car_name = null;
            }
            
            // Add expense type name if available
            $expense->expense_type_name = 'مصروفات عامة';
            
            return $expense;
        });
        
        return Response::json($expenses, 200);    
    }
    public function addGenExpenses (Request $request){
        $user_id = auth()->id();
        
        // Get car name if car_id is provided
        $car_name = '';
        if ($request->car_id) {
            $car = Car::find($request->car_id);
            $car_name = $car ? $car->name . ' - رقم ' . $car->no : '';
        }
        
        // Get customer name if customer_id is provided
        $customer_name = '';
        if ($request->customer_id) {
            $customer = User::find($request->customer_id);
            $customer_name = $customer ? $customer->name : '';
        }
        
        $reason = $request->reason ?? 'مصروفات عامة';
        if ($car_name) {
            $reason = "مصروفات السيارة: " . $car_name;
        }
        if ($customer_name) {
            $reason .= " - الزبون: " . $customer_name;
        }
        
        $expenses = Expenses::create([
            'user_id' => $user_id,
            'reason' => $reason,
            'amount' => $request->amount ?? 0,
            'note' => $request->note ?? '',
        ]);
        
        if($expenses->id){
            $desc = $reason;
            $this->accountingController->increaseWallet($expenses->amount, $desc, $this->outAccount->id, $user_id, 'App\Models\User', $user_id);
        }
        return Response::json('ok', 200);    

    }
    public function GenExpenses (){
        $user_id = auth()->id(); // الحصول على المستخدم من الجلسة
        
        // فحص وجود الحسابات المطلوبة
        if (!$this->outAccount) {
            return Response::json(['error' => 'الحسابات المطلوبة غير موجودة'], 500);
        }
        
        $amount = $_GET['amount'] ?? 0;
        $reason = $_GET['reason'] ?? '';
        $note = $_GET['note'] ?? '';
        
        $expenses = Expenses::create([
            'user_id' => $user_id,
            'reason' => $reason,
            'amount' => $amount,
            'note' => $note,
        ]);
        
        if($expenses->id){
            $desc=trans('text.genExpenses');
            $this->accountingController->increaseWallet($expenses->amount, $desc,$this->outAccount->id,$user_id,'App\Models\User', $user_id);
            
            return Response::json([
                'success' => true,
                'message' => 'تم إضافة المصروف العام بنجاح!',
                'amount' => $amount,
                'reason' => $reason,
                'note' => $note
            ], 200);
        }
        
        return Response::json(['error' => 'فشل في إضافة المصروف العام'], 500);
    }
    public function addExpenses (){

        $user_id=$_GET['user_id']??0;
        $car_id=$_GET['car_id']??0;        
        $expenses_id=$_GET['expenses_id']??0;
        $expens_amount=$_GET['expens_amount']??0;
        $note=$_GET['note']??'';
        
        $expensesType =ExpensesType::find($expenses_id);
        $car = Car::with('company')->with('name')->find($car_id);
        switch ($expensesType->id) {
            case 1:
                $car->increment('dubai_exp',$expens_amount);
                $desc=trans('text.expensesExpDubai').$expens_amount.'$ '.$car->company?->name.' '.$car->name?->name.' '.$note;
                if ($this->outAccount) {
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                }
                break;
            
            case 2:
                $car->increment('erbil_exp',$expens_amount);
                $desc=trans('text.expensesExpErbil').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                if ($this->outAccount) {
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                }
                break;

            case 3:
                $car->increment('erbil_shipping',$expens_amount);
                $desc=trans('text.expensesShippingErbil').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                if ($this->outAccount) {
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                }
                break;
            
            case 4:
                $car->increment('dubai_shipping',$expens_amount);
                $desc=trans('text.expensesShippingDubai').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                if ($this->outAccount) {
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                }
                break;

            case 5:
                if(($car->purchase_price - $car->paid_amount) ==0){
                    return Response::json('error', 500);       
                }
                else{
            
                    $car->increment('paid_amount',$expens_amount);
                    $desc=trans('text.expensesExpPay').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name;
                    if ($this->outAccount) {
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                }
          

                }

                break;

            default:
                
                break;
        }


     return Response::json('ok', 200);    
        
    }
    public function payCar(Request $request)
    {
        $authUser = auth()->user();   
        $client_id =$request->client_id;
        $pay_price =(int)$request->pay_price??0;
        $paid_amount_pay =(int)$request->paid_amount_pay??0;

        if( $client_id==0){
            // إنشاء زبون جديد
            $client = new User;
            $client->name = $request->client_name;
            $client->phone = $request->client_phone;
            $client->type_id = $this->userClient;
            $client->save();
            
            // إنشاء محفظة للزبون الجديد برصيد المبلغ المتبقي
            Wallet::create([
                'user_id' => $client->id,
                'balance' => $pay_price - $paid_amount_pay
            ]);
            
            $client_id = $client->id;
            
            \Log::info('New client created', [
                'client_id' => $client_id,
                'client_name' => $request->client_name,
                'client_phone' => $request->client_phone,
                'initial_balance' => $pay_price - $paid_amount_pay
            ]);
        }else{
            // تحديث محفظة الزبون الموجود
            $wallet = Wallet::where('user_id',$client_id)->first();
            if($wallet) {
            $wallet->increment('balance',$pay_price-$paid_amount_pay); 
            }
        }

        $car=Car::find($request->id);
        if($car->id){
                // تحديد حالة السيارة بناءً على المبلغ المدفوع
                $carResults = 1; // افتراضياً مدفوعة جزئياً
                if ($pay_price > 0 && $paid_amount_pay >= $pay_price) {
                    $carResults = 2; // مدفوعة بالكامل
                } else if ($paid_amount_pay == 0 && $pay_price == 0) {
                    $carResults = 0; // غير مدفوعة
                }
                
                $car->update([
                'note_pay' =>$request->note_pay,
                'client_id'=> $client_id ,
                'pay_data'=> Carbon::now()->format('Y-m-d'),
                'pay_price'=>$pay_price,
                'paid_amount_pay' =>  $paid_amount_pay,
                'results'=> $carResults
                 ]);
                
                // تحديث السيارة للتأكد من الحصول على البيانات المحدثة
                $car->refresh();
                
                $desc=trans('text.buyCar').' '.$car->pay_price.trans('text.payDone').$car->paid_amount_pay;
                if ($this->inAccount) {
                    $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
                }
                // استخدام outAccount لجميع المعاملات الخارجية بدلاً من debtAccount منفصل
                if($pay_price-$paid_amount_pay > 0 && $this->outAccount){
                    $debtDesc = 'دين عميل - ' . $desc;
                    $this->accountingController->increaseWallet($pay_price-$paid_amount_pay, $debtDesc,$this->outAccount->id,$car->id,'App\Models\Car');
                }
                
                // إذا كانت السيارة مدفوعة بالكامل، قم بتوزيع الربح
                if ($carResults == 2 && $pay_price > 0) {
                    try {
                        $car->refresh(); // تحديث بيانات السيارة
                        $car->distributeProfitToInvestors();
                        \Log::info('Profit distributed automatically for car', [
                            'car_id' => $car->id,
                            'car_no' => $car->no,
                            'pay_price' => $pay_price,
                            'paid_amount_pay' => $paid_amount_pay,
                            'total_cost' => $car->total_cost
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error distributing profit automatically', [
                            'car_id' => $car->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            return Response::json('ok', 200);    
    }
    public function getCarPayments(Request $request)
    {
        $carId = $request->car_id;
        
        // جلب الدفعات الخاصة بالسيارة باستخدام morphed_id
        $payments = Transactions::with(['wallet.user'])
            ->where('morphed_id', $carId)
            ->where('morphed_type', 'App\Models\Car')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($payments);
    }
    
    public function editSalePrice(Request $request)
    {
        $carId = $request->id;
        $newPayPrice = $request->newPayPrice;
        $editNote = $request->editNote ?? '';
        
        $car = Car::find($carId);
        if (!$car) {
            return response()->json(['error' => 'السيارة غير موجودة'], 404);
        }
        
        $oldPayPrice = $car->pay_price;
        
        // إذا كان السعر الجديد صفر، إلغاء البيع
        if ($newPayPrice == 0) {
            $car->update([
                'pay_price' => null,
                'note_pay' => $editNote,
                'client_id' => null,
                'results' => 0
            ]);
            
            // تسجيل التغيير في التاريخ
            DB::table('car_field_histories')->insert([
                'car_id' => $car->id,
                'field' => 'pay_price',
                'old_value' => $oldPayPrice,
                'new_value' => null,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // تسجيل إلغاء البيع
            DB::table('car_field_histories')->insert([
                'car_id' => $car->id,
                'field' => 'client_id',
                'old_value' => $car->client_id,
                'new_value' => null,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json(['success' => 'تم إلغاء بيع السيارة بنجاح'], 200);
        }
        
        // تحديث سعر البيع العادي
        $car->update([
            'pay_price' => $newPayPrice,
            'note_pay' => $editNote
        ]);
        
        // إعادة حساب حالة السيارة
        if ($car->paid_amount_pay >= $newPayPrice) {
            $car->results = 2; // مدفوعة بالكامل
            
            // حساب وتوزيع الربح تلقائياً فقط عند الدفع الكامل
            if ($car->paid_amount_pay == $newPayPrice) {
                try {
                    $car->refresh(); // تحديث بيانات السيارة
                    $car->distributeProfitToInvestors();
                    \Log::info('Profit distributed automatically after sale price update - FULL PAYMENT', [
                        'car_id' => $car->id,
                        'car_no' => $car->no,
                        'pay_price' => $newPayPrice,
                        'paid_amount' => $car->paid_amount_pay,
                        'total_cost' => $car->total_cost
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error distributing profit after sale price update', [
                        'car_id' => $car->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::info('Car marked as fully paid but profit not distributed - payment amount mismatch', [
                    'car_id' => $car->id,
                    'car_no' => $car->no,
                    'pay_price' => $newPayPrice,
                    'paid_amount' => $car->paid_amount_pay
                ]);
            }
        } else if ($car->paid_amount_pay > 0) {
            $car->results = 1; // مدفوعة جزئياً
        } else {
            $car->results = 0; // غير مبيعة
        }
        $car->save();
        
        // تسجيل التغيير في التاريخ
        DB::table('car_field_histories')->insert([
            'car_id' => $car->id,
            'field' => 'pay_price',
            'old_value' => $oldPayPrice,
            'new_value' => $newPayPrice,
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json(['success' => 'تم تعديل سعر البيع بنجاح'], 200);
    }
    
    public function editPaidAmount(Request $request)
    {
        $carId = $request->id;
        $newPaidAmount = $request->newPaidAmount;
        $editNote = $request->editNote ?? '';
        
        $car = Car::find($carId);
        if (!$car) {
            return response()->json(['error' => 'السيارة غير موجودة'], 404);
        }
        
        $oldPaidAmount = $car->paid_amount_pay;
        $difference = $newPaidAmount - $oldPaidAmount;
        
        // التحقق من الحالة السابقة (قبل التحديث)
        $wasFullyPaid = ($oldPaidAmount >= $car->pay_price && $car->pay_price > 0);
        
        // تحديث المبلغ المدفوع
        $car->paid_amount_pay = $newPaidAmount;
        
        // إعادة حساب حالة السيارة حسب المبلغ المدفوع وسعر البيع
        $isNowFullyPaid = ($newPaidAmount >= $car->pay_price && $car->pay_price > 0);
        if ($isNowFullyPaid) {
            // السيارة مدفوعة بالكامل
            $car->results = 2;
        } else if ($newPaidAmount > 0 && $car->pay_price > 0) {
            // السيارة مدفوعة جزئياً
            $car->results = 1;
        } else {
            // غير مدفوعة
            $car->results = 0;
        }
        
        $car->save();
        
        // إذا كانت السيارة أصبحت مدفوعة بالكامل الآن ولم تكن من قبل، قم بتوزيع الربح
        if ($isNowFullyPaid && !$wasFullyPaid) {
            try {
                $car->refresh(); // تحديث بيانات السيارة
                $car->distributeProfitToInvestors();
                \Log::info('Profit distributed automatically after editPaidAmount - FULL PAYMENT', [
                    'car_id' => $car->id,
                    'car_no' => $car->no,
                    'pay_price' => $car->pay_price,
                    'paid_amount_pay' => $newPaidAmount,
                    'total_cost' => $car->total_cost
                ]);
            } catch (\Exception $e) {
                \Log::error('Error distributing profit after editPaidAmount', [
                    'car_id' => $car->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // إنشاء transaction عكسي إذا كان هناك فرق
        if ($difference != 0) {
            // تحديد نوع المعاملة
            $transactionType = $difference > 0 ? 'in' : 'out';
            $amount = abs($difference);
            
            // إنشاء معاملة جديدة
            // استخدام محفظة الدخل للمعاملات الإيجابية ومحفظة الخرج للمعاملات السلبية
            $walletId = null;
            if ($difference > 0 && $this->inAccount) {
                $inWallet = $this->inAccount->getWalletOrCreate();
                $walletId = $inWallet ? $inWallet->id : null;
            } elseif ($difference < 0 && $this->outAccount) {
                $outWallet = $this->outAccount->getWalletOrCreate();
                $walletId = $outWallet ? $outWallet->id : null;
            }
            
            if ($walletId) {
                Transactions::create([
                    'amount' => $amount,
                    'type' => $transactionType,
                    'description' => $editNote ?: ($difference > 0 ? 'تعديل زيادة المبلغ المدفوع' : 'تعديل تقليل المبلغ المدفوع'),
                    'wallet_id' => $walletId,
                    'morphed_id' => $carId,
                    'morphed_type' => 'App\Models\Car',
                    'user_id' => auth()->id(),
                ]);
            }
        }
        
        // تسجيل التغيير في التاريخ
        DB::table('car_field_histories')->insert([
            'car_id' => $car->id,
            'field' => 'paid_amount_pay',
            'old_value' => $oldPaidAmount,
            'new_value' => $newPaidAmount,
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json(['success' => 'تم تعديل المبلغ المدفوع بنجاح'], 200);
    }
    
    public function deletePayment($paymentId)
    {
        return DB::transaction(function () use ($paymentId) {
            $payment = Transactions::find($paymentId);
            if (!$payment) {
                return response()->json(['error' => 'الدفعة غير موجودة'], 404);
            }

            // التأكد أن الدفعة تخص سيارة فقط
            if ($payment->morphed_type !== 'App\\Models\\Car') {
                return response()->json(['error' => 'هذه الدفعة لا تخص سيارة'], 400);
            }

            $car = Car::find($payment->morphed_id);
            if (!$car) {
                return response()->json(['error' => 'السيارة غير موجودة'], 404);
            }

            $deletedAmount = $payment->amount;
            $deletedWalletId = $payment->wallet_id;

            // حذف الدفعة المحددة فقط
            $payment->delete();

            // إعادة احتساب إجمالي المدفوع من واقع المعاملات المتبقية للسيارة
            $totalPaid = Transactions::where('morphed_id', $car->id)
                ->where('morphed_type', 'App\\Models\\Car')
                ->where('type', 'in')
                ->sum('amount');

            // تحديث car وفق الإجمالي الحقيقي
            $car->paid_amount_pay = max(0, (int) $totalPaid);

            if ($car->paid_amount_pay >= $car->pay_price && $car->pay_price > 0) {
                $car->results = 2; // مدفوعة بالكامل
            } else if ($car->paid_amount_pay > 0 && $car->pay_price > 0) {
                $car->results = 1; // مدفوعة جزئياً
            } else {
                $car->results = 0; // غير مدفوعة
            }

            $car->save();

            // إنشاء معاملة معاكسة (out) لنفس المحفظة لأغراض المطابقة المحاسبية
            Transactions::create([
                'amount' => $deletedAmount,
                'type' => 'out',
                'description' => 'حذف دفعة - معاملة معاكسة للسيارة: ' . $car->name . ' - رقم: ' . $car->pin,
                'wallet_id' => $deletedWalletId,
                'morphed_id' => $car->id,
                'morphed_type' => 'App\\Models\\Car',
                'user_id' => auth()->id(),
            ]);

            return response()->json(['success' => 'تم حذف الدفعة وتحديث حالة السيارة بنجاح'], 200);
        });
    }
    
    public function getIndexCar()
    {
        $data =  Car::with(['client', 'transactions.wallet.user']);
        
        // فلتر حالة السيارة
        $type = $_GET['type'] ?? '';
        if($type !== ''){
            $data = $data->where('results', $type);
        }
        
        // فلتر حالة الدفع
        $paymentStatus = $_GET['payment_status'] ?? '';
        if($paymentStatus){
            switch($paymentStatus){
                case 'unpaid':
                    $data = $data->where('results', 0); // في المخزن
                    break;
                case 'partial':
                    $data = $data->where('results', 1)->whereRaw('pay_price > paid_amount_pay'); // مباعة ولكن مدفوعة جزئياً
                    break;
                case 'paid':
                    $data = $data->where('results', 2)->whereRaw('pay_price = paid_amount_pay'); // مباعة ومدفوعة بالكامل
                    break;
            }
        }
        
        // البحث بالرقم التسلسلي
        $search = $_GET['q'] ?? '';
        if($search){
            $data = $data->where('pin', 'LIKE', '%'.$search.'%');
        }
        
        $data = $data->orderByRaw('CAST(no AS UNSIGNED) DESC')->paginate(100);
        
        // حساب الأرقام من البيانات الفعلية
        $totalIncome = Transactions::where('type', 'in')->sum('amount');
        $totalExpenses = Transactions::where('type', 'out')->sum('amount');
        $totalDebt = Car::where('results', '!=', 0)->sum('pay_price') - Car::where('results', '!=', 0)->sum('paid_amount_pay');
        $totalFundIncome = Transactions::where('type', 'in')
            ->whereHas('wallet.user', function($query) {
                $query->where('email', 'main@account.com');
            })->sum('amount');
        
        // حساب رأس المال (مجموع سعر الشراء + جميع المصاريف لجميع السيارات)
        $totalCapital = Car::selectRaw('SUM(purchase_price + COALESCE(erbil_exp, 0) + COALESCE(erbil_shipping, 0) + COALESCE(dubai_exp, 0) + COALESCE(dubai_shipping, 0)) as total')
                       ->value('total') ?? 0;
        
        return Response::json([
            'data' => $data,
            'stats' => [
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'totalDebt' => $totalDebt,
                'totalFundIncome' => $totalFundIncome,
                'totalCapital' => $totalCapital
            ]
        ], 200);
    }
    public function getIndexCarSearch()
    {
        $data = Car::with(['client', 'transactions.wallet.user']);
        
        // البحث بالرقم التسلسلي
        $term = $_GET['q'] ?? '';
        if($term){
            $data = $data->where('pin', 'LIKE', '%'.$term.'%');
        }
        
        // فلتر حالة السيارة
        $type = $_GET['type'] ?? '';
        if($type !== ''){
            $data = $data->where('results', $type);
        }
        
        // فلتر حالة الدفع
        $paymentStatus = $_GET['payment_status'] ?? '';
        if($paymentStatus){
            switch($paymentStatus){
                case 'unpaid':
                    $data = $data->where('results', 0); // في المخزن
                    break;
                case 'partial':
                    $data = $data->where('results', 1)->whereRaw('pay_price > paid_amount_pay'); // مباعة ولكن مدفوعة جزئياً
                    break;
                case 'paid':
                    $data = $data->where('results', 2)->whereRaw('pay_price = paid_amount_pay'); // مباعة ومدفوعة بالكامل
                    break;
            }
        }
        
        $data = $data->orderByRaw('CAST(no AS UNSIGNED) DESC')->paginate(10);
        return Response::json($data, 200);
    }
    public function addToBox()
    {
        $amount = $_GET['amount'] ?? 0;
        $note = $_GET['note'] ?? '';
        $car_id = $_GET['car_id'] ?? '';
        $customer_id = $_GET['customer_id'] ?? '';
        
        // Get car name if car_id is provided
        $car_name = '';
        if ($car_id) {
            $car = Car::find($car_id);
            $car_name = $car ? $car->name . ' - رقم ' . $car->no : '';
        }
        
        // Get customer name if customer_id is provided
        $customer_name = '';
        if ($customer_id) {
            $customer = User::find($customer_id);
            $customer_name = $customer ? $customer->name : '';
        }
        
        $desc = trans('text.addToBox').' '.$amount.'$' .(($note)?' البيان : '.$note:'');
        if ($car_name) {
            $desc .= ' - السيارة: ' . $car_name;
        }
        if ($customer_name) {
            $desc .= ' - الزبون: ' . $customer_name;
        }
        
        // التحقق من وجود حساب الدخل (in@account.com)
        if (!$this->inAccount) {
            return Response::json([
                'success' => false,
                'message' => 'حساب الدخل (in@account.com) غير موجود'
            ], 500);
        }
        
        $inWallet = $this->inAccount->getWalletOrCreate();
        if (!$inWallet || !$inWallet->id) {
            return Response::json([
                'success' => false,
                'message' => 'فشل في إنشاء محفظة حساب الدخل'
            ], 500);
        }
        
        // إنشاء معاملة دخول للصندوق (حساب الدخل)
        Transactions::create([
            'amount' => $amount,
            'type' => 'in',
            'description' => $desc,
            'wallet_id' => $inWallet->id,
            'morphed_id' => $this->inAccount->id,
            'morphed_type' => 'App\Models\User',
            'user_id' => $this->inAccount->id, // استخدام حساب الدخل
            'is_pay' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إذا كان هناك customer_id، أضف معاملة لمحفظة الزبون
        if ($customer_id) {
            $customer = User::find($customer_id);
            if ($customer) {
                // استخدام getWalletOrCreate للتأكد من وجود المحفظة
                $customerWallet = $customer->getWalletOrCreate();
                
                // التحقق من وجود المحفظة و الـ ID بشكل صحيح
                if (!$customerWallet || !$customerWallet->id) {
                    \Log::error('Customer wallet not created properly', [
                        'customer_id' => $customer_id,
                        'customer_name' => $customer->name,
                        'wallet' => $customerWallet
                    ]);
                    
                    return Response::json([
                        'success' => false,
                        'error' => 'فشل في إنشاء أو العثور على محفظة الزبون'
                    ], 500);
                }
                
                // التحقق من وجود wallet في قاعدة البيانات
                $walletExists = DB::table('wallets')->where('id', $customerWallet->id)->exists();
                if (!$walletExists) {
                    \Log::error('Wallet ID does not exist in database', [
                        'wallet_id' => $customerWallet->id,
                        'customer_id' => $customer_id
                    ]);
                    
                    // محاولة إنشاء المحفظة مرة أخرى
                    $customerWallet = $customer->createWalletIfNotExists();
                    
                    if (!$customerWallet || !$customerWallet->id) {
                        return Response::json([
                            'success' => false,
                            'error' => 'فشل في إنشاء محفظة الزبون'
                        ], 500);
                    }
                }
                
                Transactions::create([
                    'amount' => $amount,
                    'type' => 'in',
                    'description' => $desc,
                    'wallet_id' => $customerWallet->id,
                    'morphed_id' => $customer->id,
                    'morphed_type' => 'App\Models\User',
                    'user_id' => $customer->id,
                    'is_pay' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        return Response::json([
            'success' => true,
            'message' => 'تم إدخال المبلغ بنجاح!',
            'amount' => $amount,
            'note' => $note,
            'car_name' => $car_name
        ], 200);    
    }
    public function withDrawFromBox()
    {
        $amount = $_GET['amount'] ?? 0;
        $note = $_GET['note'] ?? '';
        $car_id = $_GET['car_id'] ?? '';
        $customer_id = $_GET['customer_id'] ?? '';
        
        // Get car name if car_id is provided
        $car_name = '';
        if ($car_id) {
            $car = Car::find($car_id);
            $car_name = $car ? $car->name . ' - رقم ' . $car->no : '';
        }
        
        // Get customer name if customer_id is provided
        $customer_name = '';
        if ($customer_id) {
            $customer = User::find($customer_id);
            $customer_name = $customer ? $customer->name : '';
        }
        
        $desc = trans('text.withDrawFromBox').' '.$amount.'$' .(($note)?' البيان : '.$note:'');
        if ($car_name) {
            $desc .= ' - السيارة: ' . $car_name;
        }
        if ($customer_name) {
            $desc .= ' - الزبون: ' . $customer_name;
        }
        
        // التحقق من وجود حساب الخرج (out@account.com)
        if (!$this->outAccount) {
            return Response::json([
                'success' => false,
                'message' => 'حساب الخرج (out@account.com) غير موجود'
            ], 500);
        }
        
        $outWallet = $this->outAccount->getWalletOrCreate();
        if (!$outWallet || !$outWallet->id) {
            return Response::json([
                'success' => false,
                'message' => 'فشل في إنشاء محفظة حساب الخرج'
            ], 500);
        }
        
        // إنشاء معاملة خرج من الصندوق (حساب الخرج)
        Transactions::create([
            'amount' => $amount,
            'type' => 'out',
            'description' => $desc,
            'wallet_id' => $outWallet->id,
            'morphed_id' => $this->outAccount->id,
            'morphed_type' => 'App\Models\User',
            'user_id' => $this->outAccount->id, // استخدام حساب الخرج
            'is_pay' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إذا كان هناك customer_id، أضف معاملة لمحفظة الزبون
        if ($customer_id) {
            $customer = User::find($customer_id);
            if ($customer) {
                // استخدام getWalletOrCreate للتأكد من وجود المحفظة
                $customerWallet = $customer->getWalletOrCreate();
                if ($customerWallet) {
                    Transactions::create([
                        'amount' => $amount,
                        'type' => 'out',
                        'description' => $desc,
                        'wallet_id' => $customerWallet->id,
                        'morphed_id' => $customer->id,
                        'morphed_type' => 'App\Models\User',
                        'user_id' => $customer->id,
                        'is_pay' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        return Response::json([
            'success' => true,
            'message' => 'تم سحب المبلغ بنجاح!',
            'amount' => $amount,
            'note' => $note,
            'car_name' => $car_name
        ], 200);    
    }
    public function addPaymentCar()
    {
        $user_id = auth()->id(); // استخدام المستخدم المسجل دخوله
        $car_id = $_GET['car_id']??0;
        $amount=$_GET['amount']??0;
        $pay_price =$_GET['pay_price']??0;
        $note = $_GET['note']??'';
        
        $car = Car::find($car_id);
        if(!$car) {
            return Response::json(['error' => 'السيارة غير موجودة'], 404);
        }
        
        // فحص أن المبلغ أكبر من 0
        if($amount <= 0) {
            return Response::json(['error' => 'يجب إدخال مبلغ أكبر من 0'], 400);
        }
        
        if($pay_price){
            $car->update(['pay_price'=>$pay_price]);
        }
        $car->increment('paid_amount_pay',$amount);
        
        $wallet = Wallet::where('user_id',$car->client_id)->first();
        $desc=trans('text.addPayment').' '.$amount.'$' .(($note)?' البيان : '.$note:'');
        
        // إضافة المعاملات المحاسبية
        if($this->inAccount) {
         
            
            // زيادة في الحساب الرئيسي
             // زيادة في حساب الدخل
            $this->accountingController->increaseWallet($amount, $desc, $this->inAccount->id, $car_id, 'App\Models\Car', $user_id);
        } else {
            \Log::warning('Main account or In account not found', [
                'inAccount' => $this->inAccount
            ]);
        }
        
        // فحص وجود المحفظة قبل استدعاء decrement
        if($wallet && $car->pay_price-$car->paid_amount_pay >= 0){
            $wallet->decrement('balance',$amount); 
        }
        
        // تحديث حالة السيارة بعد إضافة الدفعة
        $car->refresh(); // تحديث بيانات السيارة بعد increment
        $totalPaid = $car->paid_amount_pay;
        $salePrice = $car->pay_price;
        
        if($totalPaid >= $salePrice && $salePrice > 0){
            // السيارة مدفوعة بالكامل
            $car->update(['results' => 2]);
            
            // حساب وتوزيع الربح تلقائياً عند اكتمال دفع السيارة
            try {
                $car->refresh(); // تحديث بيانات السيارة
                $car->distributeProfitToInvestors();
                \Log::info('Profit distributed automatically after payment completion', [
                    'car_id' => $car->id,
                    'car_no' => $car->no,
                    'pay_price' => $car->pay_price,
                    'total_cost' => $car->total_cost
                ]);
            } catch (\Exception $e) {
                \Log::error('Error distributing profit after payment completion', [
                    'car_id' => $car->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else if($totalPaid > 0 && $totalPaid < $salePrice) {
            // السيارة مدفوعة جزئياً
            $car->update(['results' => 1]);
        }
        
        return Response::json([
            'success' => true,
            'message' => 'تم إضافة الدفعة بنجاح!',
            'amount' => $amount,
            'remaining' => $car->pay_price - $car->paid_amount_pay
        ], 200);
    }

    // حساب ربح الصندوق من المستثمرين
    public function getMainFundProfitFromInvestors()
    {
        try {
            // الحصول على جميع السيارات المباعة والمدفوعة بالكامل
            $soldCars = Car::where('results', 2)
                ->whereRaw('pay_price = paid_amount_pay')
                ->where('pay_price', '>', 0)
                ->get();

            $totalIncome = 0;
            $totalInvestments = 0;
            $totalProfitDistributed = 0;
            $mainFundNetProfit = 0;

            foreach ($soldCars as $car) {
                $carIncome = $car->pay_price ?? 0;
                $carCost = $car->total_cost;
                $carProfit = $carIncome - $carCost;

                // حساب إجمالي الاستثمارات في هذه السيارة
                $carInvestments = $car->investmentCars()
                    ->whereHas('investment', function($query) {
                        $query->where('status', 'active');
                    })
                    ->sum('invested_amount');

                // حساب إجمالي الربح الموزع للمستثمرين
                $carProfitDistributed = $car->investmentCars()
                    ->whereHas('investment', function($query) {
                        $query->where('status', 'active');
                    })
                    ->sum('profit_share');

                $totalIncome += $carIncome;
                $totalInvestments += $carInvestments;
                $totalProfitDistributed += $carProfitDistributed;
            }

            // حساب الربح الصافي للصندوق
            $mainFundNetProfit = $totalIncome - $totalInvestments - $totalProfitDistributed;

            return Response::json([
                'success' => true,
                'data' => [
                    'total_income_from_sales' => $totalIncome,
                    'total_investments_returned' => $totalInvestments,
                    'total_profit_distributed' => $totalProfitDistributed,
                    'main_fund_net_profit' => $mainFundNetProfit,
                    'sold_cars_count' => $soldCars->count(),
                    'breakdown' => [
                        'income' => $totalIncome,
                        'investments_returned' => $totalInvestments,
                        'profit_distributed' => $totalProfitDistributed,
                        'net_profit_for_main_fund' => $mainFundNetProfit
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error calculating main fund profit from investors', [
                'error' => $e->getMessage()
            ]);

            return Response::json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب ربح الصندوق من المستثمرين',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function DelCar(Request $request){
        $car=Car::find($request->id);
        if(($car->client_id??0)&&($car->paid_amount??0)){
            $desc=' مرتج حذف سيارة'.$car->paid_amount;
            $wallet = Wallet::where('user_id',$car->client_id)->first();
            if($wallet) {
            $wallet->decrement('balance',$car->pay_price-$car->paid_amount_pay);
            }
            $this->accountingController->decreaseWallet($car->pay_price-$car->paid_amount_pay, $desc,$this->debtAccount->id,$car->id,'App\Models\Car');
        }
        if(($car->paid_amount??0)>0){
            $desc=' مرتج حذف سيارة'.$car->paid_amount;
     

        }
        if(($car->purchase_price??0)-($car->paid_amount_pay??0)>0){
            $desc=' مرتج حذف سيارة'.$car->paid_amount_pay;
           
        }

        $car->delete();
        DB::statement('SET @row_number = 0');

        DB::table('car')
            ->whereNull('deleted_at') // Apply soft delete constraint
            ->orderBy('id') // Assuming 'id' is the primary key column
            ->update(['no' => DB::raw('(@row_number:=@row_number + 1)')]);
            
       

        return Response::json('delete is done', 200);    

        
    }

    public function getCarHistory($carId)
    {
        try {
            $history = CarFieldHistory::where('car_id', $carId)->get();

            if ($history->isEmpty()) {
                return response()->json(['message' => 'No history found for this car.'], 404);
            }

            return response()->json($history, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching car history.', 'error' => $e->getMessage()], 500);
        }
    }

    public function restoreCarHistory($historyId)
    {
        try {
            $history = CarFieldHistory::find($historyId);

            if (!$history) {
                return response()->json(['message' => 'History record not found.'], 404);
            }

            // Find the car and update the field to its old value
            $car = Car::find($history->car_id);
            if (!$car) {
                return response()->json(['message' => 'Car not found.'], 404);
            }

            $field = $history->field;
            $oldValue = $history->old_value;

            // Update the car's field
            $car->update([$field => $oldValue]);

            // Log the restoration in the history table
            CarFieldHistory::create([
                'car_id' => $car->id,
                'field' => $field,
                'old_value' => $car->$field,
                'new_value' => $oldValue,
                'updated_by' => auth()->id(), // Replace with actual user ID if authentication is implemented
            ]);

            return response()->json(['message' => 'Field restored successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error restoring history.', 'error' => $e->getMessage()], 500);
        }
    }


    
}
