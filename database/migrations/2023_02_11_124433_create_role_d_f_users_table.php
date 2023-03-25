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
        Schema::create('role_d_f_users', function (Blueprint $table) {
            $table->increments("id")->from(1000000);
            $table->unsignedInteger("userId");
            $table->unsignedInteger("roleId");
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('roleId')->references('id')->on('roles')->onDelete('cascade');
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
        Schema::dropIfExists('role_d_f_users');
    }
};
