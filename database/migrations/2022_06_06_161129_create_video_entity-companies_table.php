<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Domains\Auth\Models\VideoEntityCompanies;

class CreateVideoEntityCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(VideoEntityCompanies::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger(VideoEntityCompanies::FIELD_ENTITY_ID);
            $table->unsignedBigInteger(VideoEntityCompanies::FIELD_COMPANY_ID);
            $table->string(VideoEntityCompanies::FIELD_ENTITY_TYPE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(VideoEntityCompanies::TABLE_NAME);
    }
}
