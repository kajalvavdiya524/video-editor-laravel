<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('product_name');
            $table->string('brand');
            $table->text('path');
            $table->text('thumbnail');
            $table->string('ASIN');
            $table->string('UPC');
            $table->string('parent_gtin');
            $table->string('child_gtin');
            $table->float('width')->default(0);
            $table->float('height')->default(0);
            $table->float('depth')->default(0);
            $table->unsignedTinyInteger('has_dimension')->default(0);
            $table->unsignedTinyInteger('has_child')->default(0);
            $table->unsignedTinyInteger('is_cropped')->default(0);
            $table->unsignedBigInteger('company_id');
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
        Schema::dropIfExists('files');
    }
}
