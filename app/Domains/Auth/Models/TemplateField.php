<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TemplateField.
 */
class TemplateField extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_id',
        'name',
        'element_id',
        'type',
        'order',
        'grid_col',
        'options'
    ];

}
