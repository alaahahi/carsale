<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\Company;
use App\Models\Transactions;
use App\Models\SystemConfig;
use App\Models\Name;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Color;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CarConfigController extends Controller
{
    public function __construct()
    {
        $this->url = env('FRONTEND_URL');
        $this->userSeles = UserType::where('name', 'sales')->first()?->id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::all();
        return Inertia::render('CarConfig/Index', ['url' => $this->url]);
    }

    public function showCar()
    {
        $car_id = $_GET['car_id'] ?? 0;
        $data = Car::with('carmodel')->with('name')->with('user')->with('color')->with('company')->with('client')->with('transactions')->where('id', $car_id)->first();
        return Response::json($data, 200);
    }

    public function companyEdit($id)
    {
        // Profile functionality removed - placeholder for future implementation
        // $data = Profile::where('id',$id)->first();
        return Inertia::render('CarConfig/Edit', ['url' => $this->url, 'data' => null]);
    }

    public function saved()
    {
        try {
            $authUser = auth()?->user();
            if ($authUser) {
                $users = User::where('type_id', $this->userSeles)->get();
                return Inertia::render('FormRegistrationSaved', ['url' => $this->url, 'users' => $users]);
            } else {
                return Inertia::render('Auth/Login');
            }
        } catch (\Throwable $th) {
            return Inertia::render('Auth/Login');
        }
    }

    public function court()
    {
        try {
            $authUser = auth()?->user();
            if ($authUser) {
                $users = User::where('type_id', $this->userSeles)->get();
                return Inertia::render('FormRegistrationCourt', ['url' => $this->url, 'users' => $users]);
            } else {
                return Inertia::render('Auth/Login');
            }
        } catch (\Throwable $th) {
            return Inertia::render('Auth/Login');
        }
    }

    public function completed()
    {
        try {
            $authUser = auth()?->user();
            if ($authUser) {
                $users = User::where('type_id', $this->userSeles)->get();
                return Inertia::render('FormRegistrationCompleted', ['url' => $this->url, 'users' => $users]);
            } else {
                return Inertia::render('Auth/Login');
            }
        } catch (\Throwable $th) {
            return Inertia::render('Auth/Login');
        }
    }

    public function getIndex()
    {
        $data = Company::paginate(10);
        return Response::json($data, 200);
    }

    public function getIndexName()
    {
        $data = Name::paginate(10);
        return Response::json($data, 200);
    }

    public function getIndexModel()
    {
        $data = CarModel::paginate(10);
        return Response::json($data, 200);
    }

    public function getIndexColor()
    {
        $data = Color::paginate(10);
        return Response::json($data, 200);
    }

    public function getIndexSaved()
    {
        // Profile functionality removed - placeholder for future implementation
        // $data = Profile::with('user')->orderBy('no', 'DESC')->paginate(10);
        return Response::json(['data' => [], 'message' => 'Profile functionality temporarily removed'], 200);
    }

    public function getIndexAccountsSelas()
    {
        $user_id = $_GET['user_id'] ?? 0;
        $sales = User::with('wallet')->where('id', $user_id)->first();
        $transactions = Transactions::where('wallet_id', $sales?->wallet?->id);

        $data = $transactions->paginate(10);
        // Profile functionality removed - placeholder for future implementation
        // $profile_count = Profile::where('user_id', $sales?->id)->where('results',1)->count();
        $profile_count = 0;

        // Additional logic to retrieve sales data
        $salesData = [
            'totalAmount' => $transactions->sum('amount'),
            'count' => $profile_count,
            'total_sales' => $data?->total(),
            'current_page' => $data?->currentPage(),
            'per_page' => $data?->perPage(),
            'last_page' => $data?->lastPage(),
            'data' => $data?->items(),
            'sales' => $sales,
            'date' => Carbon::now()->format('Y-m-d')
        ];
        return Response::json($salesData, 200);
    }

    public function getIndexCompleted()
    {
        // Profile functionality removed - placeholder for future implementation
        // $user_id = $_GET['user_id'] ?? 0;
        // if($user_id){
        //     $data = Profile::with('user')->where('user_id',$user_id)->where('results',0)->orderBy('no', 'DESC')->paginate(10);
        // }else{
        //     $data = Profile::with('user')->orderBy('no', 'DESC')->where('results',0)->paginate(10);
        // }
        return Response::json(['data' => [], 'message' => 'Profile functionality temporarily removed'], 200);
    }

    public function companyDel($id)
    {
        $company = Company::find($id);
        if ($company) {
            $company->delete();
            return Response::json(['message' => 'Company deleted successfully'], 200);
        }
        return Response::json(['message' => 'Company not found'], 404);
    }

    public function delName($id)
    {
        $name = Name::find($id);
        if ($name) {
            $name->delete();
            return Response::json(['message' => 'Name deleted successfully'], 200);
        }
        return Response::json(['message' => 'Name not found'], 404);
    }

    public function delModel($id)
    {
        $model = CarModel::find($id);
        if ($model) {
            $model->delete();
            return Response::json(['message' => 'Model deleted successfully'], 200);
        }
        return Response::json(['message' => 'Model not found'], 404);
    }

    public function delColor($id)
    {
        $color = Color::find($id);
        if ($color) {
            $color->delete();
            return Response::json(['message' => 'Color deleted successfully'], 200);
        }
        return Response::json(['message' => 'Color not found'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $company = Company::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'status' => $request->status,
        ]);

        return Response::json(['message' => 'Company created successfully', 'data' => $company], 201);
    }

    public function storeName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:company,id',
            'status' => 'required|in:active,inactive',
        ]);

        $name = Name::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'company_id' => $request->company_id,
            'status' => $request->status,
        ]);

        return Response::json(['message' => 'Name created successfully', 'data' => $name], 201);
    }

    public function storeCarModel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $model = CarModel::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return Response::json(['message' => 'Car model created successfully', 'data' => $model], 201);
    }

    public function storeColor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $color = Color::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'status' => $request->status,
        ]);

        return Response::json(['message' => 'Color created successfully', 'data' => $color], 201);
    }

    public function storeEdit(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $company = Company::findOrFail($id);
        $company->update([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'status' => $request->status,
        ]);

        return Response::json(['message' => 'Company updated successfully', 'data' => $company], 200);
    }

    // Profile-related methods - placeholders for future implementation
    public function labResults($id)
    {
        // Profile functionality removed - placeholder for future implementation
        return Inertia::render('CarConfig/AddlabResults', ['url' => $this->url, 'profile_id' => $id, 'profile' => null]);
    }

    public function labResultsEdit($id)
    {
        // Profile functionality removed - placeholder for future implementation
        return Inertia::render('CarConfig/EditlabResults', ['url' => $this->url, 'profile_id' => $id, 'profile' => null, 'data' => null]);
    }

    public function doctorResults($id)
    {
        // Profile functionality removed - placeholder for future implementation
        return Inertia::render('CarConfig/AddDoctorResults', ['url' => $this->url, 'is_doctor' => true, 'profile' => null, 'profile_id' => $id, 'profiles' => null]);
    }

    public function doctorResultsEdit($id)
    {
        // Profile functionality removed - placeholder for future implementation
        return Inertia::render('CarConfig/EditDoctorResults', ['url' => $this->url, 'is_doctor' => true, 'profile' => null, 'profile_id' => $id, 'profiles' => null, 'data' => null]);
    }

    public function document($id)
    {
        // Profile functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Profile functionality temporarily removed'], 200);
    }

    public function showfile($id)
    {
        // Profile functionality removed - placeholder for future implementation
        $config = SystemConfig::first();
        $url = $this->url;
        return view('show', compact('url', 'config'));
    }

    public function sentToCourt($id)
    {
        // Profile functionality removed - placeholder for future implementation
        // Profile::where('id',$id)->update(['results'=>4]);
        return back()->with('success', 'شكراّ,تمت العملية بنجاح');
    }

    public function getProfiles(Request $request)
    {
        // Profile functionality removed - placeholder for future implementation
        // $term = $request->get('q');
        // $data = Profile::with('user')->orwhere('name', 'LIKE','%'.$term.'%')->orwhere('card_number', 'LIKE','%'.$term.'%')->orwhere('invoice_number',$term)->paginate(10);
        return response()->json(['data' => [], 'message' => 'Profile functionality temporarily removed']);
    }

    public function getProfilesSaved(Request $request)
    {
        // Profile functionality removed - placeholder for future implementation
        // $term = $request->get('q');
        // $data = Profile::with('user')->where('name', 'LIKE','%'.$term.'%')->orwhere('card_number', 'LIKE','%'.$term.'%')->orwhere('invoice_number',$term)->paginate(10);
        return response()->json(['data' => [], 'message' => 'Profile functionality temporarily removed']);
    }

    public function getProfilesCompleted(Request $request)
    {
        // Profile functionality removed - placeholder for future implementation
        // $term = $request->get('q');
        // $data = Profile::with('user')->where('name', 'LIKE','%'.$term.'%')->where('results',3)->orwhere('card_number', 'LIKE','%'.$term.'%')->where('results',3)->orwhere('invoice_number',$term)->where('results',3)->paginate(10);
        return response()->json(['data' => [], 'message' => 'Profile functionality temporarily removed']);
    }
}
