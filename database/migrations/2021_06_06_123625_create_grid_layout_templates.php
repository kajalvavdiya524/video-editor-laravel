<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGridLayoutTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grid_layout_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layout_id');
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('timestamp');
            $table->text('settings');
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
        Schema::dropIfExists('grid_layout_templates');
    }
}
