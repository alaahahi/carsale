<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Helpers\TenantDataHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view (Blade — full page, بدون Inertia).
     */
    public function create()
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'systemConfig' => TenantDataHelper::getSystemConfig(),
            'loginDebug' => session('login_debug'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');
        $debug = $this->buildLoginDebug($request, $email, $password);

        Log::channel('single')->info('LOGIN_DEBUG', $debug);

        $user = User::where('email', $email)->first();

        if ($user && (int) $user->is_band === 1) {
            $debug['result'] = 'banned';
            $debug['fail_reason'] = 'is_band=1';
            return back()
                ->withErrors(['email' => 'هذا الحساب محظور.'])
                ->with('login_debug', $debug)
                ->onlyInput('email');
        }

        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            $debug['result'] = 'auth_failed';
            $debug['fail_reason'] = $this->explainAuthFailure($user, $password);
            $debug['hash_check'] = $user
                ? (Hash::check($password, $user->password) ? 'match' : 'no_match')
                : 'no_user';

            Log::channel('single')->warning('LOGIN_DEBUG_FAILED', $debug);

            return back()
                ->withErrors($e->errors())
                ->with('login_debug', $debug)
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $debug['result'] = 'success';
        $debug['auth_check_after'] = Auth::check();
        $debug['auth_id_after'] = Auth::id();
        Log::channel('single')->info('LOGIN_DEBUG_SUCCESS', $debug);

        if ($this->isLoginDebugEnabled()) {
            // نبقي الديبقغ لطلب واحد بعد النجاح عبر query (للمقارنة)
            return redirect()
                ->intended(RouteServiceProvider::HOME)
                ->with('login_debug', $debug);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
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

    private function isLoginDebugEnabled(): bool
    {
        return (bool) config('app.login_debug', env('LOGIN_DEBUG', true));
    }

    private function buildLoginDebug(Request $request, string $email, string $password): array
    {
        $user = User::where('email', $email)->first();
        $tenant = $request->get('current_tenant');
        $dbConfig = $request->get('current_database_config');
        $hash = $user?->password;

        return [
            'at' => now()->toDateTimeString(),
            'host' => $request->getHost(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'default_connection' => config('database.default'),
            'db_name' => DB::connection()->getDatabaseName(),
            'dynamic_connection' => $request->get('dynamic_connection_name'),
            'tenant_id' => $tenant->id ?? null,
            'tenant_name' => $tenant->name ?? null,
            'tenant_status' => $tenant->status ?? null,
            'tenant_blocked' => $tenant ? $tenant->isAccessBlocked() : null,
            'db_config_id' => $dbConfig->id ?? null,
            'db_config_name' => $dbConfig->database_name ?? null,
            'db_config_active' => $dbConfig->is_active ?? null,
            'email_input' => $email,
            'password_length' => strlen($password),
            'user_found' => (bool) $user,
            'user_id' => $user->id ?? null,
            'user_type_id' => $user->type_id ?? null,
            'user_is_band' => $user->is_band ?? null,
            'user_is_band_int' => $user ? (int) $user->is_band : null,
            'password_hash_prefix' => $hash ? substr($hash, 0, 7) : null,
            'password_hash_length' => $hash ? strlen($hash) : 0,
            'password_looks_bcrypt' => $hash ? str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') : false,
            'hash_check' => ($user && $hash) ? (Hash::check($password, $hash) ? 'match' : 'no_match') : 'skipped',
            'users_count_same_email' => User::where('email', $email)->count(),
            'session_id_prefix' => substr((string) $request->session()->getId(), 0, 8),
        ];
    }

    private function explainAuthFailure(?User $user, string $password): string
    {
        if (!$user) {
            return 'user_not_found_in_tenant_db';
        }

        if (!(str_starts_with((string) $user->password, '$2y$') || str_starts_with((string) $user->password, '$2a$'))) {
            return 'password_hash_invalid_or_plain';
        }

        if ($user->password === '' || $user->password === null) {
            return 'password_empty';
        }

        if (!Hash::check($password, $user->password)) {
            return 'password_mismatch';
        }

        return 'auth_attempt_failed_unknown';
    }
}
