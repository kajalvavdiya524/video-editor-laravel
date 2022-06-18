<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Domains\Auth\Models\VideoScenes;
use App\Domains\Auth\Services\VideoSceneService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class VideoSceneController extends Controller
{
    private $sceneService;

    public function __construct(VideoSceneService $sceneService)
    {
        $this->sceneService = $sceneService;
    }

    public function get(Request $request)
    {
        $scenes = $this->sceneService->getScene();

        return response($scenes);
    }

    public function save(Request $request)
    {
        $scene_data = $request->input('scene_data');
        $title = $request->input('title');
        $user = Auth::user();

        VideoScenes::create([
            VideoScenes::FIEND_TITLE => $title,
            VideoScenes::FIELD_SCENE_DATA => $scene_data,
            VideoScenes::FIELD_USER_ID => $user->id
        ]);
    }

    public function edit(Request $request)
    {
        VideoScenes::where(VideoScenes::FIELD_ID, $request->input('id'))->update([
            VideoScenes::FIEND_TITLE => $request->input('title'),
        ]);
    }

    public function delete(Request $request, int $id)
    {
        VideoScenes::where(VideoScenes::FIELD_ID, $id)->delete();
    }
}
