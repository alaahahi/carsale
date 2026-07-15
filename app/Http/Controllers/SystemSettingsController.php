<?php

namespace App\Http\Controllers;

use App\Helpers\TenantDataHelper;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class SystemSettingsController extends Controller
{
    protected $userAdmin;
    protected $url;

    public function __construct()
    {
        $this->url = env('FRONTEND_URL');

        // Capture tenant user type IDs after tenancy init
        $this->middleware(function ($request, $next) {
            $userTypeIds = TenantDataHelper::getUserTypeIds();
            $this->userAdmin = $userTypeIds['admin'];
            return $next($request);
        });
    }

    private function ensureAdmin(): void
    {
        $user = auth()->user();
        if (!$user || !$this->userAdmin || (int) $user->type_id !== (int) $this->userAdmin) {
            abort(403, 'غير مصرح');
        }
    }

    private function ensureMigrationsAllowed(): void
    {
        // حماية إضافية: لا نسمح بتنفيذ مايغريشن من الواجهة إلا بفلاغ صريح
        if (!filter_var(env('SYSTEM_SETTINGS_ALLOW_MIGRATIONS', false), FILTER_VALIDATE_BOOL)) {
            abort(403, 'تشغيل المايغريشن من الواجهة غير مفعل');
        }
    }

    private function logPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    public function index()
    {
        $this->ensureAdmin();

        return Inertia::render('System/Settings', [
            'url' => $this->url,
            'importCarsUrl' => route('car.import.form'),
            'migrationsUiEnabled' => (bool) filter_var(env('SYSTEM_SETTINGS_ALLOW_MIGRATIONS', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    private function detectExistingDataWarning(): array
    {
        $connectionName = DB::getDefaultConnection();
        $warning = [
            'has_other_tables' => false,
            'has_nonempty_tables' => false,
            'example_table' => null,
            'database' => $connectionName,
        ];

        try {
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(function ($table) {
                return array_values((array) $table)[0] ?? null;
            }, $tables);
            $tableNames = array_values(array_filter($tableNames));

            $other = array_values(array_filter($tableNames, fn ($t) => $t !== 'migrations'));
            $warning['has_other_tables'] = count($other) > 0;
            $warning['example_table'] = $other[0] ?? null;

            // Try to detect non-empty tables using information_schema (MySQL/MariaDB)
            $row = DB::selectOne("
                SELECT TABLE_NAME, TABLE_ROWS
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME <> 'migrations'
                  AND TABLE_ROWS > 0
                LIMIT 1
            ");
            if ($row && isset($row->TABLE_NAME)) {
                $warning['has_nonempty_tables'] = true;
                $warning['example_table'] = $warning['example_table'] ?? $row->TABLE_NAME;
            }
        } catch (\Throwable $e) {
            // If cannot detect, keep false and continue
        }

        return $warning;
    }

    public function migrationsList()
    {
        $this->ensureAdmin();

        $connectionName = DB::getDefaultConnection();
        $files = collect(File::files(database_path('migrations')))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.php'))
            ->sortBy(fn ($f) => $f->getFilename())
            ->values();

        $migrationNames = $files->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))->all();

        $ran = [];
        try {
            $ran = DB::table('migrations')
                ->whereIn('migration', $migrationNames)
                ->select(['migration', 'batch'])
                ->get()
                ->keyBy('migration')
                ->toArray();
        } catch (\Throwable $e) {
            $ran = [];
        }

        $list = $files->map(function ($file) use ($ran) {
            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $isRan = isset($ran[$name]);
            return [
                'name' => $name,
                'file' => $file->getFilename(),
                'path' => 'database/migrations/' . $file->getFilename(),
                'status' => $isRan ? 'ran' : 'pending',
                'batch' => $isRan ? ($ran[$name]->batch ?? null) : null,
            ];
        });

        $warning = $this->detectExistingDataWarning();

        return Response::json([
            'success' => true,
            'database' => $connectionName,
            'warning' => $warning,
            'migrations' => $list,
        ], 200);
    }

    public function runOneMigration(Request $request)
    {
        $this->ensureAdmin();
        $this->ensureMigrationsAllowed();

        $request->validate([
            'migration' => 'required|string',
        ]);

        $migrationName = trim((string) $request->migration);
        $connectionName = DB::getDefaultConnection();
        $filePath = database_path('migrations/' . $migrationName . '.php');
        if (!file_exists($filePath)) {
            return Response::json([
                'success' => false,
                'message' => 'ملف المايغريشن غير موجود',
            ], 404);
        }

        // If already ran, block to avoid confusion (can be changed later)
        $alreadyRan = false;
        try {
            $alreadyRan = DB::table('migrations')->where('migration', $migrationName)->exists();
        } catch (\Throwable $e) {
            $alreadyRan = false;
        }
        if ($alreadyRan) {
            return Response::json([
                'success' => false,
                'message' => 'هذا المايغريشن منفذ مسبقاً (Ran).',
            ], 409);
        }

        $warning = $this->detectExistingDataWarning();

        Log::warning('UI single migration requested', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'database' => $connectionName,
            'migration' => $migrationName,
            'tenant' => function_exists('tenant') ? tenant('id') : null,
            'warning' => $warning,
        ]);

        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path' => 'database/migrations/' . $migrationName . '.php',
            '--force' => true,
        ]);

        $output = Artisan::output();

        Log::warning('UI single migration finished', [
            'user_id' => auth()->id(),
            'database' => $connectionName,
            'migration' => $migrationName,
            'output' => $output,
        ]);

        return Response::json([
            'success' => true,
            'database' => $connectionName,
            'migration' => $migrationName,
            'warning' => $warning,
            'output' => $output,
        ], 200);
    }

    public function getLogs(Request $request)
    {
        $this->ensureAdmin();

        $maxBytes = (int) $request->get('max_bytes', 200000); // ~200KB
        $path = $this->logPath();

        if (!file_exists($path)) {
            return Response::json([
                'success' => true,
                'path' => $path,
                'content' => '',
                'size' => 0,
            ], 200);
        }

        $size = filesize($path) ?: 0;
        $readBytes = min($size, max(1000, $maxBytes));

        $content = '';
        $fp = fopen($path, 'rb');
        if ($fp) {
            if ($size > $readBytes) {
                fseek($fp, -$readBytes, SEEK_END);
            }
            $content = stream_get_contents($fp) ?: '';
            fclose($fp);
        }

        return Response::json([
            'success' => true,
            'path' => $path,
            'content' => $content,
            'size' => $size,
        ], 200);
    }

    public function clearLogs(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'confirm' => 'required|string',
        ]);

        if (trim((string) $request->confirm) !== 'CLEAR') {
            return Response::json([
                'success' => false,
                'message' => 'نص التأكيد غير صحيح. اكتب CLEAR للتفريغ.',
            ], 422);
        }

        $path = $this->logPath();
        @file_put_contents($path, '');

        Log::warning('Logs cleared from UI', [
            'user_id' => auth()->id(),
            'path' => $path,
        ]);

        return Response::json([
            'success' => true,
            'message' => 'تم تفريغ اللوج بنجاح',
        ], 200);
    }

    public function getBranding()
    {
        $this->ensureAdmin();

        return Response::json([
            'success' => true,
            'branding' => TenantDataHelper::getSystemConfig(),
        ], 200);
    }

    public function updateBranding(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'logo_image' => 'nullable|string|max:255',
            'login_bg_image' => 'nullable|string|max:255',
        ]);

        if (!\Illuminate\Support\Facades\Schema::hasColumn('system_config', 'logo_image')
            || !\Illuminate\Support\Facades\Schema::hasColumn('system_config', 'login_bg_image')) {
            return Response::json([
                'success' => false,
                'message' => 'أعمدة الشعار غير موجودة. شغّل المايغريشن: 2026_07_05_000001_add_logo_fields_to_system_config_table',
            ], 422);
        }

        $config = SystemConfig::query()->first();
        if (!$config) {
            $config = SystemConfig::create([]);
        }

        $logo = trim((string) $request->input('logo_image', ''));
        $bg = trim((string) $request->input('login_bg_image', ''));

        $config->update([
            'logo_image' => $logo !== '' ? basename(str_replace('\\', '/', $logo)) : null,
            'login_bg_image' => $bg !== '' ? basename(str_replace('\\', '/', $bg)) : null,
        ]);

        TenantDataHelper::clearCacheOnUpdate();

        return Response::json([
            'success' => true,
            'message' => 'تم حفظ إعدادات الشعار والخلفية',
            'branding' => TenantDataHelper::getSystemConfig(),
        ], 200);
    }
}

