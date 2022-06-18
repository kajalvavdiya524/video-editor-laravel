<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProjectApproval.
 */
class ProjectApproval extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'requester_id',
        'request_time',
        'user_id',
        'approval_time',
        'approved',
        'comment'
    ];

}
