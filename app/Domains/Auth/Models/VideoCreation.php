<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCreation extends Model
{
    public $fillable = ['xlsx',
    	'user_id',
    	'status',
    	'percent',
    	'task_id',
    	'mp4',
    	'vtt',
    	'type',
    	'last_details',
		'thumbnail'
    ];

    public function share() {
    	return $this->hasOne(VideoShare::class, 'video_creation_id');
    }

    public function path_mp4() {
    	return url('/video_creation/mp4/' . $this->xlsx . '.mp4');
    }
}
