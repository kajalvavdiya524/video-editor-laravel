<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('urls_files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('filename');
            $table->unsignedBigInteger('rows');
            $table->unsignedTinyInteger('status')->default(0);
            $table->string('url');
            $table->string('id_column');
            $table->string('zip_file_url');
            $table->integer('new_prod')->default(0);
            $table->integer('new_nf')->default(0);
            $table->integer('new_ingr')->default(0);
            $table->integer('changed_prod')->default(0);
            $table->integer('changed_nf')->default(0);
            $table->integer('changed_ingr')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('urls_files');
    }
}
