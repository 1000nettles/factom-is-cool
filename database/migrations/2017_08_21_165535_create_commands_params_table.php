<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commands_params', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('command_id')->unsigned();
            $table
                ->foreign('command_id')
                ->references('id')
                ->on('commands')
                ->onDelete('cascade');
            $table->string('identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commands_params');
    }
}
