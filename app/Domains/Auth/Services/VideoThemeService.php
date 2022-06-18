<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\VideoTheme;

class VideoThemeService
{
    public static function updateAllCompaniesColumn(array $data, int $entityId): void
    {
        VideoTheme::where('id', $entityId)->update([
            VideoTheme::FIELD_ALL_COMPANIES =>
                (isset($data['isSelectAll']) && $data['isSelectAll']) || $data['action'] === VideoService::ACTION_SELECT ? 1 : 0
        ]);
    }
}