<?php

namespace App\Console\Commands;

use App\Helpers\EnsureTenantAdmin;
use App\Models\TenantDatabaseConfig;
use Illuminate\Console\Command;

class FixTenantAdminCommand extends Command
{
    protected $signature = 'tenants:fix-admin
        {domain? : الدومين أو الـ subdomain مثل sarwan أو sarwan.intellij-app.com}
        {--all : إصلاح كل التجار النشطين}
        {--email= : إيميل الأدمن (افتراضي admin@admin.com)}
        {--password= : كلمة المرور (افتراضي 12345678)}';

    protected $description = 'إنشاء/إعادة ضبط حساب الأدمن على قاعدة التاجر حتى يعمل تسجيل الدخول';

    public function handle(): int
    {
        $email = $this->option('email') ?: EnsureTenantAdmin::defaultEmail();
        $password = $this->option('password') ?: EnsureTenantAdmin::defaultPassword();

        if ($this->option('all')) {
            $configs = TenantDatabaseConfig::query()->where('is_active', true)->get();
            if ($configs->isEmpty()) {
                $this->error('لا توجد إعدادات قواعد نشطة');
                return self::FAILURE;
            }

            $ok = 0;
            foreach ($configs as $config) {
                $result = EnsureTenantAdmin::fix($config, $email, $password);
                $this->line(sprintf(
                    '[%s] %s — %s (db=%s user_id=%s)',
                    $result['ok'] ? 'OK' : 'FAIL',
                    $config->subdomain,
                    $result['message'],
                    $result['database'] ?? '-',
                    $result['user_id'] ?? '-'
                ));
                if ($result['ok']) {
                    $ok++;
                }
            }

            $this->info("تم: {$ok}/{$configs->count()} — الدخول: {$email} / {$password}");
            return self::SUCCESS;
        }

        $domain = $this->argument('domain');
        if (!$domain) {
            $this->error('حدد domain أو استخدم --all');
            $this->line('مثال: php artisan tenants:fix-admin sarwan');
            $this->line('مثال: php artisan tenants:fix-admin --all');
            return self::FAILURE;
        }

        $result = EnsureTenantAdmin::fixByHost($domain, $email, $password);

        if (!$result['ok']) {
            $this->error($result['message']);
            return self::FAILURE;
        }

        $this->info($result['message']);
        $this->table(
            ['Field', 'Value'],
            [
                ['action', $result['action']],
                ['database', $result['database']],
                ['email', $result['email']],
                ['user_id', $result['user_id']],
                ['password', $password],
            ]
        );

        return self::SUCCESS;
    }
}
