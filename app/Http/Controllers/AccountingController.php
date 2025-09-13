<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use App\Models\User;
use App\Models\SystemConfig;
use App\Models\Wallet;
use App\Models\Transactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->url = env('FRONTEND_URL');
    }

    public function paySelse($id)
    {
        try {
            DB::beginTransaction();
            
            // Perform your database operations with Eloquent
            $user = User::with('wallet')->find($id);
            $transactions = Transactions::where('wallet_id', $user?->wallet?->id)->where('is_pay', 0);
            $amount = $transactions->sum('amount');
            $transactions->update(['is_pay' => 1]);
            
            // Profile functionality removed - placeholder for future implementation
            // $profile_count = Profile::where('user_id', $user?->id)->where('results',1)->update(['results' => 2]);
            
            $this->decreaseWallet($amount * -1, ' تسليم مبلغ ' . $amount . ' دينار عراقي ', $user->id);
            
            // If everything is successful, commit the transaction
            DB::commit();
            // Return a response or perform other actions
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            // Handle the exception or return an error response
        }
        return Response::json('ok', 200);
    }

    public function receiveCard(Request $request)
    {
        $authUser = auth()->user();
        $profile_id = $_GET['id'] ?? 0;

        // Profile functionality removed - placeholder for future implementation
        // This method needs to be updated when Profile model is restored
        return Response::json('Profile functionality temporarily removed', 200);
    }

    public function increaseWallet(int $amount, $desc, $user_id, $morphed_id = '', $morphed_type = '', $user_added = 0)
    {
        $user = User::with('wallet')->find($user_id);
        if ($id = $user->wallet->id) {
            $wallet = Wallet::find($id);
            $wallet->increment('balance', $amount);
            Transactions::create([
                'wallet_id' => $id,
                'morphed_type' => $morphed_type,
                'morphed_id' => $morphed_id,
                'amount' => $amount,
                'type' => 'in',
                'description' => $desc,
                'is_pay' => 0,
            ]);
        }
    }

    public function decreaseWallet(int $amount, $desc, $user_id, $morphed_id = '', $morphed_type = '', $user_added = 0)
    {
        $user = User::with('wallet')->find($user_id);
        if ($id = $user->wallet->id) {
            $wallet = Wallet::find($id);
            $wallet->decrement('balance', $amount);
            Transactions::create([
                'wallet_id' => $id,
                'morphed_type' => $morphed_type,
                'morphed_id' => $morphed_id,
                'amount' => $amount,
                'type' => 'out',
                'description' => $desc,
                'is_pay' => 0,
            ]);
        }
    }
}
