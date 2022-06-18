<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('font_names');
            $table->string('default_font_name');
            $table->integer('font_size')->default(100);
            $table->string('stroke_colors');
            $table->integer('stroke_width')->default(2);
            $table->string('font_colors');
            $table->string('default_font_color');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_themes');
    }
}
