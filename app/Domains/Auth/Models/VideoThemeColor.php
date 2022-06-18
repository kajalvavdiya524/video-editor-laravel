<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoThemeColor extends Model
{
    public $fillable = [
        'name',
        'hex',
        'type',
        'video_theme_id',
    ];

    public function theme(){
        return $this->belongsTo(VideoThemeColor::class);
    }
}
