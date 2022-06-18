<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Method\NewMappingMethod;

class NewMapping extends Model
{
    use NewMappingMethod;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'GTIN',
        'child_links',
        'ASIN',
        'brand',
        'product_name',
        'image_url',
        'nf_url',
        'ingredient_url',
        'width',
        'height',
        'depth', 
        'company_id', 
        'status'
    ];
}