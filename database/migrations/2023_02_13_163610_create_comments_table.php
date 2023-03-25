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
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id')->from(1000000);
            $table->unsignedInteger("userId");
            $table->unsignedInteger("parkingId");
            $table->string("content")->nullable();
            $table->integer('ranting');
            $table->timestamps();
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parkingId')->references('id')->on('parking_lots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
