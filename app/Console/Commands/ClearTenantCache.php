<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\SubdomainHelper;

class ClearTenantCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:clear-cache {--all : Clear all tenant cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear tenant cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('all')) {
            SubdomainHelper::clearAllTenantCache();
            $this->info('تم مسح جميع كاش المستأجرين بنجاح');
        } else {
            $this->info('استخدم --all لمسح جميع كاش المستأجرين');
        }

        return 0;
    }
}
