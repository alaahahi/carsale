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
     * يضمن وجود أدمن id=1 قابل للدخول على قاعدة التاجر ويعيد ضبط كلمة المرور.
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

            $result = self::ensureAdminIdOne($db, $email, $payload);
            $userId = $result['user_id'];
            $action = $result['action'];

            if ($db->getSchemaBuilder()->hasTable('wallets')) {
                $walletExists = $db->table('wallets')->where('user_id', 1)->exists();
                if (!$walletExists) {
                    $db->table('wallets')->insert([
                        'user_id' => 1,
                        'balance' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
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
                    ? 'تم إنشاء الأدمن (id=1) بنجاح'
                    : 'تم ضبط الأدمن على id=1 وإعادة كلمة المرور',
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
     * الأدمن يجب أن يكون دائماً users.id = 1 (متطلبات النظام المحاسبي/الصلاحيات).
     *
     * @return array{user_id:int,action:string}
     */
    private static function ensureAdminIdOne($db, string $email, array $payload): array
    {
        $db->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $adminByEmail = $db->table('users')->where('email', $email)->first();
            $idOne = $db->table('users')->where('id', 1)->first();

            // الحالة المثالية: الأدمن موجود و id=1
            if ($adminByEmail && (int) $adminByEmail->id === 1) {
                $db->table('users')->where('id', 1)->update($payload);

                return ['user_id' => 1, 'action' => 'password_reset'];
            }

            // أدمن موجود لكن id ≠ 1 — انقله إلى 1
            if ($adminByEmail && (int) $adminByEmail->id !== 1) {
                $oldId = (int) $adminByEmail->id;

                if (!$idOne) {
                    $db->table('users')->where('id', $oldId)->update(array_merge($payload, ['id' => 1]));
                    self::repointUserForeignKeys($db, $oldId, 1);

                    return ['user_id' => 1, 'action' => 'admin_id_moved_to_1'];
                }

                // id=1 مشغول بمستخدم آخر: حوّل الصف 1 إلى الأدمن واحذف الصف القديم
                $db->table('users')->where('id', 1)->update(array_merge($payload, [
                    'name' => 'مدير النظام',
                    'email' => $email,
                ]));
                self::repointUserForeignKeys($db, $oldId, 1);
                $db->table('users')->where('id', $oldId)->delete();

                return ['user_id' => 1, 'action' => 'admin_merged_to_id_1'];
            }

            // لا يوجد أدمن بهذا الإيميل
            if ($idOne) {
                $db->table('users')->where('id', 1)->update(array_merge($payload, [
                    'name' => 'مدير النظام',
                    'email' => $email,
                ]));

                return ['user_id' => 1, 'action' => 'id_1_converted_to_admin'];
            }

            $insert = array_merge($payload, [
                'id' => 1,
                'name' => 'مدير النظام',
                'email' => $email,
                'created_at' => now(),
            ]);
            if ($db->getSchemaBuilder()->hasColumn('users', 'show_wallet')) {
                $insert['show_wallet'] = true;
            }
            $db->table('users')->insert($insert);

            return ['user_id' => 1, 'action' => 'admin_created'];
        } finally {
            $db->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private static function repointUserForeignKeys($db, int $fromId, int $toId): void
    {
        if ($fromId === $toId) {
            return;
        }

        if ($db->getSchemaBuilder()->hasTable('wallets')) {
            // ادمج المحفظة إن وُجدت على الوجهة
            $fromWallet = $db->table('wallets')->where('user_id', $fromId)->first();
            $toWallet = $db->table('wallets')->where('user_id', $toId)->first();
            if ($fromWallet && $toWallet) {
                $db->table('wallets')->where('user_id', $fromId)->delete();
            } elseif ($fromWallet) {
                $db->table('wallets')->where('user_id', $fromId)->update(['user_id' => $toId]);
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
