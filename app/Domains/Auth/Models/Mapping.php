<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Method\MappingMethod;

class Mapping extends Model
{
    use MappingMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ASIN',
        'UPC',
        'TCIN',
        'WMT_ID',
        'company_id',
    ];
}