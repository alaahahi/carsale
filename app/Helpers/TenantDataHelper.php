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
     * مسار صورة عامة من public/img حسب اسم الملف في system_config
     */
    public static function resolvePublicImageUrl(?string $filename, string $default = 'logo-color1.png'): string
    {
        $name = trim((string) ($filename ?: $default));
        $name = basename(str_replace('\\', '/', $name));
        if ($name === '' || $name === '.' || $name === '..') {
            $name = $default;
        }

        return '/img/' . $name;
    }

    public static function defaultSystemConfig(): array
    {
        $defaultLogo = 'logo-color1.png';

        return [
            'company_name' => 'Salam Jalal Ayoub Company',
            'first_title_ar' => null,
            'first_title_kr' => null,
            'second_title_ar' => null,
            'second_title_kr' => null,
            'third_title_ar' => null,
            'third_title_kr' => null,
            'logo_image' => $defaultLogo,
            'login_bg_image' => $defaultLogo,
            'logo_url' => self::resolvePublicImageUrl($defaultLogo),
            'login_bg_url' => self::resolvePublicImageUrl($defaultLogo),
            'external_merchant_ids' => [],
        ];
    }

    private static function formatSystemConfig(?SystemConfig $config): array
    {
        if (!$config) {
            return self::defaultSystemConfig();
        }

        $logoImage = $config->logo_image ?: 'logo-color1.png';
        $loginBgImage = $config->login_bg_image ?: $logoImage;

        return [
            'company_name' => $config->first_title_ar ?? 'Salam Jalal Ayoub Company',
            'first_title_ar' => $config->first_title_ar,
            'first_title_kr' => $config->first_title_kr,
            'second_title_ar' => $config->second_title_ar,
            'second_title_kr' => $config->second_title_kr,
            'third_title_ar' => $config->third_title_ar,
            'third_title_kr' => $config->third_title_kr,
            'logo_image' => $logoImage,
            'login_bg_image' => $loginBgImage,
            'logo_url' => self::resolvePublicImageUrl($logoImage),
            'login_bg_url' => self::resolvePublicImageUrl($loginBgImage),
            'external_merchant_ids' => self::parseMerchantIds($config->external_merchant_ids),
        ];
    }

    private static function parseMerchantIds($raw): array
    {
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return array_map('intval', array_filter(
            array_map('trim', explode(',', (string) $raw)),
            fn ($id) => $id !== '' && is_numeric($id)
        ));
    }

    /**
     * الحصول على إعدادات النظام من قاعدة بيانات الـ tenant
     */
    public static function getSystemConfig()
    {
        $key = self::tenantCacheKey('system_config');
        $compute = function () {
            return self::formatSystemConfig(SystemConfig::first());
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
