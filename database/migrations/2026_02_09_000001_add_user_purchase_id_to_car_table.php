<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (!Schema::hasColumn('car', 'user_purchase_id')) {
                $table->unsignedBigInteger('user_purchase_id')->nullable()->after('user_id');
                $table->index('user_purchase_id');
                $table->foreign('user_purchase_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (Schema::hasColumn('car', 'user_purchase_id')) {
                // Drop FK first (name is auto-generated)
                try {
                    $table->dropForeign(['user_purchase_id']);
                } catch (\Throwable $e) {
                    // ignore if missing
                }
                try {
                    $table->dropIndex(['user_purchase_id']);
                } catch (\Throwable $e) {
                    // ignore if missing
                }
                $table->dropColumn('user_purchase_id');
            }
        });
    }
};

