<?php

namespace App\Helpers;

use App\Models\TenantDatabaseConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EnsureTenantAdmin
{
    public static function defaultEmail(): string
    {
        return (string) env('TENANT_DEFAULT_ADMIN_EMAIL', 'admin@admin.com');
    }

    public static function defaultPassword(): string
    {
        return (string) env('TENANT_DEFAULT_ADMIN_PASSWORD', '12345678');
    }

    /**
     * يضمن وجود أدمن قابل للدخول على قاعدة التاجر ويعيد ضبط كلمة المرور.
     *
     * @return array{ok:bool,action:string,email:string,user_id:?int,database:?string,message:string}
     */
    public static function fix(TenantDatabaseConfig $config, ?string $email = null, ?string $password = null): array
    {
        $email = $email ?: self::defaultEmail();
        $password = $password ?: self::defaultPassword();
        $connectionName = 'ensure_admin_' . $config->id;

        try {
            config([
                "database.connections.{$connectionName}" => $config->getConnectionInfo(),
            ]);

            $db = DB::connection($connectionName);
            $db->getPdo();

            if (!$db->getSchemaBuilder()->hasTable('users')) {
                return [
                    'ok' => false,
                    'action' => 'missing_users_table',
                    'email' => $email,
                    'user_id' => null,
                    'database' => $config->database_name,
                    'message' => 'جدول users غير موجود — شغّل المايغريشن أولاً',
                ];
            }

            $adminTypeId = null;
            if ($db->getSchemaBuilder()->hasTable('user_type')) {
                $adminTypeId = $db->table('user_type')->where('name', 'admin')->value('id');
            }

            $user = $db->table('users')->where('email', $email)->first();
            $hash = Hash::make($password);

            $payload = [
                'password' => $hash,
                'updated_at' => now(),
            ];

            if ($db->getSchemaBuilder()->hasColumn('users', 'is_band')) {
                $payload['is_band'] = 0;
            }
            if ($db->getSchemaBuilder()->hasColumn('users', 'email_verified_at')) {
                $payload['email_verified_at'] = now();
            }
            if ($adminTypeId && $db->getSchemaBuilder()->hasColumn('users', 'type_id')) {
                $payload['type_id'] = $adminTypeId;
            }

            if ($user) {
                $db->table('users')->where('id', $user->id)->update($payload);
                $action = 'password_reset';
                $userId = (int) $user->id;
            } else {
                $insert = array_merge($payload, [
                    'name' => 'مدير النظام',
                    'email' => $email,
                    'created_at' => now(),
                ]);
                if ($db->getSchemaBuilder()->hasColumn('users', 'show_wallet')) {
                    $insert['show_wallet'] = true;
                }
                $userId = (int) $db->table('users')->insertGetId($insert);
                $action = 'admin_created';

                if ($db->getSchemaBuilder()->hasTable('wallets')) {
                    $walletExists = $db->table('wallets')->where('user_id', $userId)->exists();
                    if (!$walletExists) {
                        $db->table('wallets')->insert([
                            'user_id' => $userId,
                            'balance' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            Log::info('EnsureTenantAdmin OK', [
                'config_id' => $config->id,
                'database' => $config->database_name,
                'email' => $email,
                'action' => $action,
                'user_id' => $userId,
            ]);

            return [
                'ok' => true,
                'action' => $action,
                'email' => $email,
                'user_id' => $userId,
                'database' => $config->database_name,
                'message' => $action === 'admin_created'
                    ? 'تم إنشاء الأدمن بنجاح'
                    : 'تم إعادة ضبط كلمة مرور الأدمن',
            ];
        } catch (\Throwable $e) {
            Log::error('EnsureTenantAdmin failed', [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'action' => 'error',
                'email' => $email,
                'user_id' => null,
                'database' => $config->database_name,
                'message' => $e->getMessage(),
            ];
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
        }
    }

    /**
     * إصلاح حسب الدومين (مثل sarwan.intellij-app.com أو subdomain sarwan)
     * مهم: جدول tenant_database_configs موجود فقط في القاعدة المركزية
     */
    public static function fixByHost(string $hostOrSubdomain, ?string $email = null, ?string $password = null): array
    {
        $host = trim($hostOrSubdomain);
        $subdomain = explode('.', $host)[0] ?? $host;
        $central = config('tenancy.database.central_connection', 'mysql');

        $config = TenantDatabaseConfig::on($central)
            ->where(function ($q) use ($host, $subdomain) {
                $q->where('subdomain', $host)
                    ->orWhere('subdomain', $subdomain);
            })
            ->first();

        if (!$config) {
            // SubdomainHelper يقرأ من المركزية أيضاً (domains/tenants)
            $previousDefault = config('database.default');
            try {
                config(['database.default' => $central]);
                DB::purge($central);
                $tenantData = SubdomainHelper::getTenantAndDatabaseConfigByDomain($host);
                $config = $tenantData['database_config'] ?? null;
            } finally {
                config(['database.default' => $previousDefault]);
            }
        }

        if (!$config) {
            return [
                'ok' => false,
                'action' => 'config_not_found',
                'email' => $email ?: self::defaultEmail(),
                'user_id' => null,
                'database' => null,
                'message' => "لا يوجد TenantDatabaseConfig للدومين: {$host}",
            ];
        }

        return self::fix($config, $email, $password);
    }
}
