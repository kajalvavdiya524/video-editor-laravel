<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTag extends Model
{
	public $table = 'video_tags';
    public $timestamps = false;
    public $fillable = ['name'];
}
