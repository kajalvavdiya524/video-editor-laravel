<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoComment extends Model
{
    public $table = 'video_comments';
    public $fillable = [
    	'share_id',
    	'user_id',
    	'subject',
    	'comment'
    ];

    public function user() {
    	return $this->belongsTo(User::class, 'user_id');
    }
}
