<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTemplate extends Model
{
    const TABLE_NAME = 'video_templates';

    const FIELD_ALL_COMPANIES = 'all_companies';

	public $table = 'video_templates';
    public $fillable = ['name',
    	'file_name',
    	'order',
    	'readonly',
    	'visibility',
        self::FIELD_ALL_COMPANIES
    ];
}
