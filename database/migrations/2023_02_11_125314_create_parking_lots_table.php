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
        Schema::create('parking_lots', function (Blueprint $table) {
            $table->increments("id")->from(1000000);
            $table-> json("images")->nullable();
            $table-> time("openTime");
            $table-> time("endTime");
            $table-> string("nameParkingLot",100)->unique();
            $table->double('address_latitude')->nullable();
            $table->double('address_longitude')->nullable();
            $table-> string("address",200);
            $table-> string("desc")->nullable();
            $table-> boolean('status')->nullable();
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
        Schema::dropIfExists('parking_lots');
    }
};
