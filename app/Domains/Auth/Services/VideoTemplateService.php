<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Http\Imports\VideoTemplateImport;
use App\Domains\Auth\Models\VideoTemplate;
use Maatwebsite\Excel\Facades\Excel;

class VideoTemplateService
{
    public static function importTemplate($template)
    {
        $array = Excel::toArray(new VideoTemplateImport, $template);
        $rows = $array[0];

        foreach ($rows as $key => &$row) {
            $row['End'] = (float)$row['Start'] + (float)$row['Duration'];
            $row['Background_Color'] = $row['Background_Color'] === "" ? '#ffffff' : $row['Background_Color'];
            $row['Line_Spacing'] = $row['Line_Spacing'] === "" ? 1 : $row['Line_Spacing'];
        }

        return $rows;
    }

    public static function updateAllCompaniesColumn(array $data, int $entityId): void
    {
        VideoTemplate::where('id', $entityId)->update([
            VideoTemplate::FIELD_ALL_COMPANIES =>
                (isset($data['isSelectAll']) && $data['isSelectAll']) || $data['action'] === VideoService::ACTION_SELECT ? 1 : 0
        ]);
    }
}
