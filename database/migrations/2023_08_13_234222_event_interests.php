<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EventInterests extends Migration
{
    public function up()
    {
        Schema::create('event_interests', function (Blueprint $table) {
            $table->id('id_event_interest');
            $table->unsignedBigInteger('fk_id_event');
            $table->unsignedBigInteger('fk_id_label');

            $table->foreign('fk_id_event')->references('id')->on('events');
            $table->foreign('fk_id_label')->references('id_label')->on('interest_label');

            $table->unique(['fk_id_event','fk_id_label']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_interests');
    }
}
