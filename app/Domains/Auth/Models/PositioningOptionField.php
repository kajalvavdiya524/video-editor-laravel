<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PositioningOptionField.
 */
class PositioningOptionField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'option_id',
        'field_name',
        'fields',
        'x',
        'y',
        'width'
    ];
}
