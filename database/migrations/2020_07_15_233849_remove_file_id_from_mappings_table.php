<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFileIdFromMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mappings', function (Blueprint $table) {
            //
            $table->dropColumn('file_id');
            $table->unsignedBigInteger('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mappings', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('file_id');
            $table->dropColumn('company_id');
        });
    }
}
