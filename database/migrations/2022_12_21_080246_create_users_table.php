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
        Schema::create('users', function (Blueprint $table) {
            $table->increments("id")->from(1000000);
            $table-> string("email",100)->unique();
            $table-> string("fullName",100);
            $table-> string("avatar");
            $table-> string("phone")->nullable();
            $table-> string("imageCardIdBef")->nullable();
            $table-> string("imageCardIdAft")->nullable();
            $table->enum('areaType',['local','business'])->nullable();
            $table->enum('role',['admin','user','owner']);
            $table-> string("password");
            $table-> boolean('status');
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
        Schema::dropIfExists('users');
    }
};
