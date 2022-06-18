<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Relationship\GridLayoutTemplateRelationship;
use App\Domains\Auth\Models\Traits\Method\GridLayoutTemplateMethod;
use Illuminate\Database\Eloquent\Model;

class GridLayoutTemplate extends Model
{
    use GridLayoutTemplateRelationship;
    use GridLayoutTemplateMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'layout_id',
        'template_id',
        'instance_id',
        'settings',
    ];
}