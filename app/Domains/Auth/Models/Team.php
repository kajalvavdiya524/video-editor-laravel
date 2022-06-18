<?php

namespace App\Domains\Auth\Models;

use Altek\Accountant\Contracts\Recordable;
use Altek\Accountant\Recordable as RecordableTrait;
use Altek\Eventually\Eventually;
use App\Domains\Auth\Models\Traits\Attribute\TeamAttribute;
use App\Domains\Auth\Models\Traits\Method\TeamMethod;
use App\Domains\Auth\Models\Traits\Scope\TeamScope;
use App\Domains\Auth\Models\Traits\Relationship\TeamRelationship;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Team.
 */
class Team extends Model implements Recordable
{
    use Eventually,
        RecordableTrait,
        TeamAttribute,
        TeamMethod,
        TeamRelationship,
        TeamScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'company_id',
    ];

}
