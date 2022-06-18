<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Auth\Models\Traits\Relationship\ProductSelectionRelationship;

class ProductSelection extends Model
{
    use ProductSelectionRelationship;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'file_id',
        'company_id',
        'count'
    ];

}
