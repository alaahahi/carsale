<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_payments')) {
            return;
        }

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            // car.id عند بعض قواعد البيانات يكون INT
            $table->integer('car_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'paid_at']);
            $table->index(['car_id']);
            $table->index(['created_by']);

            // FK على users فقط (متوافق دائماً)
            $table->foreign('supplier_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};

