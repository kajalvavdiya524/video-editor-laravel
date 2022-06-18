<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Method\DimensionMethod;

class Dimension extends Model
{
    use DimensionMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'GTIN',
        'width',
        'height',
        'company_id',
    ];
}