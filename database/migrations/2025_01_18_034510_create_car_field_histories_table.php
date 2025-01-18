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
        Schema::create('car_field_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id'); // Foreign key to the car table
            $table->string('field'); // Field name being changed
            $table->text('old_value')->nullable(); // Old value
            $table->text('new_value')->nullable(); // New value
            $table->unsignedBigInteger('user_id')->nullable(); // User who made the change
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('car_id')->references('id')->on('car')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_field_histories');
    }
};
