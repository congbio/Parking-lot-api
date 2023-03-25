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
        Schema::create('blocks', function (Blueprint $table) {
            $table->increments("id")->from(1000000);
            $table->unsignedInteger("parkingLotId");
            $table-> string("nameBlock",50);
            $table->enum('carType',['4-16SLOT','16-34SLOT']);
            $table->double("price");
            $table->string("desc");
            $table->foreign('parkingLotId')->references('id')->on('parking_lots')->onDelete('cascade');
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
        Schema::dropIfExists('blocks');
    }
};
