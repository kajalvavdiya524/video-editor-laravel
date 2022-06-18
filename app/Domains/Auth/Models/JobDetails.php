<?php

namespace App\Domains\Auth\Models;
use App\Domains\Auth\Models\Traits\Relationship\JobDetailsRelationship;
use Illuminate\Database\Eloquent\Model;

class JobDetails extends Model
{
    use JobDetailsRelationship;
}
