<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\ProjectRelationship;
use App\Domains\Auth\Models\Traits\Attribute\ProjectAttribute;
use App\Domains\Auth\Models\Traits\Method\ProjectMethod;

/**
 * Class Company.
 */
class Project extends Model
{
    use ProjectRelationship;
    use ProjectAttribute;
    use ProjectMethod;

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
