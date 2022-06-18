<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePositioningFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('positioning_option_fields', function (Blueprint $table) {
            $table->integer('fields')->nullable()->change();
            $table->integer('x')->nullable()->change();
            $table->integer('y')->nullable()->change();
            $table->integer('width')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('positioning_option_fields', function (Blueprint $table) {
            $table->integer('fields')->nullable(false)->change();
            $table->integer('x')->nullable(false)->change();
            $table->integer('y')->nullable(false)->change();
            $table->integer('width')->nullable(false)->change();
        });
    }
}
