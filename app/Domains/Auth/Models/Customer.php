<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Relationship\CustomerRelationship;
use App\Domains\Auth\Models\Traits\Scope\CustomerScope;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Method\CustomerMethod;
/**
 * Class Customer.
 */
class Customer extends Model
{
    use CustomerScope;
    use CustomerRelationship;
    use CustomerMethod;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'value',
        'image_url',
        'xlsx_template_url'
    ];

}
