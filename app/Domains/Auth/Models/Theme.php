<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Relationship\ThemeRelationship;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Theme.
 */
class Theme extends Model
{

    use ThemeRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'customer_id',
        'company_id',
        'status',
        'order',
        'attributes'
    ];

}
