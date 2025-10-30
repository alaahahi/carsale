<?php

namespace App\Helpers;

use App\Models\UserType;
use App\Models\User;
use App\Models\ExpensesType;
use App\Models\SystemConfig;

class TenantDataHelper
{
    private static function isCacheEnabled(): bool
    {
        return (bool) config('tenant-cache.enabled', false);
    }

    private static function tenantCacheKey(string $baseKey): string
    {
        // Try to get current tenant id from stancl/tenancy helper or request merge
        $tenantId = function_exists('tenant') ? (tenant('id') ?? null) : null;
        if (!$tenantId && request()) {
            $tenant = request()->get('current_tenant');
            $tenantId = $tenant && isset($tenant->id) ? $tenant->id : null;
        }
        $tenantSuffix = $tenantId ?: 'central';
        return $tenantSuffix . '_' . $baseKey;
    }

    /**
     * الحصول على أنواع المستخدمين من قاعدة بيانات الـ tenant
     */
    public static function getUserTypes()
    {
        $key = self::tenantCacheKey('user_types_tenant');
        if (!self::isCacheEnabled()) {
            return UserType::pluck('name', 'id');
        }
        return cache()->remember($key, 3600, function () {
            return UserType::pluck('name', 'id');
        });
    }

    /**
     * الحصول على نوع مستخدم معين بالاسم من قاعدة بيانات الـ tenant
     */
    public static function getUserTypeId($name)
    {
        $cacheKey = self::tenantCacheKey("user_type_{$name}");
        if (!self::isCacheEnabled()) {
            return UserType::where('name', $name)->first()?->id;
        }
        return cache()->remember($cacheKey, 3600, function () use ($name) {
            return UserType::where('name', $name)->first()?->id;
        });
    }

    /**
     * الحصول على معرفات أنواع المستخدمين من قاعدة بيانات الـ tenant
     */
    public static function getUserTypeIds()
    {
        $key = self::tenantCacheKey('user_type_ids');
        $compute = function () {
            $userType = UserType::select('id', 'name')->get();
            return [
                'admin' => $userType->where('name', 'admin')->first()?->id,
                'seles' => $userType->where('name', 'seles')->first()?->id,
                'client' => $userType->where('name', 'client')->first()?->id,
                'account' => $userType->where('name', 'account')->first()?->id,
            ];
        };
        if (!self::isCacheEnabled()) {
            return $compute();
        }
        return cache()->remember($key, 3600, $compute);
    }

    /**
     * الحصول على الحسابات المحاسبية من قاعدة بيانات الـ tenant
     */
    public static function getAccountingUsers()
    {
        $key = self::tenantCacheKey('accounting_users');
        $compute = function () {
            $userAccount = self::getUserTypeId('account');
            
            if (!$userAccount) {
                return [
                    'main' => null,
                    'in' => null,
                    'out' => null,
                    'transfers' => null,
                    'outSupplier' => null,
                    'debtSupplier' => null,
                ];
            }
            
            return [
                'main' => User::with('wallet')->where('type_id', $userAccount)->where('email', 'main@account.com')->first(),
                'in' => User::with('wallet')->where('type_id', $userAccount)->where('email', 'in@account.com')->first(),
                'out' => User::with('wallet')->where('type_id', $userAccount)->where('email', 'out@account.com')->first(),
                'transfers' => User::with('wallet')->where('type_id', $userAccount)->where('email', 'transfers@account.com')->first(),
                'outSupplier' => User::with('wallet')->where('type_id', $userAccount)->where('email', 'supplier-out')->first(),
                'debtSupplier' => User::with('wallet')->where('type_id', $userAccount)->where('email', 'supplier-debt')->first(),
            ];
        };
        if (!self::isCacheEnabled()) {
            return $compute();
        }
        return cache()->remember($key, 3600, $compute);
    }

    /**
     * الحصول على إعدادات النظام من قاعدة بيانات الـ tenant
     */
    public static function getSystemConfig()
    {
        $key = self::tenantCacheKey('system_config');
        $compute = function () {
            $config = SystemConfig::first();
            
            if (!$config) {
                return [
                    'company_name' => 'Salam Jalal Ayoub Company',
                    'first_title_ar' => null,
                    'first_title_kr' => null,
                    'second_title_ar' => null,
                    'second_title_kr' => null,
                    'third_title_ar' => null,
                    'third_title_kr' => null,
                ];
            }
            
            return [
                'company_name' => $config->first_title_ar ?? 'Salam Jalal Ayoub Company',
                'first_title_ar' => $config->first_title_ar,
                'first_title_kr' => $config->first_title_kr,
                'second_title_ar' => $config->second_title_ar,
                'second_title_kr' => $config->second_title_kr,
                'third_title_ar' => $config->third_title_ar,
                'third_title_kr' => $config->third_title_kr,
            ];
        };
        if (!self::isCacheEnabled()) {
            return $compute();
        }
        return cache()->remember($key, 3600, $compute);
    }

    /**
     * مسح الكاش الخاص بالـ tenant
     */
    public static function clearCache()
    {
        $keys = [
            'user_types_tenant',
            'user_type_ids',
            'accounting_users',
            'system_config',
            'user_type_admin',
            'user_type_seles',
            'user_type_client',
            'user_type_account',
        ];
        foreach ($keys as $k) {
            cache()->forget(self::tenantCacheKey($k));
        }
    }

    /**
     * مسح الكاش عند التحديث
     */
    public static function clearCacheOnUpdate()
    {
        self::clearCache();
    }
}
