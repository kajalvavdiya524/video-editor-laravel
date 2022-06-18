<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Relationship\LoadingHistoryRelationship;
use App\Domains\Auth\Models\Traits\Attribute\LoadingHistoryAttribute;
use Illuminate\Database\Eloquent\Model;

class LoadingHistory extends Model
{
    use LoadingHistoryAttribute;
    use LoadingHistoryRelationship;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'company_id',
        'user_id',
        'filename',
        'url', 
        'type'
    ];

}
