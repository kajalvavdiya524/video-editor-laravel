<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoThemeColors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_theme_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hex')->nullable();
            $table->string('type') ;
            $table->string('video_theme_id')->references('id')->on('video_themes');
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
        Schema::dropIfExists('video_theme_colors');
    }
}
