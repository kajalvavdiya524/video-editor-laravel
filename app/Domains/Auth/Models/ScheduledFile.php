<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'filename',
        'status'
    ];
}
