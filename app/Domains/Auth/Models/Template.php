<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Relationship\TemplateRelationship;
use App\Domains\Auth\Models\Traits\Scope\TemplateScope;
use App\Domains\Auth\Models\Traits\Method\TemplateMethod;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Template.
 */
class Template extends Model
{
    use TemplateRelationship;
    use TemplateScope;
    use TemplateMethod;
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
        'width',
        'height',
        'image_url'
    ];

}
