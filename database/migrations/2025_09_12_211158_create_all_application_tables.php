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
        // جدول أنواع المستخدمين
        if (!Schema::hasTable('user_type')) {
            Schema::create('user_type', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        // جدول المستخدمين الأساسي
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('type_id')->nullable();
            $table->string('public_key')->nullable();
            $table->string('publickey_receiver')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_band')->default(false);
            $table->string('phone')->nullable();
            $table->string('device')->nullable();
            $table->string('tenant_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('user_type')->onDelete('set null');
            });
        }

        // جدول الشركات
        if (!Schema::hasTable('company')) {
            Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('tenant_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade'); // جدول tenants غير موجود في قاعدة بيانات المستأجر
            });
        }

        // جدول السيارات
        if (!Schema::hasTable('car')) {
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
            $table->string('tenant_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade'); // جدول tenants غير موجود في قاعدة بيانات المستأجر
            });
        }

        // جدول تاريخ تغيير حقول السيارات
        if (!Schema::hasTable('car_field_histories')) {
            Schema::create('car_field_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->foreign('car_id')->references('id')->on('car')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // جدول المحافظ
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('card')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // جدول المعاملات
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('morphed_type')->nullable();
            $table->unsignedBigInteger('morphed_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('type');
            $table->text('description')->nullable();
            $table->boolean('is_pay')->default(false);
            $table->timestamps();
            
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->index(['morphed_type', 'morphed_id']);
            });
        }

        // جدول أنواع المصروفات
        if (!Schema::hasTable('expenses_type')) {
            Schema::create('expenses_type', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_kr')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            });
        }

        // جدول المصروفات
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reason')->nullable();
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // جدول التحويلات
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

 

        // جدول أسماء السيارات
        if (!Schema::hasTable('name')) {
            Schema::create('name', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('id')->on('company')->onDelete('set null');
            });
        }

        // جدول موديلات السيارات
        if (!Schema::hasTable('car_model')) {
            Schema::create('car_model', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            });
        }

        // جدول ألوان السيارات
        if (!Schema::hasTable('color')) {
            Schema::create('color', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            });
        }

        // جدول إعدادات النظام
        if (!Schema::hasTable('system_config')) {
            Schema::create('system_config', function (Blueprint $table) {
            $table->id();
            $table->string('first_title_ar')->nullable();
            $table->string('first_title_kr')->nullable();
            $table->string('second_title_ar')->nullable();
            $table->string('second_title_kr')->nullable();
            $table->string('third_title_ar')->nullable();
            $table->string('third_title_kr')->nullable();
            $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_config');
        Schema::dropIfExists('color');
        Schema::dropIfExists('car_model');
        Schema::dropIfExists('name');
         Schema::dropIfExists('transfers');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expenses_type');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('car_field_histories');
        Schema::dropIfExists('car');
        Schema::dropIfExists('company');
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_type');
    }
};