<?php

namespace App\Domains\Auth\Models;

use Altek\Accountant\Contracts\Recordable;
use Altek\Accountant\Recordable as RecordableTrait;
use Altek\Eventually\Eventually;
use App\Domains\Auth\Models\Traits\Attribute\CompanyAttribute;
use App\Domains\Auth\Models\Traits\Method\CompanyMethod;
use App\Domains\Auth\Models\Traits\Scope\CompanyScope;
use App\Domains\Auth\Models\Traits\Relationship\CompanyRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Company.
 */
class Company extends Model implements Recordable
{
    use Eventually,
        RecordableTrait,
        SoftDeletes,
        CompanyAttribute,
        CompanyMethod,
        CompanyRelationship,
        CompanyScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'active',
        'notification_emails',
        'has_mrhi',
        'has_pilot',
        'use_azure'
    ];

}
