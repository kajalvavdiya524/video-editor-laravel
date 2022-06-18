<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\UploadimgRelationship;
use App\Domains\Auth\Models\Traits\Attribute\UploadimgAttribute;

class Uploadimg extends Model
{
    
    use UploadimgRelationship;
    use UploadimgAttribute;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'company_id',
        'filename',
        'ASIN',
        'UPC',
        'GTIN',
        'width',
        'height',
        'url'
    ];

}
