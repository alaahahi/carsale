<?php
   
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Wallet;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct(){
         $this->url = env('FRONTEND_URL');
         $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
         $this->userSeles =  UserType::where('name', 'seles')->first()->id;
         $this->userClient =  UserType::where('name', 'client')->first()->id;
         $this->userAccount =  UserType::where('name', 'account')->first()->id;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function index()
    {
        // Card functionality removed
        return Inertia::render('Users/Index', ['url'=>$this->url,'cards'=>[]]);
    }
    public function getSaler()
    {
        $data = User::with('userType:id,name','wallet')->where('type_id', $this->userSeles)->paginate(10);
        return Response::json($data, 200);    

    }

    public function clients()
    {
        // Card functionality removed
        return Inertia::render('Clients/Index', ['url'=>$this->url,'cards'=>[]]);
    }
    
    public function show ()
    {
        return Inertia::render('Users/Index', ['url'=>$this->url]);
    }
    public function getIndex()
    {
        $data = User::with('userType:id,name','wallet')->where('type_id', $this->userSeles)->paginate(10);
        return Response::json($data, 200);
    }
    public function getIndexClients(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $clients = User::with(['userType:id,name','wallet', 'cars' => function($query) {
            $query->where('results', '!=', 0); // السيارات المباعة فقط
        }])->where('type_id', $this->userClient);
        
        // البحث بالاسم
        if ($request->has('name') && !empty($request->name)) {
            $clients->where('name', 'LIKE', '%' . $request->name . '%');
        }
        
        // البحث برقم الهاتف
        if ($request->has('phone') && !empty($request->phone)) {
            $clients->where('phone', 'LIKE', '%' . $request->phone . '%');
        }
        
        // حساب المطلوب والمدفوع لكل عميل قبل الباجنيشن
        $allClients = $clients->get();
        
        $allClients->each(function($client) {
            $totalRequired = 0; // إجمالي المطلوب
            $totalPaid = 0; // إجمالي المدفوع
            
            foreach($client->cars as $car) {
                $totalRequired += $car->pay_price ?? 0;
                $totalPaid += $car->paid_amount_pay ?? 0;
            }
            
            $client->total_required = $totalRequired;
            $client->total_paid = $totalPaid;
            $client->remaining_debt = $totalRequired - $totalPaid;
        });
        
        // فلتر حالة العميل
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            $allClients = $allClients->filter(function($client) use ($status) {
                $debt = $client->remaining_debt;
                
                switch($status) {
                    case 'debtor':
                        return $debt > 0;
                    case 'paid':
                        return $debt === 0;
                    case 'credit':
                        return $debt < 0;
                    default:
                        return true;
                }
            });
        }
        
        // تطبيق الباجنيشن بعد الفلترة
        $clients = new \Illuminate\Pagination\LengthAwarePaginator(
            $allClients->forPage($page, $perPage),
            $allClients->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        
        // استخدام نفس البيانات المفلترة للإحصائيات
        
        $stats = [
            'total_clients' => $allClients->count(),
            'total_required' => $allClients->sum('total_required'),
            'total_paid' => $allClients->sum('total_paid'),
            'total_debt' => $allClients->sum('remaining_debt'),
            'clients_with_debt' => $allClients->where('remaining_debt', '>', 0)->count(),
            'clients_paid_off' => $allClients->where('remaining_debt', '<=', 0)->count()
        ];
        
        return Response::json([
            'data' => $clients->items(),
            'current_page' => $clients->currentPage(),
            'last_page' => $clients->lastPage(),
            'per_page' => $clients->perPage(),
            'total' => $clients->total(),
            'from' => $clients->firstItem(),
            'to' => $clients->lastItem(),
            'stats' => $stats
        ], 200);
    }
    public function addClients()
    {
        $usersType = UserType::all();
        $userSeles=$this->userSeles;
        $userDoctor =  $this->userClient;
        $userHospital = '';
        return Inertia::render('Clients/Create',['usersType'=>$usersType,'userSeles'=>$userSeles,'userDoctor'=>$userDoctor,'userHospital'=>$userHospital]);
    }
    public function create()
    {
        $usersType = UserType::all();
        $userSeles=$this->userSeles;
        $userDoctor =  $this->userClient;
        $userHospital = '';
        return Inertia::render('Users/Create',['usersType'=>$usersType,'userSeles'=>$userSeles,'userDoctor'=>$userDoctor,'userHospital'=>$userHospital]);
    }
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
           ])->validate();
           //$userChief_id =User::where('type_id',  $this->userChief)->first()->id ?? 0 ;
                $user = User::create([
                    'name' => $request->name,
                    'type_id' => $this->userSeles,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone
                ]);
  
                Wallet::create(['user_id' => $user->id]);
     
        return Inertia::render('Users/Index', ['url'=>$this->url]);
    }
    public function clientsStore(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
           ])->validate();
           //$userChief_id =User::where('type_id',  $this->userChief)->first()->id ?? 0 ;
                $user = User::create([
                    'name' => $request->name,
                    'type_id' => $this->userClient,
                    'phone' => $request->phone
                ]);
  
                Wallet::create(['user_id' => $user->id]);
     
        return Inertia::render('Clients/Index', ['url'=>$this->url]);
    }

    public function getCoordinator(Request $request)
    {
        $user =User::where('type_id', $request->id);
        return Response::json(['status' => 200,'massage' => 'users found','data' => $user->get()],200);
    }
    
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function edit(User $User)
    {
        $usersType = UserType::all();
        $userSeles=$this->userSeles;
        $userDoctor =  $this->userClient;
        $userHospital = '';
       //$coordinators =User::where('type_id', $this->userCoordinator )->get();
       // $chief =User::where('type_id', $this->userChief )->get();
        return Inertia::render('Users/Edit', [
            'user' => $User,'usersType'=>$usersType,'userSeles'=>$userSeles,'userDoctor'=>$userDoctor,'userHospital'=>$userHospital
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function storeClient(Request $request)
    {
        try {
            // التحقق من صحة البيانات
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20'
            ]);

            // إنشاء العميل الجديد
            $user = User::create([
                'name' => trim($request->name),
                'phone' => $request->phone ? trim($request->phone) : null,
                'type_id' => $this->userClient,
                'email' => 'client_' . time() . '@temp.com', // إيميل مؤقت
                'password' => bcrypt('123456') // كلمة مرور افتراضية
            ]);

            // إنشاء محفظة للعميل
            Wallet::create(['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العميل بنجاح',
                'user' => $user->fresh()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating client: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إضافة العميل'
            ], 500);
        }
    }

    public function updateClient(Request $request, $id)
    {
        try {
            // التحقق من صحة البيانات
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20'
            ]);

            // البحث عن العميل
            $user = User::findOrFail($id);
            
            // التحقق من أن المستخدم هو عميل
            if ($user->type_id != $this->userClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا المستخدم ليس عميلاً'
                ], 400);
            }

            // تحديث البيانات
            $user->update([
                'name' => trim($request->name),
                'phone' => $request->phone ? trim($request->phone) : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات العميل بنجاح',
                'user' => $user->fresh()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating client: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحديث بيانات العميل'
            ], 500);
        }
    }

    public function destroyClient($id)
    {
        try {
            // البحث عن العميل
            $user = User::findOrFail($id);
            
            // التحقق من أن المستخدم هو عميل
            if ($user->type_id != $this->userClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا المستخدم ليس عميلاً'
                ], 400);
            }

            // التحقق من وجود سيارات مرتبطة بالعميل
            $hasCars = $user->cars()->exists();
            if ($hasCars) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف العميل لأنه مرتبط بسيارات'
                ], 400);
            }

            // حذف المحفظة المرتبطة
            $user->wallet()->delete();
            
            // حذف العميل
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العميل بنجاح'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting client: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في حذف العميل'
            ], 500);
        }
    }
    
    public function update($id, Request $request)
    {
        $username = User::where('id', $id)->first()->email;

        switch ($username) {
            case $request->email:
                if ($request->password) {
                    $request->validate([
                        'name' => 'required|string|max:255',
                        'password' => [Rules\Password::defaults()],
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'password' => Hash::make($request->password),
                        'type_id' => $request->userType,
                        'percentage' => $request->percentage
                    ]);
                } else {
                    $request->validate([
                        'name' => 'required|string|max:255',
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'type_id' => $request->userType,
                        'percentage' => $request->percentage
                    ]);
                }
                break;
                
            default:
                if ($request->password) {
                    $request->validate([
                        'name' => 'required|string|max:255',
                        'email' => 'required|string|max:255|unique:users',
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'percentage' => $request->percentage
                    ]);
                } else {
                    $request->validate([
                        'name' => 'required|string|max:255',
                        'email' => 'required|string|max:255|unique:users',
                        'password' => [Rules\Password::defaults()],
                    ]);
                    $user = User::find($id)->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'percentage' => $request->percentage
                    ]);
                }
                break;
        }
        
        return Inertia::render('Users/Index', ['url'=>$this->url]);

    }
    
    
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function destroy($id)
    {   
     
       // User::where('parent_id',$id)->update(['parent_id' =>null]);
        User::find($id)->delete();
     
        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function ban($id)
    {
        User::find($id)->update(['is_band' => 1]);
        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function unban($id)
    {
        User::find($id)->update(['is_band' => 0]);
        return Inertia::render('Users/Index', ['url'=>$this->url]); 
    }
    public function login(LoginRequest $request)
    {
        try {
             $request->authenticate();
             $user =User::where('email', $request->email)->first();
             $publickey_receiver =  User::find($user->parent_id)->public_key ?? 0;
             if( $user->device){
                $request->device = $user->device.' | '.$request->device;
             }
             $user->append(['token']);
             if(!$user->is_band){
                if( $user->type_id == $this->userChief){
                    if($request->public_key){
                        $user->update(['public_key' => $request->public_key,'device' =>  $request->device,'publickey_receiver'=> $publickey_receiver]);
                    }
                    return Response::json(['status' => 200,'massage' => 'user found','data' => $user,'token'=> Crypt::encryptString($user->first()->id)],200); 
                }else{
                    if($publickey_receiver){
                    if($request->public_key){
                        $user->update(['public_key' => $request->public_key,'device' => $request->device,'publickey_receiver'=> $publickey_receiver]);
                    }
                       return Response::json(['status' => 200,'massage' => 'user found','data' => $user,'token'=> Crypt::encryptString($user->first()->id)],200); 
                    }else
                    return Response::json(['status' => 407,'massage' => 'user found but publickey for parent notfound'],407); 

                }
             }
             else  return Response::json(['status' => 403,'massage' => 'user is band'],403);
            
             //else  return Response::json(['status' => 407,'massage' => 'user parent dont have public key'],407);
        } catch (\Throwable $th) {
              return   Response::json(['status' => 400,'massage' => 'user not found','error' =>  $th ],400);
        }
        
    }

    public function getcontact($id, Request $request)
    {
        $authUser =$this->Authorization($request);
        try {
            $coordinator=User::where('type_id', $this->userCoordinator)->where('id','!=',$authUser->id)->where('public_key','!=',null);
            //return Response::json($coordinatorUser);
            $chaef=User::where('id',  $authUser->parent_id)->where('public_key','!=',null);
            if( $authUser->type_id == $this->userChief){
                return Response::json(['status' => 200,'massage' => 'users found','sources' => [],'coordinator' => $coordinator->get(),'chaef' =>[]],200);
            }
            else{
            $user =User::where('parent_id', $id)->where('public_key','!=',null);
            return Response::json(['status' => 200,'massage' => 'users found','sources' => $user->get(),'coordinator' => $coordinator->get(),'chaef' => $chaef->get()],200);
            }
        } catch (\Throwable $th) {
             return  Response::json(['status' => 400,'massage' => 'user not found'],400);
        }
    }
        
    public function getUserMassages($id,$user, Request $request)
    {
        // Massage functionality removed
        return Response::json(['status' => 404, 'message' => 'Massage functionality removed'], 404);
    }
    
    public function getMassages($id, Request $request)
    {
        // Massage functionality removed
        return Response::json(['status' => 404, 'message' => 'Massage functionality removed'], 404);
    }
    public function ackUserMassages($sender,$receiver,$date)
    {
        // Massage functionality removed
        return Response::json(['status' => 404, 'message' => 'Massage functionality removed'], 404);
    }
    public function userLocation($id)
    {
        // Massage functionality removed
        return Response::json(['status' => 404, 'message' => 'Massage functionality removed'], 404);
    }
    public function addUserCard($card_id,$card,$user_id)
    {
        // date('Y-m-d H:i:s', strtotime($data['datetime']))
            try {
                $wallet = Wallet::where('user_id', $user_id)->first();
                $old_card = $wallet->card; 
                $new_card = $card; 
                $wallet->update(['card' => $old_card+$new_card]);
            
                 return Response::json(['status' => 200,'massage' => 'massage','data' =>   $wallet],200);

            } catch (\Throwable $th) {
                return $th;
                    return  Response::json(['status' => 400,'massage' => 'massage not found'],400);
            }
    }
    
    public function Authorization($request){
        $token = substr($request->header('Authorization') ,7);;
        try {
            $id = Crypt::decryptString($token) ;
        $authUser = User::where('id', $id) ? User::where('id', $id)->first() :0;
        if($authUser && !$authUser->is_band){
           return $authUser;
        }
        else
        return  Response::json(['status' => 401,'massage' => 'user not Authorize'],401);
        } catch (\Throwable $th) {
            return  Response::json(['status' => 401,'massage' => 'user not Authorize'],401);
        }
        }
    }