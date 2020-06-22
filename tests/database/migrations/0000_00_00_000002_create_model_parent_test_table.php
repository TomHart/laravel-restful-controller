<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelParentTestTable extends Migration
{

    public function up()
    {
        Schema::create('model_parent_tests', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger('child_id')->nullable();
            $table->unsignedInteger('number')->nullable();
            $table->timestamps();
        });

        Schema::create('model_parent_test_model_test', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('model_parent_test_id');
            $table->unsignedInteger('model_test_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_parent_tests');
    }
}
