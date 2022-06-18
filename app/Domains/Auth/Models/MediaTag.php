<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class MediaTag extends Model
{
	public $table = 'media_tags';
    public $timestamps = false;
    public $fillable = ['name'];
}
