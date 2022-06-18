<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\JobRelationship;

class Job extends Model
{
    use JobRelationship;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_id',
        'api_keys_id',
        'job_types_id',
        'job_statuses_id'
    ];
}
