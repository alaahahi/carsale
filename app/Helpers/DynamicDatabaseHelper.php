<?php

namespace App\Helpers;

use App\Models\TenantDatabaseConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynamicDatabaseHelper
{
    protected static ?string $previousDefault = null;

    protected static ?string $activeConnection = null;

    /**
     * تطبيق إعدادات قاعدة البيانات الديناميكية مباشرة
     */
    public static function setConnection(TenantDatabaseConfig $config): string
    {
        // أغلق اتصالاً ديناميكياً سابقاً إن وُجد (مهم في PHP-FPM / أوامر متعددة التجار)
        if (self::$activeConnection) {
            self::releaseConnection(self::$activeConnection);
        }

        $connectionName = 'dynamic_connection_' . $config->id;
        $connectionInfo = $config->getConnectionInfo();

        self::$previousDefault = config('database.default');

        config([
            "database.connections.{$connectionName}" => $connectionInfo,
            'database.default' => $connectionName,
        ]);

        self::$activeConnection = $connectionName;

        return $connectionName;
    }

    /**
     * إغلاق وتنظيف اتصال ديناميكي (disconnect + purge)
     */
    public static function releaseConnection(?string $connectionName = null): void
    {
        $name = $connectionName ?: self::$activeConnection;
        if (!$name) {
            return;
        }

        try {
            DB::disconnect($name);
        } catch (\Throwable $e) {
            Log::debug('DB disconnect warning', [
                'connection' => $name,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            DB::purge($name);
        } catch (\Throwable $e) {
            Log::debug('DB purge warning', [
                'connection' => $name,
                'error' => $e->getMessage(),
            ]);
        }

        $connections = config('database.connections', []);
        if (isset($connections[$name])) {
            unset($connections[$name]);
            config(['database.connections' => $connections]);
        }

        if (self::$activeConnection === $name) {
            if (self::$previousDefault) {
                config(['database.default' => self::$previousDefault]);
            }
            self::$activeConnection = null;
            self::$previousDefault = null;
        }
    }

    /**
     * تنفيذ عمل على اتصال مؤقت ثم إغلاقه دائماً
     */
    public static function usingConnection(TenantDatabaseConfig $config, callable $callback, ?string $prefix = null)
    {
        $connectionName = ($prefix ?: 'tmp_tenant_') . $config->id;
        $previousDefault = config('database.default');

        config([
            "database.connections.{$connectionName}" => $config->getConnectionInfo(),
        ]);

        try {
            return $callback($connectionName, DB::connection($connectionName));
        } finally {
            try {
                DB::disconnect($connectionName);
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                DB::purge($connectionName);
            } catch (\Throwable $e) {
                // ignore
            }

            $connections = config('database.connections', []);
            unset($connections[$connectionName]);
            config([
                'database.connections' => $connections,
                'database.default' => $previousDefault,
            ]);
        }
    }

    /**
     * الحصول على الاتصال الديناميكي بناءً على الـ subdomain
     */
    public static function getConnectionBySubdomain(string $subdomain): ?string
    {
        $config = TenantDatabaseConfig::findBySubdomain($subdomain);

        if (!$config) {
            return null;
        }

        return self::setConnection($config);
    }

    /**
     * استخدام الاتصال الديناميكي لاستعلام معين
     */
    public static function queryBySubdomain(string $subdomain, callable $callback)
    {
        $config = TenantDatabaseConfig::findBySubdomain($subdomain);

        if (!$config) {
            throw new \Exception("لم يتم العثور على إعدادات قاعدة البيانات للـ subdomain: {$subdomain}");
        }

        try {
            return self::usingConnection($config, function ($connectionName) use ($callback) {
                return DB::connection($connectionName)->transaction($callback);
            }, 'query_');
        } catch (\Exception $e) {
            Log::error('Dynamic Database Query Error', [
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * الحصول على بيانات من جداول متعددة
     */
    public static function getProfileData(string $subdomain, string $profileNo)
    {
        return self::queryBySubdomain($subdomain, function () use ($profileNo) {
            return DB::connection()
                ->table('profile')
                ->join('results', 'profile.id', '=', 'results.profile_id')
                ->join('doctor_reslts', 'profile.id', '=', 'doctor_reslts.profile_id')
                ->where('profile.no', $profileNo)
                ->select('profile.*', 'doctor_reslts.*', 'results.*')
                ->get();
        });
    }

    /**
     * اختبار الاتصال بالـ subdomain
     */
    public static function testConnection(string $subdomain): bool
    {
        try {
            $config = TenantDatabaseConfig::findBySubdomain($subdomain);
            if (!$config) {
                return false;
            }

            return (bool) self::usingConnection($config, function ($connectionName, $connection) {
                $connection->getPdo();
                return true;
            }, 'test_');
        } catch (\Exception $e) {
            Log::error('Connection Test Failed', [
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * الحصول على معلومات الاتصال بالـ subdomain
     */
    public static function getConnectionInfo(string $subdomain): ?array
    {
        $config = TenantDatabaseConfig::findBySubdomain($subdomain);

        if (!$config) {
            return null;
        }

        return $config->getSafeConnectionInfo();
    }

    /**
     * تسجيل العملية
     */
    public static function logOperation(string $subdomain, string $operation, array $data = [])
    {
        Log::channel('apiprofile')->info('Dynamic Database Operation', [
            'subdomain' => $subdomain,
            'operation' => $operation,
            'data' => $data,
            'timestamp' => now(),
        ]);
    }

    /**
     * الحصول على جميع الإعدادات النشطة
     */
    public static function getAllActiveConfigs()
    {
        return TenantDatabaseConfig::getActiveConfigs();
    }

    /**
     * التحقق من وجود إعدادات للـ subdomain
     */
    public static function hasConfig(string $subdomain): bool
    {
        return TenantDatabaseConfig::findBySubdomain($subdomain) !== null;
    }

    /**
     * الحصول على إحصائيات الاتصالات
     */
    public static function getConnectionStats()
    {
        $configs = TenantDatabaseConfig::getActiveConfigs();

        return [
            'total_configs' => $configs->count(),
            'active_configs' => $configs->where('is_active', true)->count(),
            'inactive_configs' => $configs->where('is_active', false)->count(),
            'by_driver' => $configs->groupBy('driver')->map->count(),
            'current_dynamic' => self::$activeConnection,
        ];
    }

    public static function getActiveConnectionName(): ?string
    {
        return self::$activeConnection;
    }
}
