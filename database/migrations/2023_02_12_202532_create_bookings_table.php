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
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id')->from(100000000);
            $table->unsignedInteger("userId");
            $table->unsignedInteger("slotId");
            $table->dateTime('bookDate');
            $table->dateTime('returnDate');
            $table->string('licensePlate');
            $table->double('payment');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('slotId')->references('id')->on('parking_slots')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
