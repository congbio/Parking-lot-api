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
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id')->from(1000000);
            $table->unsignedInteger('userId');
            $table->string('nameUserSend');
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->text('message');
            $table->string('image');
            
            $table->json('data')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamps();
            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
