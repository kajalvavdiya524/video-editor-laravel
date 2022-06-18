<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\ApiKeysRelationship ;
use App\Domains\Auth\Models\Traits\Method\ApiKeysMethod;

class ApiKeys extends Model
{
    use ApiKeysRelationship;
    use ApiKeysMethod;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'key',
        'status',
    ];

  

}
