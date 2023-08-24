<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Participants extends Migration
{
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id('id_participant');
            $table->unsignedBigInteger('fk_id_event');
            $table->unsignedBigInteger('fk_id_user');

            $table->foreign('fk_id_event')->references('id_event')->on('events');
            $table->foreign('fk_id_user')->references('id')->on('users');

            $table->unique(['fk_id_event','fk_id_user']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        
    }
}
