<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\ImageListRelationship;

/**
 * Class ImageList.
 */
class ImageList extends Model
{
    use ImageListRelationship;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'created_by',
    ];

}
