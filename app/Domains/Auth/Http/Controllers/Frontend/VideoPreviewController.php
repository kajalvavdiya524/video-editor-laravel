<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use App\Domains\Auth\Models\CustomVisibleColumn;
use App\Domains\Auth\Models\VideoEntityCompanies;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use File;
use Auth;

use App\Domains\Auth\Models\VideoCreation;
use App\Domains\Auth\Models\VideoTemplate;
use App\Domains\Auth\Models\VideoTheme;
use App\Domains\Auth\Models\VideoTag;

use App\Domains\Auth\Services\MediaService;

class VideoPreviewController extends Controller
{
    /**
     * Show the preview page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;

        $path = public_path('audio');

        if(!File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }

        $music_files = File::files($path);

        $getID3 = new \getID3;

        $musics = collect($music_files)->map(function($file) use ($getID3) {
            $path = './audio/' . $file->getFilename();
            $file_meta = $getID3->analyze($path);

            return [
                'music' => $file->getFilename(),
                'path' => $path,
                'filename' => pathinfo('./audio/' . $file, PATHINFO_FILENAME),
                'duration' => round($file_meta['playtime_seconds'])
            ];
        });

        $mediaService = new MediaService();

        $musics = $mediaService->getAudios();
        $images = $mediaService->getImages();
        $videos = $mediaService->getVideos();

        $colors = [];
        foreach (config('video_template.colors') as $color) {
            $colors[] = [
                'value' => $color,
                'text'  => $color
            ];
        }




        $column_visibility_types = config('video_template.column_visibility_types');
        $selected_column_visibility_name = config('video_template.selected_column_visibility_name');
        $column_types = config('video_template.column_types');
        
        if ($user->customVisibleColumns && $user->customVisibleColumns->timeframe == '2') {
            $default_all_columns = config('video_template.all_timeframe_columns');
        } else {
            $default_all_columns = config('video_template.all_columns');
        }
        $all_columns = $default_all_columns;

        if ($user->customVisibleColumns && $user->customVisibleColumns->timeframe == '2') {
            $default_visible_columns = config('video_template.default_visible_timeframe_columns');
        } else {
            $default_visible_columns = config('video_template.default_visible_columns');
        }
        $custom_columns = $user->customVisibleColumns ? json_decode($user->customVisibleColumns->columns) : $default_visible_columns;

        $timeframe_column = $user->customVisibleColumns && $user->customVisibleColumns->timeframe ? $user->customVisibleColumns->timeframe : CustomVisibleColumn::DEFAULT_TIMEFRAME;
        $preview_sizes = config('video_template.preview_sizes');
        $font_names = config('video_template.fonts');
        $themes = VideoTheme::with('colors')
            ->leftJoin(VideoEntityCompanies::TABLE_NAME, VideoEntityCompanies::FIELD_ENTITY_ID, 'id')
            ->where(function ($query) {
                $query->where(VideoEntityCompanies::TABLE_NAME.'.'.VideoEntityCompanies::FIELD_COMPANY_ID, Auth::user()->company_id);
                $query->where(VideoEntityCompanies::TABLE_NAME.'.'.VideoEntityCompanies::FIELD_ENTITY_TYPE, VideoEntityCompanies::ENTITY_THEME_TYPE);
                $query->orWhere(VideoTheme::FIELD_ALL_COMPANIES, 1);
            })
            ->select(VideoTheme::TABLE_NAME.'.*')
            ->groupBy('id')
            ->orderBy('theme_number', 'ASC')->get();

        $creation_data = VideoCreation::where('user_id', $user_id)->latest()->first();
        if (!$creation_data) {
            $creation_data = [
                'status' => '',
                'percent' => ''
            ];
        }

        $tags = VideoTag::get();
        $tags->transform(function($item) {
            return [
                'id' => $item->id,
                'text' => $item->name,
            ];
        });

        return view('frontend.video.index',
            compact('font_names',
                'selected_column_visibility_name',
                'all_columns',
                'column_visibility_types',
                'column_types',
                'custom_columns',
                'preview_sizes',
                'creation_data',
                'musics',
                'images',
                'videos',
                'colors',
                'themes',
                'tags',
                'user_id',
                'timeframe_column'
            )
        );
    }
}
