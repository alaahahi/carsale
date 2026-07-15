<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Helpers\TenantDataHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view (Blade — full page, بدون Inertia).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        if (Auth::check()) {
            return redirect()->to(RouteServiceProvider::HOME);
        }

        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'systemConfig' => TenantDataHelper::getSystemConfig(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && (int) $user->is_band === 1) {
            return back()->withErrors([
                'email' => 'هذا الحساب محظور.',
            ])->onlyInput('email');
        }

        $request->authenticate();
        $request->session()->regenerate();

        // توجيه كامل للصفحة (ليس Inertia XHR) — يمنع حلقة login ↔ dashboard
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
