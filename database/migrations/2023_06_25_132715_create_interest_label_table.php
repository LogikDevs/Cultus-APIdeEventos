<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterestLabelTable extends Migration
{

    public function up()
    {
        Schema::create('interest_label', function (Blueprint $table) {
            $table->id("id_label");
            $table->string("interest");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('interest_label');
    }
}
