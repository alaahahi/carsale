<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\TenantDataHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view (Blade — full page, بدون Inertia).
     */
    public function create(Request $request)
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'systemConfig' => TenantDataHelper::getSystemConfig(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $email = trim((string) $request->input('email'));
        $user = \App\Models\User::where('email', $email)->first();

        if ($user && (int) $user->is_band === 1) {
            return back()
                ->withErrors(['email' => 'هذا الحساب محظور.'])
                ->onlyInput('email');
        }

        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->onlyInput('email');
        }

        $authed = Auth::user();
        $request->session()->regenerate();
        if ($authed) {
            Auth::login($authed, true);
        }
        $request->session()->save();

        return redirect()->to(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
