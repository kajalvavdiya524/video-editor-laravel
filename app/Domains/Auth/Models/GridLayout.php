<?php

namespace App\Domains\Auth\Models;


use App\Domains\Auth\Models\Traits\Relationship\GridLayoutRelationship;
use App\Domains\Auth\Models\Traits\Method\GridLayoutMethod;

use Illuminate\Database\Eloquent\Model;


class GridLayout extends Model
{
    use GridLayoutRelationship;
    use GridLayoutMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'user_id',
        'name',
        'settings',
        'options',
    ];
}