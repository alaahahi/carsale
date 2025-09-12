<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('color')->nullable();
            $table->string('model')->nullable();
            $table->json('image')->nullable();
            $table->string('pin')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->decimal('paid_amount_pay', 10, 2)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_purchase_id')->nullable();
            $table->unsignedBigInteger('user_accepted')->nullable();
            $table->unsignedBigInteger('user_rejected')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_data')->nullable();
            $table->date('pay_data')->nullable();
            $table->decimal('pay_price', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->text('note_pay')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->text('results')->nullable();
            $table->decimal('erbil_exp', 10, 2)->nullable();
            $table->decimal('erbil_shipping', 10, 2)->nullable();
            $table->decimal('dubai_exp', 10, 2)->nullable();
            $table->decimal('dubai_shipping', 10, 2)->nullable();
            $table->string('source')->nullable();
            $table->string('tenant_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car');
    }
};
