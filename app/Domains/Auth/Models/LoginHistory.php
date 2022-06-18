<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\LoginHistoryRelationship;

/**
 * Class Company.
 */
class LoginHistory extends Model
{
    use LoginHistoryRelationship;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login_at',
        'login_ip',
        'user_id',
    ];

}
