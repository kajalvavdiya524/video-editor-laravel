<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class StockImage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'company_id',
        'filename',
        'tags',
        'url'
    ];

}
