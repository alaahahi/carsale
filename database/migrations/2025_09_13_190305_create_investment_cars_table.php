<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade');
            $table->foreignId('car_id')->constrained('car')->onDelete('cascade');
            $table->decimal('invested_amount', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('profit_share', 15, 2)->default(0);
            $table->timestamps();
            
            // فهرس فريد لمنع الاستثمار في نفس السيارة أكثر من مرة
            $table->unique(['investment_id', 'car_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_cars');
    }
};