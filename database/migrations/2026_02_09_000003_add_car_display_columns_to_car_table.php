<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (!Schema::hasColumn('car', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('car', 'model')) {
                $table->string('model')->nullable()->after('model_id');
            }
            if (!Schema::hasColumn('car', 'color')) {
                $table->string('color')->nullable()->after('color_id');
            }
            if (!Schema::hasColumn('car', 'source')) {
                $table->string('source')->nullable()->after('pin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (Schema::hasColumn('car', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('car', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('car', 'model')) {
                $table->dropColumn('model');
            }
            if (Schema::hasColumn('car', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};

