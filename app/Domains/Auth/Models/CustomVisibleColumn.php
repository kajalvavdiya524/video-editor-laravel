<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class CustomVisibleColumn extends Model
{
    const DEFAULT_TIMEFRAME = 1;
    
    public $table = 'custom_visible_columns';
    public $fillable = ['user_id', 'columns','timeframe'];
}
