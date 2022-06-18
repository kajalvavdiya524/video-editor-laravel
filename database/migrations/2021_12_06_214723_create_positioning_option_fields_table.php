<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositioningOptionFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positioning_option_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('option_id');
            $table->string('field_name');
            $table->integer('fields');
            $table->integer('x');
            $table->integer('y');
            $table->integer('width');
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
        Schema::dropIfExists('positioning_option_fields');
    }
}
