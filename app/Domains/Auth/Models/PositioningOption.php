<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\PositioningOptionRelationship;

/**
 * Class PositioningOption.
 */
class PositioningOption extends Model
{
    use PositioningOptionRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_id',
        'name',
    ];

}
