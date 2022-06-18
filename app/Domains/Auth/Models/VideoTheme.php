<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTheme extends Model
{
    const TABLE_NAME = 'video_themes';

    const FIELD_ALL_COMPANIES = 'all_companies';

	public $table = 'video_themes';
    public $timestamps = false;
    public $fillable = [
    	'name',
		'font_names',
		'default_font_name',
		'font_size',
		'stroke_colors',
		'stroke_width',
		'font_colors',
		'default_font_color',
		'theme_number',
		'is_font_color_selector',
		'is_stroke_color_selector',
        self::FIELD_ALL_COMPANIES
	];

    public function colors(){
        return $this->hasMany(VideoThemeColor::class);
    }
}
