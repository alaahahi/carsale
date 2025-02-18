<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use App\Models\Info;
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

use Carbon\Carbon;

use Inertia\Inertia;

class DashboardController extends Controller
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
    $this->debtAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','debt@account.com')->first();
    $this->transfersAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','transfers@account.com')->first();

    
    }
    public function __invoke(Request $request)
    {
        $results = null;
       // $client = new Client( $this->url, 'masterKey');
       // $results = $client->stats();
        //dd($results);
        return Inertia::render('dashboard', ['url'=>$this->url]);   

    }
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $results = null;
        $user=   User::where('type_id', $this->userSeles)->get();
        $car = Car::all();
        $carUser=  $car->where('user_id', $authUser->id)->count();
       
        $client = User::where('type_id', $this->userClient)->get();
    
        $expenses=ExpensesType::all(); 

        $grandTotal = \App\Models\Car::selectRaw('
        SUM(
                    purchase_price + dubai_shipping + dubai_exp + erbil_shipping + erbil_exp
                ) as grand_total
            ')
            ->value('grand_total'); // Directly retrieve the value


        return Inertia::render('Dashboard', ['url'=>$this->url,'debtAccount'=>$this->debtAccount,'transfersAccount'=>$this->transfersAccount,
        'outAccount'=>$this->outAccount,'inAccount'=>$this->inAccount,'mainAccount'=>$grandTotal,
        'expenses'=> $expenses,'user'=> $user,'client'=>$client ,'carCount'=> $car->count(),
        'working'=> $car->where('client_id',null)->count()]);   

    }
    public function totalInfo(Request $request)
    {
        $expenses=ExpensesType::all();
        $car = Car::all();

        $data = [
        'debtAccount'=>$this->debtAccount->wallet->balance,
        'transfersAccount'=>$this->transfersAccount->wallet->balance,
        'outAccount'=>$this->outAccount->wallet->balance,
        'inAccount'=>$this->inAccount->wallet->balance,
        'mainAccount'=>$this->mainAccount->wallet->balance,
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
            $countComp =Car::whereBetween('created_at', [$start, $end])->where('user_accepted','!=',null)->count();
        }
        else{
            $countComp =Car::where('user_accepted','!=',null)->count();  
        }
        return response()->json($countComp); 
    }
    public function addCar(Request $request)
    {
        $car_id = $request->id ?? 0;
        $maxNo = Car::max('no');
        $no = $car_id ? $request->no : $maxNo + 1;
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
                'image' => $images ? json_encode($images) : "",
                'user_id' => $request->user_id ?? 0,
                'erbil_exp' => $request->erbil_exp,
                'erbil_shipping' => $request->erbil_shipping,
                'dubai_exp' => $request->dubai_exp,
                'dubai_shipping' => $request->dubai_shipping,
                'no' => $no
            ]);
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
                        'updated_by' =>  $request->user_id ?? 0,
                        'description'=>$exp_note,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'user_id' =>  $request->user_id ?? 0,
                    ]);
                }
            }
    
            // Update car fields
            $car->update([
                'company_id' => $request->company_id,
                'name_id' => $request->name_id,
                'model_id' => $request->model_id,
                'color_id' => $request->color_id,
                'pin' => $request->pin,
                'purchase_data' => $request->purchase_data,
                'purchase_price' => $request->purchase_price,
                'note' => $request->note ?? '',
                'image' => $images ? json_encode($images) : "",
                'erbil_exp' => $request->erbil_exp,
                'erbil_shipping' => $request->erbil_shipping,
                'dubai_exp' => $request->dubai_exp,
                'dubai_shipping' => $request->dubai_shipping,
                'source' => $request->source,
                'no' => $no
            ]);
        }
    
        return response()->json('ok', 200);
    }
    
    public function getIndexExpenses () {

        $expenses = Expenses::with('user')->paginate(10);
        return Response::json($expenses, 200);    

    }
    public function addGenExpenses (Request $request){
        $expenses = Expenses::create([
            'user_id' => $request->user_id,
            'reason' => $request->reason ?? '',
            'amount' => $request->amount ?? 0,
            'note' => $request->note ?? '',
        ]);
        if($expenses->id){
            $desc=trans('text.genExpenses');
            $this->accountingController->increaseWallet($expenses->amount, $desc,$this->outAccount->id,($request->user_id??0),'App\Models\User', ($request->user_id??0));
            $this->accountingController->decreaseWallet($expenses->amount, $desc,$this->mainAccount->id,($request->user_id??0),'App\Models\User', ($request->user_id??0));
        }
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
            $this->accountingController->increaseWallet($expenses->amount, $desc,$this->outAccount->id,($_GET['user_id']??0),'App\Models\User', ($_GET['user_id']??0));
            $this->accountingController->decreaseWallet($expenses->amount, $desc,$this->mainAccount->id,($_GET['user_id']??0),'App\Models\User', ($_GET['user_id']??0));
        }
        return Response::json('ok', 200);    

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
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;
            
            case 2:
                $car->increment('erbil_exp',$expens_amount);
                $desc=trans('text.expensesExpErbil').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;

            case 3:
                $car->increment('erbil_shipping',$expens_amount);
                $desc=trans('text.expensesShippingErbil').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;
            
            case 4:
                $car->increment('dubai_shipping',$expens_amount);
                $desc=trans('text.expensesShippingDubai').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name.' '.$note;
                $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
                break;

            case 5:
                if(($car->purchase_price - $car->paid_amount) ==0){
                    return Response::json('error', 500);       
                }
                else{
            
                    $car->increment('paid_amount',$expens_amount);
                    $desc=trans('text.expensesExpPay').$expens_amount.'$'.$car->company?->name.' '.$car->name?->name;
                    $this->accountingController->decreaseWallet($expens_amount, $desc,$this->mainAccount->id,$car_id,'App\Models\Car',$user_id);
                    $this->accountingController->increaseWallet($expens_amount, $desc,$this->outAccount->id,$car_id,'App\Models\Car',$user_id);
          

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
            $client = new User;
            $client->name = $request->client_name;
            $client->phone = $request->client_phone;
            $client->type_id = $this->userClient;
            $client->save();
            Wallet::create(['user_id' => $client->id,'balance'=>$pay_price-$paid_amount_pay]);
            $client_id=$client->id;
        }else{
            $wallet = Wallet::where('user_id',$client_id)->first();
            $wallet->increment('balance',$pay_price-$paid_amount_pay); 
        }

        $car=Car::find($request->id);
        if($car->id){
                $car->update([
                'note_pay' =>$request->note_pay,
                'client_id'=> $client_id ,
                'pay_data'=> Carbon::now()->format('Y-m-d'),
                'pay_price'=>$pay_price,
                'paid_amount_pay' =>  $paid_amount_pay,
                'results'=>1
                 ]);
                $desc=trans('text.buyCar').' '.$car->pay_price.trans('text.payDone').$car->paid_amount_pay;
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
                $this->accountingController->increaseWallet($car->paid_amount_pay, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
                if($pay_price-$paid_amount_pay >= 0){
                    $this->accountingController->increaseWallet($pay_price-$paid_amount_pay, $desc,$this->debtAccount->id,$car->id,'App\Models\Car');
                }
                if($pay_price==$paid_amount_pay){
                    $car->increment('results'); 
                }
            }
            return Response::json('ok', 200);    
    }
    public function getIndexCar()
    {
        $data =  Car::with('client')->with('transactions');
        $type =$_GET['type'] ?? '';
        if($type){
            $data =    $data->where('results', $type);
        }
        if($type==0){
            $data =    $data->where('results', $type);
        }
        $data =$data->orderBy('no', 'DESC')->paginate(100);
        return Response::json($data, 200);
    }
    public function getIndexCarSearch()
    {
        $term = $_GET['q']??'';
        $data =  Car::with('client')->orwhere('pin', 'LIKE','%'.$term.'%');
        $type =$_GET['type'] ?? '';
        if($type){
        $data =    $data->where('results', $type);
        }
        if($type==0){
            $data =    $data->where('results', $type);
        }
        $data =$data->orderBy('no', 'DESC')->paginate(10);
        return Response::json($data, 200);
    }
    public function addToBox()
    {
        $user_id = $_GET['user_id']??0;
        $desc=trans('text.addToBox').' '.($_GET['amount']??0).'$'.' || '.$_GET['note']??'';
        $this->accountingController->increaseWallet(($_GET['amount']??0), $desc,$this->mainAccount->id,$user_id,'App\Models\User',$user_id);
        $this->accountingController->increaseWallet(($_GET['amount']??0), $desc,$this->inAccount->id,$user_id,'App\Models\User',$user_id);
        return Response::json('ok', 200);    
    }
    public function withDrawFromBox()
    {
        $user_id =($_GET['user_id']??'');
        $desc=trans('text.withDrawFromBox').' '.($_GET['amount']??'').'$'.' || '.$_GET['note']??'';
        $this->accountingController->decreaseWallet(($_GET['amount']??0), $desc,$this->mainAccount->id,$user_id,'App\Models\User',$user_id);
        $this->accountingController->increaseWallet(($_GET['amount']??0), $desc,$this->outAccount->id,$user_id,'App\Models\User',$user_id);

        return Response::json('ok', 200);    
    }
    public function addPaymentCar()
    {
        $user_id = $_GET['user_id']??0;
        $car_id = $_GET['car_id']??0;
        $amount=$_GET['amount']??0;
        $pay_price =$_GET['pay_price']??0;
        $car = Car::find($car_id);
        if($pay_price){
            $car->update(['pay_price'=>$pay_price]);
        }
        $car->increment('paid_amount_pay',$amount);
        $wallet = Wallet::where('user_id',$car->client_id)->first();
        $desc=trans('text.addPayment').' '.$amount.'$'.' || '.$_GET['note']??'';
        //$this->accountingController->decreaseWallet($amount, $desc,$this->debtSupplier->id,$car_id,'App\Models\Car',$user_id);
        if($car->pay_price-$car->paid_amount_pay >= 0){

            $wallet->decrement('balance',$amount); 
        }
        if($car->pay_price-$car->paid_amount_pay==0){
            $car->increment('results'); 
        }
        $desc=trans('text.payDone').' '.$amount;
        $this->accountingController->increaseWallet($amount, $desc,$this->mainAccount->id,$car->id,'App\Models\Car');
        $this->accountingController->increaseWallet($amount, $desc,$this->inAccount->id,$car->id,'App\Models\Car');
        if($pay_price-$car->paid_amount_pay >= 0){
            $this->accountingController->increaseWallet($amount, $desc,$this->debtAccount->id,$car->id,'App\Models\Car');
        }

        return Response::json('ok', 200);    
    }
    public function DelCar(Request $request){
        $car=Car::find($request->id);
        if(($car->client_id??0)&&($car->paid_amount??0)){
            $desc=' مرتج حذف سيارة'.$car->paid_amount;
            $wallet = Wallet::where('user_id',$car->client_id)->first();
            $wallet->decrement('balance',$car->pay_price-$car->paid_amount_pay);
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
