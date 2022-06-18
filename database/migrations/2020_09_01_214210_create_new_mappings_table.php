<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('GTIN');
            $table->string('child_links');
            $table->string('ASIN');
            $table->string('brand');
            $table->string('product_name');
            $table->string('image_url')->nullable();
            $table->string('nf_url')->nullable();
            $table->string('ingredient_url')->nullable();
            $table->float('width')->nullable()->default(0);
            $table->float('height')->nullable()->default(0);
            $table->float('depth')->nullable()->default(0);
            $table->unsignedBigInteger('company_id');
            $table->string('status');
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
        Schema::dropIfExists('new_mappings');
    }
}
