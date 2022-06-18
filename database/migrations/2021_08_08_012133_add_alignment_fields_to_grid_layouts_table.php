<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlignmentFieldsToGridLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grid_layouts', function (Blueprint $table) {
            //
            $table->smallInteger('alignment')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grid_layouts', function (Blueprint $table) {
            //
            $table->dropColumn('alignment');
        });
    }
}
