<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound(\App\Monitor\Services\ExceptionMonitor::class)) {
                app(\App\Monitor\Services\ExceptionMonitor::class)->log($e);
            }
        });

        // 419 Page Expired — أعد فتح صفحة الدخول بجلسة/توكن جديد
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->is('login') || $request->routeIs('login')) {
                return redirect()
                    ->to('/login')
                    ->with('status', 'انتهت صلاحية النموذج، أعد تسجيل الدخول.');
            }

            return redirect()
                ->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('status', 'انتهت صلاحية الصفحة، أعد المحاولة.');
        });
    }
}
