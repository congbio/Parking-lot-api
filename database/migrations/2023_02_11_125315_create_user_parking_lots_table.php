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
        Schema::create('user_parking_lots', function (Blueprint $table) {
            $table->increments("id")->from(1000000);
            $table->unsignedInteger("userId");
            $table->unsignedInteger("parkingId");
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parkingId')->references('id')->on('parking_lots')->onDelete('cascade');
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
        Schema::dropIfExists('user_parking_lots');
    }
};
