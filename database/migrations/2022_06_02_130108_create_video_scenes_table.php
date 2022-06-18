<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Domains\Auth\Models\VideoScenes;

class CreateVideoScenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_scenes', function (Blueprint $table) {
            $table->id();
            $table->string(VideoScenes::FIEND_TITLE, 255);
            $table->longText(VideoScenes::FIELD_SCENE_DATA);
            $table->unsignedBigInteger(VideoScenes::FIELD_USER_ID);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_scenes');
    }
}
