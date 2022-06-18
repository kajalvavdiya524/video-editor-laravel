<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoProject extends Model
{
    public $table = 'video_projects';
    
    public $fillable = [
        'user_id',
        'name',
        'file_name',
        'order',
        'thumbnail_image',
        'visibility',
        'screen_width',
        'screen_height',
        'is_draft',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
