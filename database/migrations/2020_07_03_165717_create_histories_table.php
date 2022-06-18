<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('customer')->nullable();
            $table->string('output_dimensions')->nullable();
            $table->string('projectname')->nullable();
            $table->string('fileid');
            $table->string('headline')->nullable();
            $table->string('size');
            $table->string('url');
            $table->text('settings');
            $table->unsignedBigInteger('user_id');
            $table->string('jpg_files')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
}
