<?php

namespace App\Domains\Auth\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Model;

class VideoScenes extends Model
{
    const TABLE_NAME = 'video_scenes';

    const FIELD_ID = 'id';
    const FIEND_TITLE = 'title';
    const FIELD_SCENE_DATA = 'scene_data';
    const FIELD_USER_ID = 'user_id';

    protected $table = 'video_scenes';
    protected $fillable = [
        self::FIEND_TITLE,
        self::FIELD_SCENE_DATA,
        self::FIELD_USER_ID,
    ];
    public $timestamps = false;

    protected $casts = [
        self::FIELD_SCENE_DATA => Json::class,
    ];
}
