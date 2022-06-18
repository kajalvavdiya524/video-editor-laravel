<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Method\FileMethod;

class File extends Model
{
    use FileMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'product_name', 
        'brand', 
        'path',
        'thumbnail',
        'ASIN', 
        'UPC', 
        'parent_gtin', 
        'child_gtin',
        'width', 
        'height', 
        'depth', 
        'has_dimension', 
        'has_child', 
        'is_cropped'
    ];
}
