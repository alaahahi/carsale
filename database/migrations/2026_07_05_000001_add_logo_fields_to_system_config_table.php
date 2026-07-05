<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_config')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            if (!Schema::hasColumn('system_config', 'logo_image')) {
                $table->string('logo_image')->nullable()->after('third_title_kr');
            }
            if (!Schema::hasColumn('system_config', 'login_bg_image')) {
                $table->string('login_bg_image')->nullable()->after('logo_image');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('system_config')) {
            return;
        }

        Schema::table('system_config', function (Blueprint $table) {
            if (Schema::hasColumn('system_config', 'login_bg_image')) {
                $table->dropColumn('login_bg_image');
            }
            if (Schema::hasColumn('system_config', 'logo_image')) {
                $table->dropColumn('logo_image');
            }
        });
    }
};
