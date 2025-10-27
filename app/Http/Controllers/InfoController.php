<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use App\Models\User;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportInfo;
use App\Imports\CarDataImport;
use App\Exports\ExportInfo;
use Illuminate\Support\Facades\Log;

class InfoController extends Controller
{
    public function __construct()
    {
        $this->url = env('FRONTEND_URL');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/Index', ['url' => $this->url, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/Create', ['url' => $this->url, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Info functionality removed - placeholder for future implementation
        return Inertia::render('Info/Edit', ['url' => $this->url, 'id' => $id, 'message' => 'Info functionality temporarily removed']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }

    /**
     * Show upload form for importing cars
     */
    public function showUploadForm()
    {
        return Inertia::render('ImportCars', [
            'url' => $this->url
        ]);
    }

    /**
     * Import cars from Excel file
     */
    public function import(Request $request)
    {
        try {
            // التحقق من وجود الملف
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'يرجى اختيار ملف Excel للاستيراد'
                ], 400);
            }

            $file = $request->file('file');
            
            // التحقق من نوع الملف
            $extension = $file->getClientOriginalExtension();
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الملف غير مدعوم. يرجى استخدام ملف Excel (.xlsx, .xls) أو CSV'
                ], 400);
            }

            // تنفيذ الاستيراد
            $import = new CarDataImport();
            
            Excel::import($import, $file);
            
            // الحصول على النتائج
            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();
            $skipCount = $import->getSkipCount();
            
            // تحضير الرسالة
            $errorCount = count($errors);
            $message = "تم استيراد {$successCount} سيارة بنجاح";
            if ($errorCount > 0) {
                $message .= " مع {$errorCount} خطأ";
            }
            if ($skipCount > 0) {
                $message .= " وتم تخطي {$skipCount} صف";
            }

            // استدعاء callback في حال الفشل
            if (count($errors) > $successCount && $successCount == 0) {
                $this->handleFailedImport($errors, $request);
                return response()->json([
                    'success' => false,
                    'message' => 'فشل الاستيراد بالكامل. يرجى التحقق من الملف وحاول مرة أخرى.',
                    'errors' => $errors,
                    'success_count' => $successCount,
                    'error_count' => count($errors),
                    'skip_count' => $skipCount
                ], 400);
            }

            // استدعاء callback للحالات الناجحة جزئياً
            if (count($errors) > 0) {
                $this->handlePartialImport($errors, $successCount, $request);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => count($errors),
                'skip_count' => $skipCount,
                'errors' => $errors
            ], 200);

        } catch (\Exception $e) {
            Log::error('Car import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'file_name' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'unknown'
            ]);

            // استدعاء callback عند الفشل
            $this->handleFailedImport([['message' => $e->getMessage()]], $request);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * معالجة فشل الاستيراد بالكامل
     */
    private function handleFailedImport(array $errors, Request $request)
    {
        Log::warning('Car import failed - rolling back', [
            'errors_count' => count($errors),
            'errors' => $errors,
            'user_id' => auth()->id(),
            'file_name' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'unknown'
        ]);

        // يمكن إضافة إشعارات أو إرسال بريد إلكتروني هنا
        // notification::send(auth()->user(), new ImportFailedNotification($errors));
    }

    /**
     * معالجة الاستيراد الجزئي
     */
    private function handlePartialImport(array $errors, int $successCount, Request $request)
    {
        Log::info('Car import partial success', [
            'success_count' => $successCount,
            'error_count' => count($errors),
            'errors' => $errors,
            'user_id' => auth()->id()
        ]);

        // يمكن إضافة إشعارات هنا
        // notification::send(auth()->user(), new ImportPartialSuccessNotification($successCount, $errors));
    }

    /**
     * Export infos to Excel
     */
    public function exportInfos(Request $request)
    {
        // Info functionality removed - placeholder for future implementation
        return Response::json(['message' => 'Info functionality temporarily removed'], 200);
    }
}
