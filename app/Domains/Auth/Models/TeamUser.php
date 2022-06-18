<?php

namespace App\Domains\Auth\Models;

use Altek\Accountant\Contracts\Recordable;
use Altek\Accountant\Recordable as RecordableTrait;
use Altek\Eventually\Eventually;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TeamUser.
 */
class TeamUser extends Model implements Recordable
{
    use Eventually,
        RecordableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'company_id',
    ];

}
