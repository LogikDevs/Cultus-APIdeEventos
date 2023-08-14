<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("surname");
            $table->integer("age");
            $table->string("gender")->nullable();
            $table->string("email")->unique();
            $table->string("password");
            $table->string("profile_pic")->nullable();
            $table->string("description")->nullable();

            $table->unsignedBigInteger("homeland")->nullable();
            $table->unsignedBigInteger("residence")->nullable();
            $table->foreign("homeland")->references("id_country")->on("country");
            $table->foreign("residence")->references("id_country")->on("country");
           
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
