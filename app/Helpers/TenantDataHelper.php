<?php

namespace App\Helpers;

use App\Models\UserType;
use App\Models\User;
use App\Models\ExpensesType;

class TenantDataHelper
{
    /**
     * الحصول على أنواع المستخدمين من قاعدة بيانات الـ tenant
     */
    public static function getUserTypes()
    {
        return cache()->remember('user_types_tenant', 3600, function () {
            return UserType::pluck('name', 'id');
        });
    }

    /**
     * الحصول على نوع مستخدم معين بالاسم من قاعدة بيانات الـ tenant
     */
    public static function getUserTypeId($name)
    {
        $cacheKey = "user_type_{$name}";
        
        return cache()->remember($cacheKey, 3600, function () use ($name) {
            return UserType::where('name', $name)->first()?->id;
        });
    }

    /**
     * الحصول على معرفات أنواع المستخدمين من قاعدة بيانات الـ tenant
     */
    public static function getUserTypeIds()
    {
        return cache()->remember('user_type_ids', 3600, function () {
            $userType = UserType::select('id', 'name')->get();
            return [
                'admin' => $userType->where('name', 'admin')->first()?->id,
                'seles' => $userType->where('name', 'seles')->first()?->id,
                'client' => $userType->where('name', 'client')->first()?->id,
                'account' => $userType->where('name', 'account')->first()?->id,
            ];
        });
    }

    /**
     * الحصول على الحسابات المحاسبية من قاعدة بيانات الـ tenant
     */
    public static function getAccountingUsers()
    {
        return cache()->remember('accounting_users', 3600, function () {
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
        });
    }

    /**
     * مسح الكاش الخاص بالـ tenant
     */
    public static function clearCache()
    {
        cache()->forget('user_types_tenant');
        cache()->forget('user_type_ids');
        cache()->forget('accounting_users');
        cache()->forget('user_type_admin');
        cache()->forget('user_type_seles');
        cache()->forget('user_type_client');
        cache()->forget('user_type_account');
    }

    /**
     * مسح الكاش عند التحديث
     */
    public static function clearCacheOnUpdate()
    {
        self::clearCache();
    }
}
