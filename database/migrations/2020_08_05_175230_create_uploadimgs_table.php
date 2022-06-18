<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadimgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploadimgs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedBigInteger('company_id');
            $table->string('ASIN')->nullable();
            $table->string('UPC')->nullable();
            $table->string('GTIN')->nullable();
            $table->float('width')->default(0);
            $table->float('height')->default(0);
            $table->string('url')->nullable();
            $table->unsignedTinyInteger('is_reindexed')->default(0);
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
        Schema::dropIfExists('uploadimgs');
    }
}
