<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnColorSelectorToVideoThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_themes', function (Blueprint $table) {
            $table->tinyInteger('is_font_color_selector')->default('2')->comment('1=Yes, 2=No');
            $table->tinyInteger('is_stroke_color_selector')->default('2')->comment('1=Yes, 2=No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_themes', function (Blueprint $table) {
            $table->dropColumn('is_font_color_selector');
            $table->dropColumn('is_stroke_color_selector');
        });
    }
}
