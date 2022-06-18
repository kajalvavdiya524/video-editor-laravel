<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoShare extends Model
{
	public $table = 'video_shares';
    public $fillable = [
    	'uuid',
    	'video_creation_id',
    	'name',
    	'file_name'
    ];

    public function videoCreation() {
    	return $this->belongsTo(VideoCreation::class, 'video_creation_id');
    }

    public function comments() {
    	return $this->hasMany(VideoComment::class, 'share_id');
    }
}
