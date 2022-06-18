<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\VideoTemplate;
use App\Domains\Auth\Models\VideoTheme;
use App\Domains\Auth\Models\VideoEntityCompanies;

class AddAllCompaniesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_templates', function (Blueprint $table) {
            $table->tinyInteger(VideoTemplate::FIELD_ALL_COMPANIES)
                ->default(1)
                ->after('order');
        });

        Schema::table('video_themes', function (Blueprint $table) {
            $table->tinyInteger(VideoTheme::FIELD_ALL_COMPANIES)
                ->default(1);
        });

        $allCompanies = Company::all();
        $allVideoTemplates = VideoTemplate::all();
        $allVideoThemes = VideoTheme::all();

        $data = [];

        $this->setDataEntity($data, $allVideoTemplates, $allCompanies,VideoEntityCompanies::ENTITY_TEMPLATE_TYPE);
        $this->setDataEntity($data, $allVideoThemes, $allCompanies,VideoEntityCompanies::ENTITY_THEME_TYPE);

        VideoEntityCompanies::insert($data);
    }

    private function setDataEntity(array &$data, Collection $entities, Collection $companies, string $entityType)
    {
        foreach ($entities as $entity) {
            foreach ($companies as $company) {
                $data[] = [
                    VideoEntityCompanies::FIELD_ENTITY_ID => $entity->id,
                    VideoEntityCompanies::FIELD_COMPANY_ID => $company->id,
                    VideoEntityCompanies::FIELD_ENTITY_TYPE => $entityType
                ];
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_templates', function (Blueprint $table) {
            $table->dropColumn('all_companies');
        });

        Schema::table('video_themes', function (Blueprint $table) {
            $table->dropColumn('all_companies');
        });
    }
}
