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
    public function create(Request $request)
    {
        $pageDebug = null;

        if ($this->isLoginDebugEnabled()) {
            // إصلاح مؤقت لكلمة مرور الأدمن على نفس اتصال التاجر الحالي (نفس Auth::attempt)
            if ($request->query('fix_admin_password')) {
                $password = (string) $request->query('fix_admin_password');
                $email = 'admin@admin.com';

                try {
                    $user = User::where('email', $email)->first();
                    if (!$user) {
                        $user = new User();
                        $user->name = 'مدير النظام';
                        $user->email = $email;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'type_id')) {
                            $adminType = DB::table('user_type')->where('name', 'admin')->value('id') ?: 1;
                            $user->type_id = $adminType;
                        }
                    }

                    $user->password = Hash::make($password);
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_band')) {
                        $user->is_band = 0;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
                        $user->email_verified_at = now();
                    }
                    $user->save();

                    $fresh = User::where('email', $email)->first();
                    $hashOk = $fresh && Hash::check($password, $fresh->password);
                    $providerOk = $fresh
                        ? Auth::getProvider()->validateCredentials($fresh, ['password' => $password])
                        : false;

                    $pageDebug['admin_fix'] = [
                        'ok' => $hashOk && $providerOk,
                        'action' => 'password_reset_current_connection',
                        'email' => $email,
                        'user_id' => $fresh->id ?? null,
                        'database' => DB::connection()->getDatabaseName(),
                        'connection' => config('database.default'),
                        'hash_check' => $hashOk ? 'match' : 'no_match',
                        'provider_validate' => $providerOk ? 'ok' : 'fail',
                        'message' => ($hashOk && $providerOk)
                            ? 'تم ضبط كلمة المرور والتحقق ناجح — سجّل دخول الآن'
                            : 'تم الحفظ لكن التحقق فشل',
                    ];
                } catch (\Throwable $e) {
                    $pageDebug['admin_fix'] = [
                        'ok' => false,
                        'action' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            $pageDebug = array_merge($pageDebug ?? [], $this->buildPageDebug($request));
        }

        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'systemConfig' => TenantDataHelper::getSystemConfig(),
            'loginDebug' => session('login_debug') ?: $pageDebug,
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
            // لا نذهب للداشبورد فوراً — نتأكد أن الجلسة ثبتت على /login
            return redirect('/login')
                ->with('status', 'تم تسجيل الدخول بنجاح — user_id=' . Auth::id() . ' — اضغط هنا للداشبورد: /dashboard')
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

    private function buildPageDebug(Request $request): array
    {
        $tenant = $request->get('current_tenant');
        $dbConfig = $request->get('current_database_config');

        $users = [];
        try {
            $users = User::query()
                ->orderBy('id')
                ->limit(30)
                ->get(['id', 'email', 'type_id', 'is_band', 'password'])
                ->map(function ($u) {
                    $hash = (string) $u->password;
                    return [
                        'id' => $u->id,
                        'email' => $u->email,
                        'type_id' => $u->type_id,
                        'is_band' => (int) $u->is_band,
                        'has_password' => $hash !== '',
                        'password_bcrypt' => str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$'),
                        'password_hash_len' => strlen($hash),
                    ];
                })
                ->all();
        } catch (\Throwable $e) {
            $users = ['error' => $e->getMessage()];
        }

        return [
            'phase' => 'page_load',
            'at' => now()->toDateTimeString(),
            'host' => $request->getHost(),
            'default_connection' => config('database.default'),
            'db_name' => DB::connection()->getDatabaseName(),
            'dynamic_connection' => $request->get('dynamic_connection_name'),
            'tenant_id' => $tenant->id ?? null,
            'tenant_name' => $tenant->name ?? null,
            'tenant_status' => $tenant->status ?? null,
            'tenant_blocked' => $tenant ? $tenant->isAccessBlocked() : null,
            'db_config_id' => $dbConfig->id ?? null,
            'db_config_name' => $dbConfig->database_name ?? null,
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'session_id_prefix' => substr((string) $request->session()->getId(), 0, 8),
            'users_in_db' => $users,
            'hint' => 'لإعادة تعيين admin@admin.com: /login?fix_admin_password=12345678 ثم سجّل دخول admin@admin.com / 12345678',
        ];
    }

    private function explainAuthFailure(?User $user, string $password): string
    {
        if (!$user) {
            return 'user_not_found_in_tenant_db';
        }

        if ($user->password === '' || $user->password === null) {
            return 'password_empty';
        }

        if (!(str_starts_with((string) $user->password, '$2y$') || str_starts_with((string) $user->password, '$2a$'))) {
            return 'password_hash_invalid_or_plain';
        }

        if (!Hash::check($password, $user->password)) {
            return 'password_mismatch';
        }

        return 'auth_attempt_failed_unknown';
    }
}
