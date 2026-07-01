<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'show_wallet')) {
                $table->boolean('show_wallet')->default(false);
                $table->index('show_wallet');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'show_wallet')) {
                try {
                    $table->dropIndex(['show_wallet']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('show_wallet');
            }
        });
    }
};

