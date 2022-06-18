<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('histories', function (Blueprint $table) {
            $table->dropColumn('is_master');
            $table->unsignedSmallInteger('type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('histories', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->unsignedTinyInteger('is_master')->default(0);
        });
    }
}
