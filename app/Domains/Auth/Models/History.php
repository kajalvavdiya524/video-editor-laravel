<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\HistoryRelationship;
use App\Domains\Auth\Models\Traits\Method\HistoryMethod;
/**
 * Class Company.
 */
class History extends Model
{
    use HistoryRelationship;
    use HistoryMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'customer',
        'output_dimensions',
        'projectname',
        'fileid',
        'headline',
        'size',
        'url',
        'settings',
        'user_id',
        'jpg_files',
        'type',
        'parent_id'
    ];

}
