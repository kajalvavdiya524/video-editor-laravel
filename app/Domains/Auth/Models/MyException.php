<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Attribute\ExceptionAttribute;
use App\Domains\Auth\Models\Traits\Relationship\ExceptionRelationship;

/**
 * Class MyException.
 */
class MyException extends Model
{
    use ExceptionAttribute;
    use ExceptionRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'file_id',
        'message'
    ];

}
