<?php

namespace App\Helpers;

use App\Models\TenantDatabaseConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynamicDatabaseHelper
{
    /**
     * تطبيق إعدادات قاعدة البيانات الديناميكية مباشرة
     */
    public static function setConnection(TenantDatabaseConfig $config): string
    {
        $connectionName = 'dynamic_connection_' . $config->id;
        $connectionInfo = $config->getConnectionInfo();
        
        // تحديث إعدادات الاتصال
        config([
            "database.connections.{$connectionName}" => $connectionInfo
        ]);

        // تعيين الاتصال الافتراضي للـ tenant
        config(['database.default' => $connectionName]);

        return $connectionName;
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
        $connectionName = self::getConnectionBySubdomain($subdomain);
        
        if (!$connectionName) {
            throw new \Exception("لم يتم العثور على إعدادات قاعدة البيانات للـ subdomain: {$subdomain}");
        }

        try {
            return DB::connection($connectionName)->transaction($callback);
        } catch (\Exception $e) {
            Log::error('Dynamic Database Query Error', [
                'subdomain' => $subdomain,
                'connection_name' => $connectionName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            $connectionName = self::getConnectionBySubdomain($subdomain);
            
            if (!$connectionName) {
                return false;
            }

            $connection = DB::connection($connectionName);
            $connection->getPdo();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Connection Test Failed', [
                'subdomain' => $subdomain,
                'error' => $e->getMessage()
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
        $stats = [
            'total_configs' => $configs->count(),
            'active_configs' => $configs->where('is_active', true)->count(),
            'inactive_configs' => $configs->where('is_active', false)->count(),
            'by_driver' => $configs->groupBy('driver')->map->count(),
        ];

        return $stats;
    }
}
