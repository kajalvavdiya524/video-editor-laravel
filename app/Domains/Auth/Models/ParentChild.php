<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Method\ParentChildMethod;

class ParentChild extends Model
{
    use ParentChildMethod;

    protected $table = 'parent_childs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent',
        'child',
        'company_id',
    ];
}