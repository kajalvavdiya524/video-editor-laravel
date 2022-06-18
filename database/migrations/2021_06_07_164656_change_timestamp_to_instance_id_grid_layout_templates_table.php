<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTimestampToInstanceIdGridLayoutTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grid_layout_templates', function (Blueprint $table) {
            //
            $table->dropColumn('timestamp');
            $table->string('instance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grid_layout_templates', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('timestamp');
            $table->dropColumn('instance_id');
        });
    }
}
