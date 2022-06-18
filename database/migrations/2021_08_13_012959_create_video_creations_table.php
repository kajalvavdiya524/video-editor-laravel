<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoCreationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_creations', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('status')->nullable();
            $table->string('task_id')->nullable();
            $table->string('percent')->default('0%');
            $table->string('mp4')->nullable();
            $table->string('vtt')->nullable();
            $table->string('xlsx')->default('Output');
            $table->enum('type', ['vtt', 'mp4', 'both'])->default('mp4');
            $table->json('last_details')->nullable();
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
        Schema::dropIfExists('video_creations');
    }
}
